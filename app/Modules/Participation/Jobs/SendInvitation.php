<?php

namespace App\Modules\Participation\Jobs;

use App\Modules\Participation\Models\Invitation;
use App\Modules\Participation\Models\InvitationDelivery;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendInvitation implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public int $deliveryId, public string $token)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $delivery = InvitationDelivery::findOrFail($this->deliveryId);
        if ($delivery->status !== 'queued') {
            return;
        }
        $invite = Invitation::findOrFail($delivery->invitation_id);
        if ($invite->status !== 'pending' || $invite->expires_at->isPast() || ! $invite->meeting || ! hash_equals($invite->token_hash, hash('sha256', $this->token))) {
            $this->failed();

            return;
        }
        try {
            if (in_array(config('mail.default'), ['log', 'array'], true)) {
                throw new \RuntimeException('SMTP diperlukan.');
            }
            $link = route('invitation.show').'#'.$this->token;
            Mail::raw("Anda diundang ke meeting RAPATIN. Buka tautan ini untuk melihat jadwal dan merespons:\n".$link, fn ($m) => $m->to($invite->email)->subject('Undangan meeting RAPATIN'));
        } catch (Throwable) {
            $this->failed();

            return;
        }
        $delivery->forceFill(['status' => 'sent', 'attempted_at' => now()])->save();
    }

    public function failed(?Throwable $error = null): void
    {
        InvitationDelivery::whereKey($this->deliveryId)->where('status', 'queued')->update(['status' => 'failed', 'attempted_at' => now(), 'error_message' => 'Pengiriman gagal atau undangan tidak berlaku.']);
    }
}
