<?php

namespace App\Livewire\Superadmin;

use Illuminate\Support\Facades\Cache;
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

    #[Title('Notifications - SuperAdmin')]
    #[Layout('components.layouts.superadmin')]

    #[Url(except: '')]
    public $search = '';

    #[Url(except: '')]
    public $typeFilter = '';

    #[Url(except: '')]
    public $statusFilter = '';

    public $unreadCount = 0;

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
            "superadmin_notifications_counts_{$user->user_id}",
            60, // 1 minute cache
            function () use ($user) {
                $today = today();
                $weekStart = now()->startOfWeek();
                $weekEnd = now()->endOfWeek();

                // Only count system-level notifications (not ticket-related)
                $allNotifications = $user->notifications()
                    ->whereRaw('JSON_EXTRACT(data, "$.type") NOT LIKE ?', ['ticket_%']);

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

    public function getNotificationsProperty()
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        // Build query - ONLY system-level notifications
        $query = $user->notifications()
            ->whereRaw('JSON_EXTRACT(data, "$.type") NOT LIKE ?', ['ticket_%'])
            ->latest();

        // Apply search filter - search in JSON data fields
        if ($this->search) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                // Search in title, message, and other relevant fields
                $q->whereRaw('JSON_EXTRACT(data, "$.title") LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('JSON_EXTRACT(data, "$.message") LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('JSON_EXTRACT(data, "$.user_name") LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('JSON_EXTRACT(data, "$.org_name") LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('JSON_EXTRACT(data, "$.setting_name") LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        // Apply type filter
        if ($this->typeFilter) {
            $query->whereRaw('JSON_EXTRACT(data, "$.type") = ?', [$this->typeFilter]);
        }

        // Apply status filter
        if ($this->statusFilter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->statusFilter === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query->paginate(15);
    }

    public function markAsRead($notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            // Clear cache to refresh counts
            Cache::forget("superadmin_notifications_counts_{$user->user_id}");
            $this->loadCounts();
        }
    }

    public function markAllAsRead()
    {
        $user = auth()->user();

        // Only mark system-level notifications as read
        $user->notifications()
            ->whereRaw('JSON_EXTRACT(data, "$.type") NOT LIKE ?', ['ticket_%'])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Clear cache to refresh counts
        Cache::forget("superadmin_notifications_counts_{$user->user_id}");
        $this->loadCounts();

        $this->success('All notifications marked as read.', position: 'toast-top');
    }

    public function deleteNotification($notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->delete();

            // Clear cache to refresh counts
            Cache::forget("superadmin_notifications_counts_{$user->user_id}");
            $this->loadCounts();
            $this->resetPage();

            $this->success('Notification deleted.', position: 'toast-top');
        }
    }

    public function clearAllRead()
    {
        $user = auth()->user();

        // Only delete read system-level notifications
        $deletedCount = $user->notifications()
            ->whereRaw('JSON_EXTRACT(data, "$.type") NOT LIKE ?', ['ticket_%'])
            ->whereNotNull('read_at')
            ->delete();

        // Clear cache to refresh counts
        Cache::forget("superadmin_notifications_counts_{$user->user_id}");
        $this->loadCounts();
        $this->resetPage();

        if ($deletedCount > 0) {
            $this->success("{$deletedCount} read notification(s) cleared.", position: 'toast-top');
        } else {
            $this->info('No read notifications to clear.', position: 'toast-top');
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
            Cache::forget("superadmin_notifications_counts_{$user->user_id}");
        }
        $this->loadCounts();
    }

    public function render()
    {
        return view('livewire.superadmin.notifications', [
            'notifications' => $this->notifications,
        ]);
    }
}

