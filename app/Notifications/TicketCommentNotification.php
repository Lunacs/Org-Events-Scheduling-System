<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $ticket;

    public $comment;

    public $commenter;

    private ?array $channels = null;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, TicketComment $comment, User $commenter, ?array $channels = null)
    {
        $this->ticket = $ticket;
        $this->comment = $comment;
        $this->commenter = $commenter;
        $this->channels = $channels;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels ?? ['database', 'mail', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $actionUrl = $this->getActionUrl($notifiable);

        return (new MailMessage)
            ->subject("New Comment on Ticket {$this->ticket->ticket_number}")
            ->view('emails.tickets.ticket-comment', [
                'ticket' => $this->ticket,
                'comment' => $this->comment,
                'commenter' => $this->commenter,
                'greetingName' => $notifiable->name,
                'actionUrl' => $actionUrl,
                'actionText' => 'View Ticket',
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
            'title' => 'New Comment on Ticket',
            'type' => 'ticket_comment',
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'ticket_title' => $this->ticket->title,
            'comment_id' => $this->comment->id,
            'comment_content' => $this->comment->content,
            'commenter_id' => $this->commenter->user_id,
            'commenter_name' => $this->commenter->name,
            'commenter_role' => $this->commenter->role_display,
            'message' => "{$this->commenter->name} commented on ticket {$this->ticket->ticket_number}",
            'action_url' => $this->getActionUrl($notifiable),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Comment on Ticket',
            'type' => 'ticket_comment',
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'ticket_title' => $this->ticket->title,
            'comment_id' => $this->comment->id,
            'comment_content' => $this->comment->content,
            'commenter_id' => $this->commenter->user_id,
            'commenter_name' => $this->commenter->name,
            'commenter_role' => $this->commenter->role_display,
            'message' => "{$this->commenter->name} commented on ticket {$this->ticket->ticket_number}",
            'action_url' => $this->getActionUrl($notifiable),
        ]);
    }

    /**
     * Get the action URL based on user role
     */
    private function getActionUrl(object $notifiable): string
    {
        // Student Org users go to their tickets page
        if ($notifiable->isStudentOrg()) {
            return route('student-org.my-tickets');
        }

        // OSA users go to ticket review
        if ($notifiable->isOsa() || $notifiable->isSuperadmin()) {
            return route('osa.ticket-review.show', $this->ticket->ticket_number);
        }

        // GSO users go to GSO ticket details
        if ($notifiable->isGso()) {
            return route('gso.ticket-details', ['ticket' => $this->ticket->ticket_id]);
        }

        return route('student-org.my-tickets');
    }
}
