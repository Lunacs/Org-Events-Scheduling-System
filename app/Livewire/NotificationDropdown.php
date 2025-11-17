<?php

namespace App\Livewire;

use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;

#[Lazy]
class NotificationDropdown extends Component
{
    public $notifications = [];

    public $unreadCount = 0;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="btn btn-ghost btn-sm btn-circle relative tooltip tooltip-bottom" data-tip="Notifications">
            <x-heroicon-s-bell class="h-5 w-5" />
            <span class="loading loading-spinner loading-xs absolute top-0 right-0 opacity-40"></span>
        </div>
        HTML;
    }

    public function loadNotifications()
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Get latest notifications
        $this->notifications = $user->notifications()->latest()->take(5)->get();

        // Count unread notifications
        $this->unreadCount = $user->unreadNotifications()->count();
    }

    public function markAsRead($notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function handleNotificationClick($notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($notificationId);

        if (! $notification) {
            return null;
        }

        // Mark as read
        $notification->markAsRead();
        $this->loadNotifications();

        // Get the appropriate URL based on notification type
        $url = $this->getNotificationUrl($notification);

        // Dispatch navigation event
        if ($url) {
            $this->dispatch('navigate-to', ['url' => $url]);
        }

        return $url;
    }

    protected function getNotificationUrl($notification): ?string
    {
        $data = $notification->data;
        $type = $data['type'] ?? '';
        $ticketNumber = $data['ticket_number'] ?? null;
        $user = auth()->user();

        // If the notification already has an action_url, use that
        if (isset($data['action_url']) && ! empty($data['action_url'])) {
            return $data['action_url'];
        }

        // Route based on notification type and user role
        if (str_starts_with($type, 'ticket_') && $ticketNumber) {
            return $this->getTicketNotificationUrl($type, $ticketNumber, $user);
        }

        // Default fallback - go to notifications page or dashboard
        return $this->getDefaultUrl($user);
    }

    /**
     * Get URL for ticket-related notifications
     */
    private function getTicketNotificationUrl(string $type, string $ticketNumber, $user): string
    {
        // Student Org users always go to their tickets page
        if ($user->isStudentOrg()) {
            return route('student-org.my-tickets');
        }

        // GSO-specific notifications
        if (in_array($type, ['ticket_forwarded_to_gso', 'gso_approved', 'gso_rejected'])) {
            if ($user->isGso()) {
                return route('gso.ticket-review');
            }
        }

        // OSA and Superadmin users go to ticket review
        if ($user->isOsa() || $user->isSuperadmin()) {
            return route('osa.ticket-review.show', $ticketNumber);
        }

        // GSO users go to ticket details
        if ($user->isGso()) {
            return route('gso.ticket-details', ['ticketNumber' => $ticketNumber]);
        }

        return $this->getDefaultUrl($user);
    }

    /**
     * Get default fallback URL based on user role
     */
    private function getDefaultUrl($user): string
    {
        return match (true) {
            $user->isOsa() => route('admin.dashboard'),
            $user->isSuperadmin() => route('superadmin.dashboard'),
            $user->isGso() => route('gso.dashboard'),
            $user->isStudentOrg() => route('student-org.dashboard'),
            default => route('student-org.dashboard'),
        };
    }

    public function markAllAsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    #[On('refresh-notifications')]
    public function refreshNotifications()
    {
        $this->loadNotifications();
    }

    #[On('notification-received')]
    public function handleNewNotification($notificationData)
    {
        $this->loadNotifications();

        // Show toast notification
        $this->dispatch('toast', [
            'type' => 'info',
            'title' => $notificationData['title'] ?? 'New Notification',
            'description' => $notificationData['message'] ?? 'You have a new notification',
        ]);
    }

    #[On('ticket-status-updated')]
    public function handleTicketStatusUpdate($ticketId, $newStatus)
    {
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notification-dropdown');
    }
}
