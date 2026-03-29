<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class EmailChangeRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $newEmail
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Generate a signed cancel URL for the email change.
     */
    protected function cancelUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'email.cancel-change',
            now()->addMinutes(60),
            [
                'id' => $user->user_id,
                'hash' => sha1($user->pending_email),
            ]
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $cancelUrl = $this->cancelUrl($notifiable);

        return (new MailMessage)
            ->subject('Email Change Request - PLV Event Scheduling System')
            ->view('emails.email-change-requested', [
                'user' => $notifiable,
                'newEmail' => $this->newEmail,
                'cancelUrl' => $cancelUrl,
            ]);
    }
}
