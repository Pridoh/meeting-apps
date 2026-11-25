<?php

namespace App\Modules\Participation\Services;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MeetingParticipantService
{
    public function add(Meeting $meeting, string $email): void
    {
        DB::transaction(function () use ($meeting, $email): void {
            $current = Meeting::lockForUpdate()->findOrFail($meeting->id);
            $participant = User::whereRaw('LOWER(email) = ?', [Str::lower($email)])->first();

            if ($participant === null) {
                throw ValidationException::withMessages(['email' => 'Tidak ada akun terdaftar dengan alamat email ini.']);
            }

            if ($current->hasParticipant($participant)) {
                throw ValidationException::withMessages(['email' => 'Akun ini sudah menjadi peserta meeting.']);
            }

            $current->participants()->attach($participant);
        });
    }

    public function remove(Meeting $meeting, User $participant): void
    {
        DB::transaction(function () use ($meeting, $participant): void {
            $current = Meeting::lockForUpdate()->findOrFail($meeting->id);
            abort_unless($current->participants()->detach($participant) === 1, 404);

            $current->actionItems()
                ->where('assignee_user_id', $participant->id)
                ->where('status', '!=', 'completed')
                ->update(['assignee_user_id' => null]);
        });
    }
}
