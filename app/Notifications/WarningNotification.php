<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $warning;
    /**
     * Create a new notification instance.
     */
    public function __construct($warning)
    {
        $this->warning = $warning;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('You recieved a warning.')
                    ->line("your {$this->warning->content_type} has been removed for violating our community standards and your account recieved a warning")
                    ->line('keep in mind recieving 3 warnings will get your account restricted.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "your {$this->warning->content_type} has been removed for violating our community standards and your account recieved a warning",
            'deleted_content_type' => $this->warning->content_type,
            'warning_id' => $this->warning->id
        ];
    }
}
