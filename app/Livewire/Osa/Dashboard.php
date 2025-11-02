<?php

namespace App\Livewire\Osa;

use App\Models\Student_Organization;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class Dashboard extends Component
{
    use Toast;

    #[Title('Dashboard - OSA Admin')]
    #[Layout('components.layouts.app')]

    // Cache duration in minutes
    protected $cacheDuration = 10;

    public function render()
    {
        return view('livewire.osa.dashboard');
    }

    #[Computed(persist: true, seconds: 600)]
    public function stats(): array
    {
        return Cache::remember('osa_dashboard_stats', $this->cacheDuration, function () {
            return [
                'pending' => Ticket::where('status', 'pending')->count(),
                'forwarded' => Ticket::whereHas('officeApprovals', function ($query) {
                    $query->where('decision', 'pending');
                })->count(),
                'approved' => Ticket::where('status', 'approved')->count(),
                'rejected' => Ticket::where('status', 'rejected')->count(),
                'totalOrganizations' => Student_Organization::count(),
                'thisMonthTickets' => Ticket::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
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
                    'user' => fn($q) => $q->select(['user_id', 'org_id'])
                        ->with('studentOrganization:org_id,org_name')
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
            return Ticket::select(['ticket_id', 'ticket_number', 'title', 'created_at', 'user_id'])
                ->with([
                    'user' => fn($q) => $q->select(['user_id', 'org_id'])
                        ->with('studentOrganization:org_id,org_name')
                ])
                ->where('status', 'pending')
                ->orderBy('created_at', 'asc')
                ->limit(5)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->ticket_id,
                        'ticket_number' => $ticket->ticket_number,
                        'title' => $ticket->title,
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
            return Ticket::select(['ticket_id', 'title', 'date_from', 'venue_requested', 'user_id'])
                ->with([
                    'user' => fn($q) => $q->select(['user_id', 'org_id'])
                        ->with('studentOrganization:org_id,org_name')
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
                        'venue' => $ticket->venue_requested ?? 'TBD',
                    ];
                })
                ->toArray();
        });
    }

    public function refreshData()
    {
        // Clear cache to force refresh
        Cache::forget('osa_dashboard_stats');
        Cache::forget('osa_dashboard_recent_tickets');
        Cache::forget('osa_dashboard_pending_approvals');
        Cache::forget('osa_dashboard_upcoming_events');

        // Unset computed properties to force re-render
        unset($this->stats, $this->recentTickets, $this->pendingApprovals, $this->upcomingEvents);

        $this->success('Dashboard data refreshed!', position: 'toast-top');
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
}
