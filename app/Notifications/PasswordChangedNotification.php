<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $changedAt;
    protected ?string $ipAddress;

    /**
     * Create a new notification instance.
     */
    public function __construct(?string $ipAddress = null)
    {
        $this->changedAt = now()->format('F j, Y \a\t g:i A');
        $this->ipAddress = $ipAddress;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Password Has Been Changed - PLV Event Scheduling System')
            ->view('emails.password-changed', [
                'user' => $notifiable,
                'changedAt' => $this->changedAt,
                'ipAddress' => $this->ipAddress,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Password Changed',
            'message' => 'Your password was successfully changed',
            'changed_at' => $this->changedAt,
        ];
    }
}
