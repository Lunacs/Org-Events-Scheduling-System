<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GsoApprovedTicketNotification extends Notification implements ShouldBroadcast
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
        $actionUrl = $this->getActionUrl($notifiable);

        return (new MailMessage)
            ->subject('GSO Approved Ticket - ' . $this->ticket->ticket_number)
            ->view('emails.tickets.gso-approved', [
                'ticket' => $this->ticket,
                'remarks' => $this->remarks,
                'actionUrl' => $actionUrl,
                'actionText' => 'Review for Final Approval',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $message = $notifiable->isOsa() || $notifiable->isSuperadmin()
            ? 'GSO has approved ticket "' . $this->ticket->title . '". Awaiting your final approval.'
            : 'GSO has approved your ticket "' . $this->ticket->title . '". Pending OSA final approval.';

        return [
            'title' => 'GSO Approved Ticket',
            'message' => $message,
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'type' => 'gso_approved',
            'icon' => 's-check-badge',
            'color' => 'success',
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

        // OSA users go to ticket review for final approval
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

