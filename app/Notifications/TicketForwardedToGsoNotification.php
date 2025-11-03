<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketForwardedToGsoNotification extends Notification implements ShouldBroadcast
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
		$actionUrl = route('gso.ticket-review');

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
        return [
            'title' => 'Ticket Forwarded to GSO',
            'message' => 'Ticket "' . $this->ticket->title . '" (' . $this->ticket->ticket_number . ') forwarded to GSO for review.',
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'type' => 'ticket_forwarded_to_gso',
            'icon' => 's-arrow-right',
            'color' => 'info',
            'remarks' => $this->remarks,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}

