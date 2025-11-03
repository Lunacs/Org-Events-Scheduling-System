<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketApprovedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
		$actionUrl = route('student-org.my-tickets');

		return (new MailMessage)
			->subject('Ticket Approved - ' . $this->ticket->ticket_number)
			->view('emails.tickets.ticket-approved', [
				'ticket' => $this->ticket,
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
            'title' => 'Ticket Approved',
            'message' => 'Your ticket "' . $this->ticket->title . '" has been approved',
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'type' => 'ticket_approved',
            'icon' => 's-check-circle',
            'color' => 'success',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Ticket Approved',
            'message' => 'Your ticket "' . $this->ticket->title . '" has been approved',
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'type' => 'ticket_approved',
            'icon' => 's-check-circle',
            'color' => 'success',
        ]);
    }
}
