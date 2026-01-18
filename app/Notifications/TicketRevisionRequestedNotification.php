<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketRevisionRequestedNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public Ticket $ticket;

    public ?string $remarks;

    public function __construct(Ticket $ticket, ?string $remarks = null)
    {
        $this->ticket = $ticket;
        $this->remarks = $remarks;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $actionUrl = route('student-org.my-tickets');

        return (new MailMessage)
            ->subject('Revision Requested - ' . $this->ticket->ticket_number)
            ->view('emails.tickets.ticket-revision-requested', [
                'ticket' => $this->ticket,
                'remarks' => $this->remarks,
                'actionUrl' => $actionUrl,
                'actionText' => 'View and Revise Ticket',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Revision Required',
            'message' => 'Your ticket "' . $this->ticket->title . '" needs revision. ' . ($this->remarks ?: 'Please review the comments.'),
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'type' => 'ticket_revision_requested',
            'icon' => 's-pencil-square',
            'color' => 'warning',
            'remarks' => $this->remarks,
            'action_url' => $this->getActionUrl($notifiable),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    private function getActionUrl(object $notifiable): string
    {
        // Student Org users go to their tickets page to revise
        if ($notifiable->isStudentOrg()) {
            return route('student-org.my-tickets');
        }

        // OSA users go to ticket review
        if ($notifiable->isOsa() || $notifiable->isSuperadmin()) {
            return route('osa.ticket-review.show', $this->ticket->ticket_number);
        }

        return route('student-org.my-tickets');
    }
}
