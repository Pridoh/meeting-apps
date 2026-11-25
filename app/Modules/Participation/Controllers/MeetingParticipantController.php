<?php

namespace App\Modules\Participation\Controllers;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Participation\Requests\AddMeetingParticipantRequest;
use App\Modules\Participation\Requests\RemoveMeetingParticipantRequest;
use App\Modules\Participation\Services\InvitationService;
use App\Modules\Participation\Services\MeetingParticipantService;
use Illuminate\Http\RedirectResponse;

class MeetingParticipantController
{
    public function store(AddMeetingParticipantRequest $request, Meeting $meeting, InvitationService $service): RedirectResponse
    {
        $service->issue($meeting, $request->validated('email'));

        return to_route('meetings.show', $meeting)->with('success', 'Undangan masuk antrean.');
    }

    public function destroy(RemoveMeetingParticipantRequest $request, Meeting $meeting, User $participant, MeetingParticipantService $service): RedirectResponse
    {
        $service->remove($meeting, $participant);

        return to_route('meetings.show', $meeting)->with('success', 'Peserta dihapus.');
    }
}
