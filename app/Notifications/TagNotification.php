<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TagNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $taggable;
    protected $tagger;
    /**
     * Create a new notification instance.
     */
    public function __construct($tagger, $taggable)
    {
        $this->tagger = $tagger;
        $this->taggable = $taggable;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $taggable = class_basename(get_class($this->taggable));
        return [
            'message' => "{$this->tagger->username} mentioned you in a {$taggable}",
            'taggable_type' => $taggable,
            'taggable_id' => $this->taggable->id,
            'tagger_id' => $this->tagger->id
        ];
    }
}
