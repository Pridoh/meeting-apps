<?php

namespace App\Modules\Participation\Services;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Participation\Models\Invitation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class AttendanceRecap
{
    public function __construct(private AttendanceService $attendance) {}

    /** @return array{confirmed_count: int, uncertain_count: int, not_attending_count: int, unchosen_count: int} */
    public function counts(Meeting $meeting, CarbonImmutable $at, bool $historical = false): array
    {
        $members = $meeting->participants()->wherePivot('created_at', '<=', $at)->get()->keyBy(fn ($user) => strtolower($user->email));
        $invitations = Invitation::where('meeting_id', $meeting->id)->where('created_at', '<=', $at)
            ->latest('id')->get()->unique('email')->keyBy('email');

        $counts = ['confirmed_count' => 0, 'uncertain_count' => 0, 'not_attending_count' => 0, 'unchosen_count' => 0];
        $emails = $members->keys()->merge($invitations->keys())->unique();
        $accounts = User::whereIn('email', $emails)->get(['id', 'email'])->keyBy(fn (User $user) => strtolower($user->email));
        foreach ($emails as $email) {
            $invite = $invitations->get($email);
            $member = $members->get($email);
            if (! $member && ($invite === null || $invite->status === 'revoked'
                || (in_array($invite->status, ['pending', 'expired'], true) && $invite->expires_at->lte($at)))) {
                continue;
            }

            $option = $this->attendance->options($email, $member?->id ?? $invite?->user_id ?? $accounts->get($email)?->id, $at, $historical, $meeting->id)->get($meeting->id);
            $state = $option['state'] ?? 'pending';
            if ($invite?->status === 'declined' && $invite->responded_at?->lte($at)) {
                $state = 'not_attending';
            }
            $category = match ($state) {
                'will_attend' => 'confirmed_count',
                'not_attending', 'not_chosen' => 'not_attending_count',
                default => 'uncertain_count',
            };
            $counts[$category]++;
            if ($state === 'unchosen' || ($historical && $state === 'conflict' && $option['deadline']->lte($at))) {
                $counts['unchosen_count']++;
            }
        }

        return $counts;
    }

    public function captureDue(): void
    {
        $now = now()->toImmutable();
        Meeting::where('mode', 'offline')->where('status', '!=', 'completed')
            ->where('start_time', '<=', $now->addDay())
            ->whereNotExists(fn ($query) => $query->from('attendance_snapshots')
                ->whereColumn('meeting_id', 'meetings.id')->whereColumn('schedule_version', 'meetings.schedule_version'))
            ->eachById(function (Meeting $meeting) use ($now): void {
                DB::transaction(function () use ($meeting, $now): void {
                    $current = Meeting::lockForUpdate()->find($meeting->id);
                    if (! $current || $current->mode !== 'offline' || $current->status === 'completed') {
                        return;
                    }

                    $deadline = $current->start_time->subDay();
                    if ($deadline->gte($now) || ! $this->deadlineWasAvailable($current)) {
                        return;
                    }

                    if ($this->snapshot($current) !== null) {
                        return;
                    }

                    DB::table('attendance_snapshots')->insert([
                        'meeting_id' => $current->id,
                        'schedule_version' => $current->schedule_version,
                        'deadline_at' => $deadline,
                        'taken_at' => $now,
                        ...$this->counts($current, $deadline, historical: true),
                    ]);
                }, attempts: 3);
            });
    }

    public function deadlineWasAvailable(Meeting $meeting): bool
    {
        return ($meeting->schedule_version_at ?? $meeting->created_at)->lte($meeting->start_time->subDay());
    }

    public function snapshot(Meeting $meeting): ?object
    {
        return DB::table('attendance_snapshots')->where('meeting_id', $meeting->id)
            ->where('schedule_version', $meeting->schedule_version)->first();
    }
}
