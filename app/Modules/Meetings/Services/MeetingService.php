<?php

namespace App\Modules\Meetings\Services;

use App\Modules\Meetings\Events\MeetingScheduleChanged;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Meetings\Notifications\MeetingChanged;
use App\Modules\Participation\Models\Invitation;
use App\Modules\Participation\Services\AttendanceRecap;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class MeetingService
{
    public function __construct(private AttendanceRecap $recap) {}

    /** @param array{title: string, description?: string|null, start_time: string, end_time: string, timezone: string, mode: string, location?: string|null} $details */
    public function update(Meeting $meeting, array $details): void
    {
        $this->recap->captureDue();
        DB::transaction(function () use ($meeting, $details): void {
            $current = Meeting::lockForUpdate()->findOrFail($meeting->id);
            $current->fill($details);
            $scheduleChanged = $current->isDirty(['start_time', 'end_time', 'mode']);
            if ($scheduleChanged) {
                $current->schedule_version++;
                $current->schedule_version_at = now();
            }

            $current->save();

            if ($scheduleChanged) {
                MeetingScheduleChanged::dispatch($current->id, $current->schedule_version, now()->toImmutable());
                $this->notifyParticipants($current, false);
            }
        });
    }

    public function updateStatus(Meeting $meeting, string $status, int $actorId): void
    {
        $this->recap->captureDue();
        DB::transaction(function () use ($meeting, $status, $actorId): void {
            $current = Meeting::lockForUpdate()->findOrFail($meeting->id);
            $nextStatus = match ($current->status) {
                'scheduled' => 'ongoing',
                'ongoing' => 'completed',
                default => null,
            };

            if ($status !== $nextStatus) {
                throw ValidationException::withMessages(['status' => 'Status hanya dapat bergerak dari terjadwal ke berlangsung, lalu selesai.']);
            }

            $current->status = $status;
            $current->save();
            DB::afterCommit(fn () => Log::info('Meeting status updated', [
                'meeting_id' => $current->id,
                'actor_id' => $actorId,
                'status' => $status,
            ]));
        });
    }

    public function delete(Meeting $meeting): void
    {
        $this->recap->captureDue();
        DB::transaction(function () use ($meeting): void {
            $current = Meeting::lockForUpdate()->findOrFail($meeting->id);
            $this->notifyParticipants($current, true);
            Invitation::where('meeting_id', $current->id)->whereIn('status', ['pending', 'accepted'])->update(['status' => 'revoked']);
            $current->delete();
        });
    }

    private function notifyParticipants(Meeting $meeting, bool $cancelled): void
    {
        $participants = $meeting->participants()->get();
        Notification::send($participants, new MeetingChanged($meeting->id, $meeting->title, $cancelled));

        $pending = Invitation::whereBelongsTo($meeting)->where('status', 'pending')
            ->where('expires_at', '>', now())->whereNotIn('email', $participants->pluck('email'))->pluck('email');
        foreach ($pending as $email) {
            Notification::route('mail', $email)->notify(new MeetingChanged($meeting->id, $meeting->title, $cancelled, pending: true));
        }
    }
}
