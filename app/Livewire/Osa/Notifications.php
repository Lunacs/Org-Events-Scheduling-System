<?php

namespace App\Livewire\Osa;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

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
        // Load counts first (lightweight)
        $this->loadCounts();
        // Load only recent notifications (limit to 20 for faster initial load)
        $this->loadNotifications(true);
    }

    public function loadCounts()
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Optimize: Load all counts in a single query with aggregations
        $counts = \Illuminate\Support\Facades\Cache::remember(
            "osa_notifications_counts_{$user->user_id}",
            60, // 1 minute cache
            function () use ($user) {
                $today = today();
                $weekStart = now()->startOfWeek();
                $weekEnd = now()->endOfWeek();

                $allNotifications = $user->notifications();

                return [
                    'unread' => (clone $allNotifications)->whereNull('read_at')->count(),
                    'total' => $allNotifications->count(),
                    'today' => (clone $allNotifications)->whereDate('created_at', $today)->count(),
                    'week' => (clone $allNotifications)->whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                ];
            }
        );

        $this->unreadCount = $counts['unread'] ?? 0;
        $this->totalCount = $counts['total'] ?? 0;
        $this->todayCount = $counts['today'] ?? 0;
        $this->weekCount = $counts['week'] ?? 0;
    }

    public function loadNotifications($limitOnly = false)
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Build query
        $query = $user->notifications()->latest();

        // Apply search filter - search in JSON data fields
        if ($this->search) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                // Search in title, message, ticket_number from JSON data
                $q->whereRaw('JSON_EXTRACT(data, "$.title") LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('JSON_EXTRACT(data, "$.message") LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('JSON_EXTRACT(data, "$.ticket_number") LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        // Apply type filter - search in JSON type field
        // Notification types are like 'ticket_status_approved', 'ticket_status_rejected', etc.
        if ($this->typeFilter) {
            if ($this->typeFilter === 'ticket_status') {
                // Filter for all ticket status notifications
                $query->whereRaw('JSON_EXTRACT(data, "$.type") LIKE ?', ['ticket_status_%']);
            } else {
                // Exact match for other types
                $query->whereRaw('JSON_EXTRACT(data, "$.type") = ?', [$this->typeFilter]);
            }
        }

        // Apply status filter
        if ($this->statusFilter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->statusFilter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Limit results for faster initial load
        if ($limitOnly) {
            $query->limit(20);
        }

        // Get notifications
        $this->notifications = $query->get();

        // Reload counts if filters changed (but not on initial mount)
        if (!$limitOnly) {
            $this->loadCounts();
        }
    }

    public function markAsRead($notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            // Clear cache to refresh counts
            \Illuminate\Support\Facades\Cache::forget("osa_notifications_counts_{$user->user_id}");
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();
        // Clear cache to refresh counts
        \Illuminate\Support\Facades\Cache::forget("osa_notifications_counts_{$user->user_id}");
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
        $this->loadNotifications(false); // Load all when filtering
    }

    public function updatedTypeFilter()
    {
        $this->loadNotifications(false); // Load all when filtering
    }

    public function updatedStatusFilter()
    {
        $this->loadNotifications(false); // Load all when filtering
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
