<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketForwardedToGsoNotification extends Notification implements ShouldBroadcast, ShouldQueue
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
            ->subject('New Ticket Forwarded for GSO Review - ' . $this->ticket->ticket_number)
            ->view('emails.tickets.ticket-forwarded-gso', [
                'ticket' => $this->ticket,
                'remarks' => $this->remarks,
                'actionUrl' => $actionUrl,
                'actionText' => 'Review Tickets',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $message = $notifiable->isGso()
            ? 'Ticket "' . $this->ticket->title . '" (' . $this->ticket->ticket_number . ') forwarded to GSO for review.'
            : 'Your ticket "' . $this->ticket->title . '" has been forwarded to GSO for review.';

        return [
            'title' => 'Ticket Forwarded to GSO',
            'message' => $message,
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'type' => 'ticket_forwarded_to_gso',
            'icon' => 's-arrow-right',
            'color' => 'info',
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

        // GSO users go to ticket review
        if ($notifiable->isGso()) {
            return route('gso.ticket-review');
        }

        // OSA users go to ticket review
        if ($notifiable->isOsa() || $notifiable->isSuperadmin()) {
            return route('osa.ticket-review.show', $this->ticket->ticket_number);
        }

        return route('gso.ticket-review');
    }
}
