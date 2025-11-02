<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

class Notifications extends Component
{
    #[Title('Notifications - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]

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

        $query = $user->notifications()->latest();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('data->title', 'like', '%' . $this->search . '%')
                  ->orWhere('data->message', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->typeFilter) {
            $query->where('data->type', $this->typeFilter);
        }

        if ($this->statusFilter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->statusFilter === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($this->statusFilter === 'archived') {
            // Define how archived notifications are identified
            // Option 1: Use a dedicated column
            $query->where('archived', true);
            // Option 2: Use JSON data field
            // $query->where('data->archived', true);
        }

        $this->notifications = $query->get();

        $this->unreadCount = $user->unreadNotifications()->count();
        $this->totalCount = $user->notifications()->count();
        $this->todayCount = $user->notifications()->whereDate('created_at', today())->count();
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

    #[On('notifications-updated')]
    public function refreshNotifications()
    {
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.student-org.notifications', [
            'typeOptions' => [
                ['id' => '', 'name' => 'All Types'],
                ['id' => 'ticket_status_approved', 'name' => 'Approvals'],
                ['id' => 'ticket_status_rejected', 'name' => 'Rejections'],
                ['id' => 'ticket_status_needs_revision', 'name' => 'Revision Required'],
                ['id' => 'reminders', 'name' => 'Reminders'],
                ['id' => 'announcements', 'name' => 'Announcements'],
                ['id' => 'ticket_status_reschedule_update', 'name' => 'Reschedule Updates'],
            ]
        ]);
    }
}
