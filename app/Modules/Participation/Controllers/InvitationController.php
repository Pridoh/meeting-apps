<?php

namespace App\Modules\Participation\Controllers;

use App\Modules\Meetings\Models\Meeting;
use App\Modules\Participation\Models\Invitation;
use App\Modules\Participation\Services\AttendanceService;
use App\Modules\Participation\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InvitationController
{
    private function invitation(Request $request): Invitation
    {
        $invite = Invitation::with('meeting.organizer')->where('token_hash', $request->session()->get('invite_hash', ''))->first();
        abort_unless($invite && $invite->meeting && $invite->expires_at->isFuture() && in_array($invite->status, ['pending', 'accepted', 'declined'], true), 410, 'Undangan tidak berlaku. Minta undangan baru kepada Organizer.');

        return $invite;
    }

    public function open(Request $request): RedirectResponse
    {
        $token = $request->input('token');
        abort_unless(is_string($token) && strlen($token) === 64, 410);
        $request->session()->put('invite_hash', hash('sha256', $token));
        $this->invitation($request);

        return to_route('invitation.show');
    }

    public function show(Request $request, AttendanceService $attendance): Response
    {
        // The landing page also exchanges a new fragment if an older invitation is in the session.
        try {
            $invite = $this->invitation($request);
        } catch (HttpException $e) {
            if ($e->getStatusCode() !== 410) {
                throw $e;
            }$invite = null;
        }
        if ($invite && ! $request->user()) {
            $request->session()->put('url.intended', route('invitation.show'));
        }

        $user = $request->user();
        $hasConflict = $invite && $user?->hasVerifiedEmail() && strtolower($user->email) === $invite->email
            && $attendance->hasConflict($invite->email, $user->id, $invite->meeting);

        return response()->view('invitation.show', compact('invite', 'hasConflict'))->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function respond(Request $request, string $answer, InvitationService $service): RedirectResponse
    {
        $invite = $this->invitation($request);
        abort_unless($request->integer('invitation_id') === $invite->id, 409, 'Buka kembali undangan yang ingin direspons.');
        $service->respond($invite, $request->session()->get('invite_hash'), $request->user(), $answer);

        return to_route('invitation.show');
    }

    public function resend(Meeting $meeting, int $id, InvitationService $service): RedirectResponse
    {
        $service->issue($meeting, '', $id);

        return to_route('meetings.show', $meeting)->with('success', 'Pengiriman ulang masuk antrean.');
    }

    public function revoke(Meeting $meeting, int $id, InvitationService $service): RedirectResponse
    {
        $service->revoke($meeting, $id);

        return to_route('meetings.show', $meeting)->with('success', 'Undangan dicabut.');
    }
}
