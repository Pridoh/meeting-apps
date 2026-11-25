<?php

namespace App\Modules\Participation\Services;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Participation\Jobs\SendInvitation;
use App\Modules\Participation\Models\Invitation;
use App\Modules\Participation\Models\InvitationDelivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    public function __construct(private AttendanceRecap $recap) {}

    public function issue(Meeting $meeting, string $email, ?int $resendId = null): void
    {
        $this->recap->captureDue();
        DB::transaction(function () use ($meeting, $email, $resendId) {
            $meeting = Meeting::lockForUpdate()->findOrFail($meeting->id);
            $email = Str::lower(trim($email));
            $invites = Invitation::where('meeting_id', $meeting->id);
            (clone $invites)->where('status', 'pending')->where('expires_at', '<=', now())->update(['status' => 'expired']);
            if ($resendId !== null) {
                $invite = $invites->findOrFail($resendId);
                if ($invite->status !== 'pending') {
                    throw ValidationException::withMessages(['email' => 'Hanya undangan menunggu yang dapat dikirim ulang.']);
                }
                $zone = $meeting->timezone;
                $count = $invite->last_sent_at->timezone($zone)->isSameDay(now($zone)) ? $invite->resend_count : 0;
                if ($count >= 5 || $invite->last_sent_at->gt(now()->subMinute())) {
                    throw ValidationException::withMessages(['email' => 'Batas kirim ulang: jeda satu menit dan maksimal lima kali per hari.']);
                }
                $invite->resend_count = $count + 1;
            } else {
                if ($meeting->participants()->whereRaw('LOWER(email) = ?', [$email])->exists() || $invites->where('active_email', $email)->exists()) {
                    throw ValidationException::withMessages(['email' => 'Alamat ini sudah menjadi peserta atau mempunyai undangan aktif.']);
                }
                $invite = new Invitation;
                $invite->meeting_id = $meeting->id;
                $invite->email = $email;
                $invite->status = 'pending';
            }
            $token = bin2hex(random_bytes(32));
            $invite->token_hash = hash('sha256', $token);
            $invite->expires_at = now()->addDays(7);
            $invite->last_sent_at = now();
            $invite->save();
            $delivery = new InvitationDelivery;
            $delivery->invitation_id = $invite->id;
            $delivery->save();
            SendInvitation::dispatch($delivery->id, $token);
        });
    }

    public function revoke(Meeting $meeting, int $id): void
    {
        $this->recap->captureDue();
        DB::transaction(function () use ($meeting, $id) {
            Meeting::lockForUpdate()->findOrFail($meeting->id);
            $invite = Invitation::where('meeting_id', $meeting->id)->findOrFail($id);
            abort_unless($invite->status === 'pending', 409);
            $invite->status = 'revoked';
            $invite->save();
        });
    }

    public function respond(Invitation $invite, string $hash, User $user, string $answer): void
    {
        $this->recap->captureDue();
        DB::transaction(function () use ($invite, $hash, $user, $answer) {
            User::lockForUpdate()->findOrFail($user->id);
            $meeting = Meeting::lockForUpdate()->findOrFail($invite->meeting_id);
            $invite = Invitation::lockForUpdate()->findOrFail($invite->id);
            abort_unless(hash_equals($invite->token_hash, $hash) && $invite->expires_at->isFuture() && in_array($invite->status, ['pending', 'accepted', 'declined'], true), 410);
            abort_unless($user->hasVerifiedEmail() && Str::lower($user->email) === $invite->email, 403);
            if ($invite->status === $answer) {
                return;
            }
            abort_unless($invite->status === 'pending', 409);
            $invite->forceFill(['status' => $answer, 'responded_at' => now(), 'user_id' => $user->id])->save();
            if ($answer === 'accepted') {
                $meeting->participants()->syncWithoutDetaching([$user->id => ['invitation_id' => $invite->id]]);
            }
        });
    }
}
