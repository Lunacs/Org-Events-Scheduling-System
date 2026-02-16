<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketSubmittedNotification extends Notification implements ShouldBroadcast, ShouldQueue
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
        $channels = ['database', 'broadcast'];

        if ($notifiable instanceof \App\Models\User && $notifiable->shouldReceiveEmailNotification('ticket_updates')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $actionUrl = $this->getActionUrl($notifiable);

        return (new MailMessage)
            ->subject('New Ticket Submitted - ' . $this->ticket->ticket_number)
            ->view('emails.tickets.ticket-submitted', [
                'ticket' => $this->ticket,
                'actionUrl' => $actionUrl,
                'actionText' => 'View Tickets',
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
            'title' => 'New Ticket Submitted',
            'message' => 'A new ticket has been submitted: ' . $this->ticket->title,
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'type' => 'ticket_submitted',
            'icon' => 's-document-plus',
            'color' => 'primary',
            'action_url' => $this->getActionUrl($notifiable),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    private function getActionUrl(object $notifiable): string
    {
        // OSA users go to ticket management
        if ($notifiable->isOsa() || $notifiable->isSuperadmin()) {
            return route('osa.ticket-review.show', $this->ticket->ticket_number);
        }

        // Student Org users go to their tickets page
        if ($notifiable->isStudentOrg()) {
            return route('student-org.my-tickets');
        }

        // GSO users go to ticket details
        if ($notifiable->isGso()) {
            return route('gso.ticket-details', ['ticketNumber' => $this->ticket->ticket_number]);
        }

        return route('osa.ticket-management');
    }
}
