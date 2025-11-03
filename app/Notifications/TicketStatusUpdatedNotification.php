<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $ticket;
    public $oldStatus;
    public $newStatus;
    public $remarks;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, string $oldStatus, string $newStatus, string $remarks = null)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
		$message = $this->getStatusMessage();
		$actionUrl = $this->getActionUrl();

		return (new MailMessage)
			->subject("Ticket {$this->ticket->ticket_number} - " . $this->getStatusTitle())
			->view('emails.tickets.ticket-status-updated', [
				'ticket' => $this->ticket,
				'message' => $message,
				'oldStatus' => $this->oldStatus,
				'newStatus' => $this->newStatus,
				'remarks' => $this->remarks,
				'actionUrl' => $actionUrl,
				'actionText' => 'View Ticket',
				'title' => $this->getStatusTitle(),
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
            'title' => $this->getStatusTitle(),
            'message' => $this->getStatusMessage(),
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'type' => 'ticket_status_' . $this->newStatus,
            'icon' => $this->getStatusIcon(),
            'color' => $this->getStatusColor(),
            'remarks' => $this->remarks,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => $this->getStatusTitle(),
            'message' => $this->getStatusMessage(),
            'ticket_id' => $this->ticket->ticket_id,
            'ticket_number' => $this->ticket->ticket_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'type' => 'ticket_status_' . $this->newStatus,
            'icon' => $this->getStatusIcon(),
            'color' => $this->getStatusColor(),
            'remarks' => $this->remarks,
        ]);
    }

    /**
     * Get the status-specific title
     */
    private function getStatusTitle(): string
    {
        return match($this->newStatus) {
            'received' => 'Ticket Received',
            'gso_review' => 'Under GSO Review',
            'pending_osa_approval' => 'Pending OSA Approval',
            'approved' => 'Ticket Approved',
            'rejected' => 'Ticket Rejected',
            'for_rescheduling' => 'Rescheduling Requested',
            'rescheduled' => 'Ticket Rescheduled',
            'needs_revision' => 'Revision Required',
            'amended' => 'Ticket Amended',
            default => 'Ticket Status Updated',
        };
    }

    /**
     * Get the status-specific message
     */
    private function getStatusMessage(): string
    {
        $ticketTitle = $this->ticket->title;
        
        return match($this->newStatus) {
            'received' => "Your ticket \"{$ticketTitle}\" has been received and is under review.",
            'gso_review' => "Your ticket \"{$ticketTitle}\" is now being reviewed by GSO.",
            'pending_osa_approval' => "Your ticket \"{$ticketTitle}\" is pending OSA approval.",
            'approved' => "Your ticket \"{$ticketTitle}\" has been approved!",
            'rejected' => "Your ticket \"{$ticketTitle}\" has been rejected.",
            'for_rescheduling' => "Your ticket \"{$ticketTitle}\" needs to be rescheduled.",
            'rescheduled' => "Your ticket \"{$ticketTitle}\" has been rescheduled.",
            'needs_revision' => "Your ticket \"{$ticketTitle}\" requires revision.",
            'amended' => "Your ticket \"{$ticketTitle}\" has been amended.",
            default => "Your ticket \"{$ticketTitle}\" status has been updated to: {$this->newStatus}.",
        };
    }

    /**
     * Get the status-specific icon
     */
    private function getStatusIcon(): string
    {
        return match($this->newStatus) {
            'received' => 's-inbox',
            'gso_review' => 's-eye',
            'pending_osa_approval' => 's-clock',
            'approved' => 's-check-circle',
            'rejected' => 's-x-circle',
            'for_rescheduling' => 's-arrow-path',
            'rescheduled' => 's-calendar-days',
            'needs_revision' => 's-pencil-square',
            'amended' => 's-pencil-square',
            default => 's-bell',
        };
    }

    /**
     * Get the status-specific color
     */
    private function getStatusColor(): string
    {
        return match($this->newStatus) {
            'received' => 'info',
            'gso_review' => 'secondary',
            'pending_osa_approval' => 'warning',
            'approved' => 'success',
            'rejected' => 'error',
            'for_rescheduling' => 'warning',
            'rescheduled' => 'success',
            'needs_revision' => 'warning',
            'amended' => 'info',
            default => 'primary',
        };
    }

    /**
     * Get the appropriate action URL based on user role
     */
    private function getActionUrl(): string
    {
        // You can customize this based on the notifiable user's role
        return route('student-org.my-tickets');
    }
}
