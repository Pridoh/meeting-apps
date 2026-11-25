<?php

namespace App\Modules\Participation\Services;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Participation\Models\Invitation;
use Illuminate\Support\Facades\DB;

class MeetingParticipantService
{
    public function __construct(private AttendanceRecap $recap) {}

    public function remove(Meeting $meeting, User $participant): void
    {
        $this->recap->captureDue();
        DB::transaction(function () use ($meeting, $participant): void {
            $current = Meeting::lockForUpdate()->findOrFail($meeting->id);
            abort_unless($current->participants()->detach($participant) === 1, 404);

            Invitation::where('meeting_id', $meeting->id)->where('user_id', $participant->id)->where('status', 'accepted')->update(['status' => 'revoked']);

            $current->actionItems()
                ->where('assignee_user_id', $participant->id)
                ->where('status', '!=', 'completed')
                ->update(['assignee_user_id' => null]);
        });
    }
}
