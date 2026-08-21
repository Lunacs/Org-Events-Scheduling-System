<?php

namespace App\Livewire\Osa;

use App\Models\Student_Organization;
use App\Models\Ticket;
use App\Services\Cache\DashboardCacheService;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

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

    #[Computed]
    public function stats(): array
    {
        return DashboardCacheService::getDashboardWidget('osa', 'stats', function () {
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

    #[Computed]
    public function recentTickets(): array
    {
        return DashboardCacheService::getDashboardWidget('osa', 'recent_tickets', function () {
            return Ticket::select(['ticket_id', 'ticket_number', 'title', 'status', 'created_at', 'user_id', 'event_type_id'])
                ->with([
                    'eventType:event_type_id,type_name',
                    'user' => fn ($q) => $q->withTrashed()->select(['user_id', 'org_id'])
                        ->with('studentOrganization:org_id,org_name,logo'),
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

    #[Computed]
    public function pendingApprovals(): array
    {
        return DashboardCacheService::getDashboardWidget('osa', 'pending_approvals', function () {
            return Ticket::select(['ticket_id', 'ticket_number', 'title', 'status', 'created_at', 'user_id'])
                ->with([
                    'user' => fn ($q) => $q->withTrashed()->select(['user_id', 'org_id'])
                        ->with('studentOrganization:org_id,org_name,logo'),
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

    /**
     * Warm up cache with frequently accessed data to prevent N+1 queries
     */
    public function warmCache()
    {
        $this->stats;
        $this->recentTickets;
        $this->pendingApprovals;

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
}
