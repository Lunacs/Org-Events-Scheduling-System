<?php

namespace App\Livewire\StudentOrg;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

class History extends Component
{
    #[Title('Event History - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]

    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $yearFilter = '';
    public $showDetailsModal = false;
    public $loadingDetails = false;
    public $selectedTicket = null;

    public function openDetailsModal($ticketId)
    {
        $this->showDetailsModal = true;
        $this->loadingDetails = true;
        $this->selectedTicket = null;

        // Dispatch browser event to load ticket details after modal renders
        $this->dispatch('modal-opened', ticketId: $ticketId);
    }

    #[On('modal-opened')]
    public function loadTicketDetails($ticketId)
    {
        $this->selectedTicket = \App\Models\Ticket::with([
            'user.studentOrganization.course',
            'user.position',
            'eventType',
            'fundSource',
            'attachments'
        ])->findOrFail($ticketId);

        $this->loadingDetails = false;
    }

    public function exportReport()
    {
        // Implement export logic
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->typeFilter = '';
        $this->yearFilter = '';
    }

    public function getStatsProperty()
    {
        $userId = auth()->id();

        // Total events (approved + for_revision)
        $totalEvents = DB::table('tickets')
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'for_revision'])
            ->count();

        // Approved count
        $approvedCount = DB::table('tickets')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->count();

        // For Revision count
        $for_revisionCount = DB::table('tickets')
            ->where('user_id', $userId)
            ->where('status', 'for_revision')
            ->count();

        // Calculate percentages
        $approvedPercentage = $totalEvents > 0 ? round(($approvedCount / $totalEvents) * 100) : 0;
        $for_revisionPercentage = $totalEvents > 0 ? round(($for_revisionCount / $totalEvents) * 100) : 0;

        // Average processing days (from created_at to when status changed to approved)
        $avgProcessingDays = DB::table('tickets')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
            ->value('avg_days');

        $avgProcessingDays = $avgProcessingDays ? round($avgProcessingDays, 1) : 0;

        return [
            'total' => $totalEvents,
            'approved' => [
                'count' => $approvedCount,
                'percentage' => $approvedPercentage,
            ],
            'for_revision' => [
                'count' => $for_revisionCount,
                'percentage' => $for_revisionPercentage,
            ],
            'avgProcessingDays' => $avgProcessingDays,
        ];
    }

    public function getEventTypeDistributionProperty()
    {
        $userId = auth()->id();

        // Get count of approved tickets by event type
        $eventTypes = DB::table('tickets')
            ->join('event__types', 'tickets.event_type_id', '=', 'event__types.event_type_id')
            ->where('tickets.user_id', $userId)
            ->where('tickets.status', 'approved')
            ->select('event__types.type_name', DB::raw('COUNT(*) as count'))
            ->groupBy('event__types.event_type_id', 'event__types.type_name')
            ->orderByDesc('count')
            ->get();

        $total = $eventTypes->sum('count');

        if ($total === 0) {
            return [];
        }

        // Define colors for each event type
        $colors = [
            'bg-blue-500',
            'bg-green-500',
            'bg-yellow-500',
            'bg-purple-500',
            'bg-pink-500',
            'bg-indigo-500',
            'bg-red-500',
            'bg-orange-500',
        ];

        return $eventTypes->map(function ($item, $index) use ($total, $colors) {
            $percentage = round(($item->count / $total) * 100);
            return [
                'name' => $item->type_name,
                'count' => $item->count,
                'percentage' => $percentage,
                'color' => $colors[$index % count($colors)],
            ];
        });
    }

    public function getMonthlyActivityProperty()
    {
        $userId = auth()->id();

        // Get ticket counts for last 6 months
        $monthlyData = DB::table('tickets')
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get()
            ->keyBy('month');

        // Generate last 6 months
        $months = collect();
        for ($i = 0; $i < 6; $i++) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $count = $monthlyData->get($monthKey)?->count ?? 0;

            $months->push([
                'name' => $date->format('F Y'),
                'count' => $count,
            ]);
        }

        // Calculate percentages based on max count
        $maxCount = $months->max('count') ?: 1;

        return $months->map(function ($item) use ($maxCount) {
            $item['percentage'] = round(($item['count'] / $maxCount) * 100);
            return $item;
        });
    }

    public function getTicketsProperty()
    {
        $query = \App\Models\Ticket::query()
            ->with([
                'events.eventType',
                'events' => function($q) {
                    $q->with(['eventSchedules' => function($sq) {
                        $sq->where('status', 'active')
                            ->select('event_id', 'venue', 'start_date', 'end_date', 'status');
                    }]);
                },
                'venue',
                'latestOsaApproval',
                'eventType'
            ])
            ->where('user_id', auth()->id())
            ->whereIn('status', ['approved', 'for_revision', 'cancelled']);

        // Apply filters
        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
                    ->orWhereHas('venue', function($vq) {
                        $vq->where('venue_name', 'like', "%{$this->search}%");
                    })
                    ->orWhereHas('events.schedules', function($sq) {
                        $sq->where('venue', 'like', "%{$this->search}%");
                    });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter) {
            $query->whereHas('eventType', function($q) {
                $q->where('type_name', $this->typeFilter);
            });
        }

        if ($this->yearFilter) {
            $query->where(function($q) {
                $q->whereYear('date_from', $this->yearFilter)
                    ->orWhereHas('events.schedules', function($sq) {
                        $sq->whereYear('start_date', $this->yearFilter);
                    });
            });
        }

        return $query->latest('created_at')->paginate(10);
    }

    public function getEventTypesProperty()
    {
        return \App\Models\Event_Type::orderBy('type_name')
            ->get()
            ->map(fn($type) => [
                'id' => $type->type_name,
                'name' => $type->type_name
            ])
            ->prepend(['id' => '', 'name' => 'All Types']);
    }

    public function getYearsProperty()
    {
        $userId = auth()->id();

        // Get distinct years from both ticket dates and event schedule dates
        $years = DB::table('tickets')
            ->leftJoin('events', 'tickets.ticket_id', '=', 'events.ticket_id')
            ->leftJoin('event_schedules', 'events.event_id', '=', 'event_schedules.event_id')
            ->where('tickets.user_id', $userId)
            ->whereIn('tickets.status', ['approved', 'for_revision', 'cancelled'])
            ->selectRaw('DISTINCT YEAR(COALESCE(event_schedules.start_date, tickets.date_from)) as year')
            ->whereNotNull(DB::raw('YEAR(COALESCE(event_schedules.start_date, tickets.date_from))'))
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn($year) => [
                'id' => (string) $year,
                'name' => (string) $year
            ])
            ->prepend(['id' => '', 'name' => 'All Years']);

        return $years;
    }

    public function render()
    {
        return view('livewire.student-org.history');
    }
}
