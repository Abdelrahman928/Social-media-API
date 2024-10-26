<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LikeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $like;
    /**
     * Create a new notification instance.
     */
    public function __construct($like)
    {
        $this->like = $like;
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
        $likeableType = class_basename($this->like->likeable_type);

        if ($likeableType === 'Comment') {
            return [
                'message' => "{$this->like->user->username} liked your comment.",
                'likeable_type' => 'Comment',
                'likeable_id' => $this->like->likeable_id,
                'post_id' => $this->like->likable->post_id,
                'user_id' => $this->like->user_id,
            ];
        }else{
            return [
                'message' => "{$this->like->user->username} liked your {$likeableType}.",
                'likeable_type' => $likeableType,
                'likeable_id' => $this->like->likeable_id,
                'user_id' => $this->like->user_id,
            ];
        }
    }
}
