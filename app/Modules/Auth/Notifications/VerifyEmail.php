<?php

namespace App\Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyEmail extends \Illuminate\Auth\Notifications\VerifyEmail implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->afterCommit();
    }

    public function toMail($notifiable)
    {
        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            throw new \RuntimeException('Konfigurasikan SMTP untuk verifikasi.');
        }

        return parent::toMail($notifiable);
    }
}
