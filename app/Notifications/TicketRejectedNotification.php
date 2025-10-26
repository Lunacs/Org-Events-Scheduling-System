<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketRejectedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $ticket;
    public $remarks;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, string $remarks = null)
    {
        $this->ticket = $ticket;
        $this->remarks = $remarks;
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
        return (new MailMessage)
            ->line('Your ticket has been rejected.')
            ->line($this->remarks ? 'Remarks: ' . $this->remarks : '')
            ->action('View Ticket', route('student-org.my-tickets'))
            ->line('Ticket Number: ' . $this->ticket->ticket_number);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Ticket Rejected',
            'message' => 'Your ticket "' . $this->ticket->title . '" has been rejected',
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'type' => 'ticket_rejected',
            'icon' => 's-x-circle',
            'color' => 'error',
            'remarks' => $this->remarks,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Ticket Rejected',
            'message' => 'Your ticket "' . $this->ticket->title . '" has been rejected',
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'type' => 'ticket_rejected',
            'icon' => 's-x-circle',
            'color' => 'error',
            'remarks' => $this->remarks,
        ]);
    }
}
