<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GsoForRevisionTicketNotification extends Notification implements ShouldBroadcast, ShouldQueue
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
        $channels = ['database', 'broadcast'];

        if ($notifiable instanceof \App\Models\User && $notifiable->shouldReceiveEmailNotification('ticket_updates')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $actionUrl = $this->getActionUrl($notifiable);

        return (new MailMessage)
            ->subject('GSO For Revision Ticket - ' . $this->ticket->ticket_number)
            ->view('emails.tickets.gso-for-revision', [
                'ticket' => $this->ticket,
                'remarks' => $this->remarks,
                'actionUrl' => $actionUrl,
                'actionText' => 'Review Ticket',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $message = $notifiable->isOsa() || $notifiable->isSuperadmin()
            ? 'GSO has put the ticket for revision: "' . $this->ticket->title . '". Please review for final decision.'
            : 'GSO has put your ticket for revision: "' . $this->ticket->title . '". Pending OSA final decision.';

        return [
            'title' => 'GSO put Ticket For revision',
            'message' => $message,
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'type' => 'gso_',
            'icon' => 's-x-circle',
            'color' => 'error',
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
        // Student Org users go to their tickets page
        if ($notifiable->isStudentOrg()) {
            return route('student-org.my-tickets');
        }

        // OSA users go to ticket review for final decision
        if ($notifiable->isOsa() || $notifiable->isSuperadmin()) {
            return route('osa.ticket-review.show', $this->ticket->ticket_number);
        }

        // GSO users go to ticket details
        if ($notifiable->isGso()) {
            return route('gso.ticket-details', ['ticketNumber' => $this->ticket->ticket_number]);
        }

        return route('student-org.my-tickets');
    }
}
