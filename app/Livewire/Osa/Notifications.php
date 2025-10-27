<?php

namespace App\Livewire\Osa;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

class Notifications extends Component
{
    #[Title('Notifications - OSA Admin')]
    #[Layout('components.layouts.app')]

    public $search = '';
    public $typeFilter = '';
    public $statusFilter = '';
    public $notifications = [];
    public $unreadCount = 0;
    public $totalCount = 0;
    public $todayCount = 0;
    public $weekCount = 0;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = auth()->user();
        
        if (!$user) {
            return;
        }

        // Build query
        $query = $user->notifications()->latest();

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('data', 'like', '%' . $this->search . '%');
            });
        }

        // Apply type filter
        if ($this->typeFilter) {
            $query->where('data', 'like', '%"type":"' . $this->typeFilter . '"%');
        }

        // Apply status filter
        if ($this->statusFilter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->statusFilter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Get notifications
        $this->notifications = $query->get();

        // Get counts
        $this->unreadCount = $user->unreadNotifications()->count();
        $this->totalCount = $user->notifications()->count();
        
        // Count today's notifications
        $this->todayCount = $user->notifications()
            ->whereDate('created_at', today())
            ->count();
        
        // Count this week's notifications
        $this->weekCount = $user->notifications()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
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
        session()->flash('success', 'All notifications marked as read.');
    }

    public function openNotificationSettings()
    {
        // Implement settings modal logic
        session()->flash('info', 'Notification settings coming soon.');
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->loadNotifications();
    }

    public function updatedSearch()
    {
        $this->loadNotifications();
    }

    public function updatedTypeFilter()
    {
        $this->loadNotifications();
    }

    public function updatedStatusFilter()
    {
        $this->loadNotifications();
    }

    public function loadMore()
    {
        // Implement pagination if needed
    }

    #[On('notifications-updated')]
    public function refreshNotifications()
    {
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.osa.notifications');
    }
}

