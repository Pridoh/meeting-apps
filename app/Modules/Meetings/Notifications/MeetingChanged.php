<?php

namespace App\Modules\Meetings\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $meetingId,
        public readonly string $title,
        public readonly bool $cancelled,
    ) {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
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
            ->action('Lihat meeting', route('meetings.show', $this->meetingId));
    }
}
