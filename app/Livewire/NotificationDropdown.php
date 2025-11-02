<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public $notifications = [];

    public $unreadCount = 0;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Get latest notifications
        $this->notifications = $user->notifications()->latest()->take(3)->get();

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
