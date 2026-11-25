<?php

namespace App\Modules\Meetings\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $meetingId,
        public readonly string $title,
        public readonly bool $cancelled,
        public readonly bool $pending = false,
    ) {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(User|AnonymousNotifiable $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User|AnonymousNotifiable $notifiable): MailMessage
    {
        $title = preg_replace_callback('/[[:punct:]]/', fn (array $match): string => '\\'.$match[0], $this->title);
        if ($this->cancelled) {
            return (new MailMessage)
                ->subject('Meeting dibatalkan')
                ->line("Meeting **{$title}** telah dibatalkan oleh organizer.");
        }

        return (new MailMessage)
            ->subject('Jadwal meeting diperbarui')
            ->line("Detail jadwal atau mode meeting **{$title}** telah berubah.")
            ->action(
                $this->pending ? 'Lihat pilihan kehadiran' : 'Buka meeting',
                $this->pending ? route('attendance.index') : route('meetings.show', $this->meetingId),
            );
    }
}
