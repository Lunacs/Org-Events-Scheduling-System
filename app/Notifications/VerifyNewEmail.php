<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyNewEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Generate a signed verification URL for the new email.
     */
    protected function verificationUrl(): string
    {
        return URL::temporarySignedRoute(
            'email.verify-new',
            now()->addMinutes(60),
            [
                'id' => $this->user->user_id,
                'hash' => sha1($this->user->pending_email),
            ]
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl();

        return (new MailMessage)
            ->subject('Verify Your New Email Address - PLV Event Scheduling System')
            ->view('emails.verify-new-email', [
                'user' => $this->user,
                'newEmail' => $this->user->pending_email,
                'verificationUrl' => $verificationUrl,
            ]);
    }
}
