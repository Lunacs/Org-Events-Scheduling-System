<?php

namespace App\Livewire\Osa;

use App\Models\Student_Organization;
use App\Models\Ticket;
use App\Models\Transaction_Logs;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Lazy]
class Dashboard extends Component
{
    use Toast;

    #[Title('Dashboard - OSA Admin')]
    #[Layout('components.layouts.app')]

    // Cache duration in seconds - optimized for better performance
    protected $cacheDuration = 300; // 5 minutes cache for better performance

    // Statuses that require OSA action
    protected array $osaActionStatuses = ['received', 'amended', 'pending_osa_approval'];

    public function placeholder()
    {
        return view('livewire.osa.placeholders.dashboard');
    }

    public function render()
    {
        return view('livewire.osa.dashboard');
    }

    #[Computed(persist: true, seconds: 600)]
    public function stats(): array
    {
        return Cache::remember('osa_dashboard_stats', $this->cacheDuration, function () {
            $now = now();
            $currentMonth = $now->month;
            $currentYear = $now->year;

            // Count tickets requiring OSA action
            return [
                'pending' => Ticket::whereIn('status', $this->osaActionStatuses)->count(),
                'forwarded' => Ticket::whereHas('officeApprovals', function ($query) {
                    $query->where('decision', 'pending');
                })->count(),
                'approved' => Ticket::where('status', 'approved')->count(),
                'for_revision' => Ticket::where('status', 'for_revision')->count(),
                'totalOrganizations' => Student_Organization::count(),
                'thisMonthTickets' => Ticket::whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear)
                    ->count(),
            ];
        });
    }

    #[Computed(persist: true, seconds: 600)]
    public function recentTickets(): array
    {
        return Cache::remember('osa_dashboard_recent_tickets', $this->cacheDuration, function () {
            return Ticket::select(['ticket_id', 'ticket_number', 'title', 'status', 'created_at', 'user_id', 'event_type_id'])
                ->with([
                    'eventType:event_type_id,type_name',
                    'user' => fn($q) => $q->withTrashed()->select(['user_id', 'org_id'])
                        ->with('studentOrganization:org_id,org_name,logo')
                ])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->ticket_id,
                        'ticket_number' => $ticket->ticket_number,
                        'title' => $ticket->title,
                        'organization' => $ticket->user?->studentOrganization?->org_name ?? 'N/A',
                        'type' => $ticket->eventType?->type_name ?? 'N/A',
                        'submitted' => $ticket->created_at->setTimezone('Asia/Manila')->format('M d, Y'),
                        'status' => ucfirst($ticket->status),
                    ];
                })
                ->toArray();
        });
    }

    #[Computed(persist: true, seconds: 600)]
    public function pendingApprovals(): array
    {
        return Cache::remember('osa_dashboard_pending_approvals', $this->cacheDuration, function () {
            return Ticket::select(['ticket_id', 'ticket_number', 'title', 'status', 'created_at', 'user_id'])
                ->with([
                    'user' => fn($q) => $q->withTrashed()->select(['user_id', 'org_id'])
                        ->with('studentOrganization:org_id,org_name,logo')
                ])
                ->whereIn('status', $this->osaActionStatuses)
                ->orderBy('created_at', 'asc')
                ->limit(5)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->ticket_id,
                        'ticket_number' => $ticket->ticket_number,
                        'title' => $ticket->title,
                        'status' => $ticket->status,
                        'status_label' => $this->getStatusLabel($ticket->status),
                        'status_class' => $this->getStatusClass($ticket->status),
                        'organization' => $ticket->user?->studentOrganization?->org_name ?? 'N/A',
                        'submitted' => $ticket->created_at->setTimezone('Asia/Manila')->diffForHumans(),
                    ];
                })
                ->toArray();
        });
    }

    #[Computed(persist: true, seconds: 600)]
    public function upcomingEvents(): array
    {
        return Cache::remember('osa_dashboard_upcoming_events', $this->cacheDuration, function () {
            return Ticket::select(['ticket_id', 'title', 'date_from', 'venue_requested', 'venue_other', 'user_id'])
                ->with([
                    'user' => fn($q) => $q->withTrashed()->select(['user_id', 'org_id'])
                        ->with('studentOrganization:org_id,org_name,logo')
                ])
                ->where('status', 'approved')
                ->where('date_from', '>=', now())
                ->orderBy('date_from', 'asc')
                ->limit(5)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'title' => $ticket->title,
                        'organization' => $ticket->user?->studentOrganization?->org_name ?? 'N/A',
                        'date' => $ticket->date_from
                            ? \Carbon\Carbon::parse($ticket->date_from)->format('M d, Y')
                            : 'TBD',
                        'venue' => $ticket->venue_display_name ?? 'TBD',
                    ];
                })
                ->toArray();
        });
    }

    #[Computed(persist: true, seconds: 600)]
    public function recentActivity(): array
    {
        return Cache::remember('osa_dashboard_recent_activity', $this->cacheDuration, function () {
            return Transaction_Logs::with('user')
                ->whereIn('action', [
                    'Ticket Approved',
                    'Ticket Rejected',
                    'Ticket Forwarded',
                    'Ticket For Revision',
                    'Ticket Status Updated',
                    'New Ticket Submitted',
                ])
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->log_id,
                        'action' => $log->action,
                        'details' => $log->details,
                        'time_ago' => $log->created_at?->diffForHumans() ?? 'Just now',
                        'icon' => $this->getActivityIcon($log->action),
                        'icon_class' => $this->getActivityIconClass($log->action),
                    ];
                })
                ->toArray();
        });
    }

    #[Computed(persist: true, seconds: 600)]
    public function todaysSummary(): array
    {
        return Cache::remember('osa_dashboard_todays_summary', $this->cacheDuration, function () {
            $today = now()->startOfDay();

            return [
                'newRequests' => Ticket::whereDate('created_at', $today)->count(),
                'processed' => Ticket::whereIn('status', ['approved', 'for_revision', 'gso_review'])
                    ->whereDate('updated_at', $today)
                    ->count(),
                'pending' => Ticket::whereIn('status', $this->osaActionStatuses)->count(),
            ];
        });
    }



    /**
     * Warm up cache with frequently accessed data to prevent N+1 queries
     */
    public function warmCache()
    {
        // Pre-warm all dashboard data
        $this->stats;
        $this->recentTickets;
        $this->pendingApprovals;
        $this->upcomingEvents;
        $this->recentActivity;
        $this->todaysSummary;

        $this->success('Cache warmed successfully!', position: 'toast-top');
    }

    #[Computed]
    public function headers(): array
    {
        return [
            ['key' => 'ticket_number', 'label' => 'Ticket #'],
            ['key' => 'title', 'label' => 'Event Title'],
            ['key' => 'organization', 'label' => 'Organization'],
            ['key' => 'type', 'label' => 'Type'],
            ['key' => 'submitted', 'label' => 'Submitted'],
            ['key' => 'status', 'label' => 'Status'],
        ];
    }

    /**
     * Get status label for display
     */
    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'received' => 'Received',
            'amended' => 'Amended',
            'pending_osa_approval' => 'Final Approval',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /**
     * Get status badge class
     */
    protected function getStatusClass(string $status): string
    {
        return match ($status) {
            'received' => 'badge-info',
            'amended' => 'badge-warning',
            'pending_osa_approval' => 'badge-primary',
            default => 'badge-ghost',
        };
    }

    /**
     * Get activity icon based on action
     */
    protected function getActivityIcon(string $action): string
    {
        return match (true) {
            str_contains($action, 'Approved') => 'o-check-circle',
            str_contains($action, 'Rejected'), str_contains($action, 'Revision') => 'o-x-circle',
            str_contains($action, 'Forwarded') => 'o-paper-airplane',
            str_contains($action, 'Submitted') => 'o-document-plus',
            default => 'o-information-circle',
        };
    }

    /**
     * Get activity icon color class
     */
    protected function getActivityIconClass(string $action): string
    {
        return match (true) {
            str_contains($action, 'Approved') => 'text-success',
            str_contains($action, 'Rejected'), str_contains($action, 'Revision') => 'text-error',
            str_contains($action, 'Forwarded') => 'text-info',
            str_contains($action, 'Submitted') => 'text-primary',
            default => 'text-gray-500',
        };
    }
}
