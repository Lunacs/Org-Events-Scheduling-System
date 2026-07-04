<?php

namespace App\Livewire\Gso;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Async;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Notifications extends Component
{
    use Toast, WithPagination;

    #[Title('Notifications - GSO Admin')]
    #[Layout('components.layouts.gso-layout')]

    #[Url(except: '')]
    public $search = '';

    #[Url(except: '')]
    public $typeFilter = '';

    #[Url(except: '')]
    public $statusFilter = '';

    public $unreadCount = 0;

    public $readCount = 0;

    public $totalCount = 0;

    public $todayCount = 0;

    public $weekCount = 0;

    public function mount()
    {
        $this->loadCounts();
    }

    public function loadCounts()
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Optimize: Load all counts in a single query with aggregations
        $counts = Cache::remember(
            "gso_notifications_counts_{$user->user_id}",
            60, // 1 minute cache
            function () use ($user) {
                $today = today();
                $weekStart = now()->startOfWeek();
                $weekEnd = now()->endOfWeek();

                $allNotifications = $user->notifications();

                return [
                    'unread' => (clone $allNotifications)->whereNull('read_at')->count(),
                    'read' => (clone $allNotifications)->whereNotNull('read_at')->count(),
                    'total' => $allNotifications->count(),
                    'today' => (clone $allNotifications)->whereDate('created_at', $today)->count(),
                    'week' => (clone $allNotifications)->whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                ];
            }
        );

        $this->unreadCount = $counts['unread'] ?? 0;
        $this->readCount = $counts['read'] ?? 0;
        $this->totalCount = $counts['total'] ?? 0;
        $this->todayCount = $counts['today'] ?? 0;
        $this->weekCount = $counts['week'] ?? 0;
    }

    public function getNotificationsProperty()
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
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
        // Notification types are like 'ticket_status_approved', 'ticket_status_', etc.
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

        return $query->paginate(15);
    }

    #[Async]
    public function markAsRead($notificationId)
    {
        try {
            $user = auth()->user();
            $notification = $user->notifications()->find($notificationId);

            if ($notification) {
                $notification->markAsRead();
                // Clear cache to refresh counts
                Cache::forget("gso_notifications_counts_{$user->user_id}");
                $this->loadCounts();

                // Sync with top bar dropdown
                $this->dispatch('refresh-notifications');
            }
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read: '.$e->getMessage());
            $this->error('Failed to mark notification as read.', position: 'toast-top');
        }
    }

    public function markAllAsRead()
    {
        try {
            $user = auth()->user();
            $user->unreadNotifications->markAsRead();
            // Clear cache to refresh counts
            Cache::forget("gso_notifications_counts_{$user->user_id}");
            $this->loadCounts();

            // Sync with top bar dropdown
            $this->dispatch('refresh-notifications');

            $this->success('All notifications marked as read.', position: 'toast-top');
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read: '.$e->getMessage());
            $this->error('Failed to mark all notifications as read.', position: 'toast-top');
        }
    }

    public function deleteNotification($notificationId)
    {
        try {
            $user = auth()->user();
            $notification = $user->notifications()->find($notificationId);

            if ($notification) {
                $notification->delete();

                // Clear cache to refresh counts
                Cache::forget("gso_notifications_counts_{$user->user_id}");
                $this->loadCounts();
                $this->resetPage();

                // Sync with top bar dropdown
                $this->dispatch('refresh-notifications');

                $this->success('Notification deleted.', position: 'toast-top');
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete notification: '.$e->getMessage());
            $this->error('Failed to delete notification.', position: 'toast-top');
        }
    }

    public function clearAllRead()
    {
        try {
            $user = auth()->user();

            // Only delete notifications that have been read
            $deletedCount = $user->notifications()
                ->whereNotNull('read_at')
                ->delete();

            // Clear cache to refresh counts
            Cache::forget("gso_notifications_counts_{$user->user_id}");
            $this->loadCounts();
            $this->resetPage();

            if ($deletedCount > 0) {
                $this->success("{$deletedCount} read notification(s) cleared.", position: 'toast-top');
            } else {
                $this->info('No read notifications to clear.', position: 'toast-top');
            }

            // Sync with top bar dropdown
            $this->dispatch('refresh-notifications');
        } catch (\Exception $e) {
            Log::error('Failed to clear read notifications: '.$e->getMessage());
            $this->error('Failed to clear read notifications.', position: 'toast-top');
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'typeFilter', 'statusFilter']);
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    #[On('notifications-updated')]
    public function refreshNotifications()
    {
        $user = auth()->user();
        if ($user) {
            Cache::forget("gso_notifications_counts_{$user->user_id}");
        }
        $this->loadCounts();
    }

    public function render()
    {
        return view('livewire.gso.notifications', [
            'notifications' => $this->notifications,
        ]);
    }
}
