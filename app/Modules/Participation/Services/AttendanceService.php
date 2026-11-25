<?php

namespace App\Modules\Participation\Services;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Participation\Models\Invitation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    /**
     * @return Collection<int, array{meeting: Meeting, overlaps: Collection, deadline: CarbonImmutable, state: string, late: bool}>
     */
    public function options(string $email, ?int $userId, CarbonImmutable $at, bool $historical = false, ?int $includeMeetingId = null): Collection
    {
        $invitations = Invitation::where('email', strtolower($email))
            ->where(function ($query) use ($at, $historical): void {
                $query->whereIn('status', ['pending', 'accepted', 'expired']);
                if ($historical) {
                    $query->orWhere(fn ($declined) => $declined->where('status', 'declined')->where('responded_at', '>', $at));
                }
            })
            ->where('created_at', '<=', $at)
            ->where(fn ($query) => $query->where('status', 'accepted')->orWhere('expires_at', '>', $at))
            ->latest('id')->get()->unique('meeting_id')->keyBy('meeting_id');

        $meetings = Meeting::where(function ($query) use ($at, $includeMeetingId): void {
            $query->where(fn ($active) => $active->where('status', '!=', 'completed')->where('end_time', '>', $at));
            if ($includeMeetingId !== null) {
                $query->orWhereKey($includeMeetingId);
            }
        })
            ->where(function ($query) use ($invitations, $userId, $at): void {
                $query->whereIn('id', $invitations->keys());
                if ($userId !== null) {
                    $query->orWhereIn('id', DB::table('meeting_participants')->select('meeting_id')
                        ->where('user_id', $userId)->where('created_at', '<=', $at));
                }
            })->orderBy('start_time')->get()->keyBy('id');

        $choices = DB::table('attendance_choices')->where('user_id', $userId)
            ->whereIn('meeting_id', $meetings->keys())
            ->where('created_at', '<=', $at->format('Y-m-d H:i:s.u'))
            ->when($historical, fn ($query) => $query->where('is_late', false))
            ->latest('id')->get()
            ->filter(fn ($choice) => $choice->schedule_version === $meetings[$choice->meeting_id]->schedule_version)
            ->unique('meeting_id')->keyBy('meeting_id');

        return $meetings->map(function (Meeting $meeting) use ($meetings, $invitations, $choices, $at, $historical): array {
            $overlaps = $meetings->filter(fn (Meeting $other) => $this->overlaps($meeting, $other));
            $deadline = $this->deadline($meeting, $meetings);
            $choice = $choices->get($meeting->id);
            $invite = $invitations->get($meeting->id);
            $accepted = $invite === null || ($invite->status === 'accepted'
                && $invite->responded_at?->lte($historical && $deadline->lt($at) ? $deadline : $at));

            $state = match (true) {
                $choice?->choice === 'will_attend' => 'will_attend',
                $choice?->choice === 'not_attending' => 'not_attending',
                $meeting->schedule_version > 1 && $accepted => 'reconfirm',
                $overlaps->contains(fn (Meeting $other) => $choices->get($other->id)?->choice === 'will_attend') => 'not_chosen',
                $overlaps->isNotEmpty() => $at->gt($deadline) ? 'unchosen' : 'conflict',
                $accepted => 'will_attend',
                default => 'pending',
            };

            return ['meeting' => $meeting, 'overlaps' => $overlaps, 'deadline' => $deadline, 'state' => $state, 'late' => (bool) $choice?->is_late];
        });
    }

    public function hasConflict(string $email, ?int $userId, Meeting $meeting): bool
    {
        $option = $this->options($email, $userId, now()->toImmutable())->get($meeting->id);

        return $option !== null && $option['overlaps']->isNotEmpty();
    }

    public function save(User $user, Meeting $meeting, string $choice, int $scheduleVersion): void
    {
        DB::transaction(function () use ($user, $meeting, $choice, $scheduleVersion): void {
            User::lockForUpdate()->findOrFail($user->id);
            $at = now()->toImmutable();
            $options = $this->options($user->email, $user->id, $at);
            $lockedMeetings = Meeting::whereIn('id', $options->keys())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $option = $options->get($meeting->id);
            abort_unless($option, 403);

            $current = $lockedMeetings->get($meeting->id);
            abort_unless($current && $current->status !== 'completed', 404);
            $changed = $options->contains(fn (array $option) => $lockedMeetings->has($option['meeting']->id)
                && $lockedMeetings[$option['meeting']->id]->schedule_version !== $option['meeting']->schedule_version);
            if ($changed || $current->schedule_version !== $scheduleVersion || $current->start_time->lte($at)) {
                throw ValidationException::withMessages(['schedule_version' => 'Jadwal berubah atau meeting sudah dimulai. Periksa jadwal terbaru.']);
            }
            $invite = Invitation::where('meeting_id', $meeting->id)->where('email', strtolower($user->email))
                ->whereIn('status', ['pending', 'accepted'])->latest('id')->lockForUpdate()->first();
            $member = $current->hasParticipant($user);
            abort_unless($member || ($invite?->status === 'pending' && $invite->expires_at->gt($at)), 403);

            if ($choice === 'will_attend') {
                foreach ($option['overlaps'] as $other) {
                    if ($other->start_time->lte($at) && $options[$other->id]['state'] === 'will_attend') {
                        throw ValidationException::withMessages(['choice' => 'Pilihan bertumpuk dengan meeting yang sudah dimulai.']);
                    }
                }
            }

            if ($choice === 'will_attend' && ! $member && $invite?->status === 'pending') {
                $invite->forceFill(['status' => 'accepted', 'user_id' => $user->id, 'responded_at' => $at])->save();
                $current->participants()->attach($user->id, ['invitation_id' => $invite->id]);
            }

            $this->record($user, $current, $choice, $current->schedule_version > 1 ? 'reconfirm' : 'user', $at, $option['deadline']);
            if ($choice === 'will_attend') {
                foreach ($option['overlaps'] as $other) {
                    $alternative = $lockedMeetings->get($other->id);
                    if ($alternative && $alternative->schedule_version === $other->schedule_version
                        && $alternative->status !== 'completed' && $alternative->start_time->gt($at)
                        && $options[$other->id]['state'] !== 'not_attending') {
                        $this->record($user, $alternative, 'not_chosen', 'system', $at, $options[$other->id]['deadline']);
                    }
                }
            }
        }, attempts: 3);
    }

    /** @param Collection<int, Meeting> $meetings */
    private function deadline(Meeting $meeting, Collection $meetings): CarbonImmutable
    {
        // Expand across related overlaps, including a meeting that connects two adjacent slots.
        $group = collect([$meeting->id => $meeting]);
        do {
            $size = $group->count();
            foreach ($meetings->diffKeys($group) as $other) {
                if ($group->contains(fn (Meeting $candidate) => $this->overlaps($candidate, $other))) {
                    $group->put($other->id, $other);
                }
            }
        } while ($group->count() !== $size);

        return $group->min('start_time')->subDay();
    }

    private function overlaps(Meeting $a, Meeting $b): bool
    {
        return $a->id !== $b->id && $a->start_time->lt($b->end_time) && $b->start_time->lt($a->end_time);
    }

    private function record(User $user, Meeting $meeting, string $choice, string $source, CarbonImmutable $at, CarbonImmutable $deadline): void
    {
        DB::table('attendance_choices')->insert([
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'schedule_version' => $meeting->schedule_version,
            'choice' => $choice,
            'source' => $source,
            'is_late' => $at->gt($deadline),
            'created_at' => $at->format('Y-m-d H:i:s.u'),
        ]);
    }
}
