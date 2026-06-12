<?php

namespace App\Livewire\StudentOrg;

use App\Models\Event_Type;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

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

    private function getUserOrgId()
    {
        return auth()->user()->org_id;
    }

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
        $this->selectedTicket = Ticket::with([
            'user' => function ($query) {
                $query->withTrashed();
            },
            'user.studentOrganization.course',
            'user.position',
            'eventType',
            'fundSource',
            'attachments',
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
        $orgId = $this->getUserOrgId();

        return Cache::remember(
            "student_org_history_stats_{$orgId}",
            120, // 2 minutes
            function () use ($orgId) {
                // Single query with conditional aggregation instead of 4 separate queries
                $stats = DB::table('tickets')
                    ->leftJoin('users', 'tickets.user_id', '=', 'users.user_id')
                    ->where('users.org_id', $orgId)
                    ->whereIn('tickets.status', ['approved', 'for_revision'])
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN tickets.status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                        SUM(CASE WHEN tickets.status = 'for_revision' THEN 1 ELSE 0 END) as for_revision_count,
                        AVG(CASE WHEN tickets.status = 'approved' THEN DATEDIFF(tickets.updated_at, tickets.created_at) END) as avg_days
                    ")
                    ->first();

                $total = (int) $stats->total;
                $approvedCount = (int) $stats->approved_count;
                $forRevisionCount = (int) $stats->for_revision_count;

                return [
                    'total' => $total,
                    'approved' => [
                        'count' => $approvedCount,
                        'percentage' => $total > 0 ? round(($approvedCount / $total) * 100) : 0,
                    ],
                    'for_revision' => [
                        'count' => $forRevisionCount,
                        'percentage' => $total > 0 ? round(($forRevisionCount / $total) * 100) : 0,
                    ],
                    'avgProcessingDays' => $stats->avg_days ? round($stats->avg_days, 1) : 0,
                ];
            }
        );
    }

    public function getEventTypeDistributionProperty()
    {
        $orgId = $this->getUserOrgId();

        $eventTypes = DB::table('tickets')
            ->leftJoin('users', 'tickets.user_id', '=', 'users.user_id')
            ->join('event__types', 'tickets.event_type_id', '=', 'event__types.event_type_id')
            ->where('users.org_id', $orgId)
            ->where('tickets.status', 'approved')
            ->select('event__types.type_name', DB::raw('COUNT(*) as count'))
            ->groupBy('event__types.event_type_id', 'event__types.type_name')
            ->orderByDesc('count')
            ->get();

        $total = $eventTypes->sum('count');

        if ($total === 0) {
            return [];
        }

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
        $orgId = $this->getUserOrgId();

        $monthlyData = DB::table('tickets')
            ->leftJoin('users', 'tickets.user_id', '=', 'users.user_id')
            ->where('users.org_id', $orgId)
            ->where('tickets.created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw('DATE_FORMAT(tickets.created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get()
            ->keyBy('month');

        $months = collect();

        for ($i = 0; $i < 6; $i++) {
            $date = now()->startOfMonth()->subMonths($i);
            $monthKey = $date->format('Y-m');

            $count = $monthlyData->get($monthKey)->count ?? 0;
            $maxCount = $monthlyData->max('count') ?? 1;

            $months->push([
                'name' => $date->format('F Y'),
                'count' => $count,
                'percentage' => $maxCount > 0 ? round(($count / $maxCount) * 100) : 0,
            ]);
        }

        return $months;
    }

    public function getTicketsProperty()
    {
        $orgId = $this->getUserOrgId();

        $query = Ticket::query()
            ->with([
                'user' => function ($query) {
                    $query->withTrashed();
                },
                'user.studentOrganization',
                'events.eventType',
                'events' => function ($q) {
                    $q->with(['eventSchedules' => function ($sq) {
                        $sq->where('status', 'active')
                            ->select('event_id', 'venue', 'start_date', 'end_date', 'status');
                    }]);
                },
                'venue',
                'latestOsaApproval',
                'eventType',
            ])
            ->whereHas('user', function ($q) use ($orgId) {
                $q->withTrashed()->where('org_id', $orgId);
            })
            ->whereIn('status', ['approved', 'completed', 'for_revision', 'cancelled']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
                    ->orWhereHas('venue', function ($vq) {
                        $vq->where('venue_name', 'like', "%{$this->search}%");
                    })
                    ->orWhereHas('events.schedules', function ($sq) {
                        $sq->where('venue', 'like', "%{$this->search}%");
                    });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter) {
            $query->whereHas('eventType', function ($q) {
                $q->where('type_name', $this->typeFilter);
            });
        }

        if ($this->yearFilter) {
            $query->where(function ($q) {
                $q->whereYear('date_from', $this->yearFilter)
                    ->orWhereHas('events.schedules', function ($sq) {
                        $sq->whereYear('start_date', $this->yearFilter);
                    });
            });
        }

        return $query->latest('created_at')->paginate(10);
    }

    public function getEventTypesProperty()
    {
        return Event_Type::orderBy('type_name')
            ->get()
            ->map(fn ($type) => [
                'id' => $type->type_name,
                'name' => $type->type_name,
            ])
            ->prepend(['id' => '', 'name' => 'All Types']);
    }

    public function getYearsProperty()
    {
        $orgId = $this->getUserOrgId();

        $years = DB::table('tickets')
            ->leftJoin('users', 'tickets.user_id', '=', 'users.user_id')
            ->leftJoin('events', 'tickets.ticket_id', '=', 'events.ticket_id')
            ->leftJoin('event_schedules', 'events.event_id', '=', 'event_schedules.event_id')
            ->where('users.org_id', $orgId)
            ->whereIn('tickets.status', ['approved', 'for_revision', 'cancelled'])
            ->selectRaw('DISTINCT YEAR(COALESCE(event_schedules.start_date, tickets.date_from)) as year')
            ->whereNotNull(DB::raw('YEAR(COALESCE(event_schedules.start_date, tickets.date_from))'))
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => [
                'id' => (string) $year,
                'name' => (string) $year,
            ])
            ->prepend(['id' => '', 'name' => 'All Years']);

        return $years;
    }

    public function resubmitTicket($ticketId)
    {
        $ticket = Ticket::query()
            ->with(['eventType', 'fundSource', 'venue'])
            ->whereHas('user', function ($q) {
                $orgId = $this->getUserOrgId();
                $q->withTrashed()->where('org_id', $orgId);
            })
            ->findOrFail($ticketId);

        // Store ticket data in session for pre-filling the submit form
        session([
            'resubmit_ticket' => [
                'is_amended' => true,
                'original_ticket_id' => $ticket->ticket_id,
                'proponent_contact' => $ticket->proponent_contact,
                'adviser_contact' => $ticket->adviser_contact,
                'eventTitle' => $ticket->title,
                'eventDescription' => $ticket->description,
                'eventType' => $ticket->event_type_id,
                'expectedPLVParticipants' => $ticket->plv_participants,
                'expectedNonPLVParticipants' => $ticket->external_participants,
                'eventStartDate' => $ticket->date_from,
                'eventEndDate' => $ticket->date_to,
                'eventStartTime' => $ticket->time_from,
                'eventEndTime' => $ticket->time_to,
                'preferredVenue' => $ticket->venue_requested ?? 'other',
                'preferredVenueOther' => $ticket->venue_other,
                'alternativeVenue' => $ticket->alternate_venue ?? 'other',
                'alternativeVenueOther' => $ticket->alternate_venue_other,
                'specialRequirements' => $ticket->special_requirements,
                'ocAccommodation' => $ticket->oc_accommodation,
                'ocTsp' => $ticket->oc_tsp,
                'ocDriverName' => $ticket->oc_driver_name,
                'ocTransportationType' => $ticket->oc_transportation_type,
                'ocVehiclePlateNumber' => $ticket->oc_vehicle_plate_number,
                'ocDriverContactNumber' => $ticket->oc_driver_contact_number,
                'totalBudget' => $ticket->estimated_budget,
                'fundingSource' => $ticket->fund_source_id,
                'igp_requested' => $ticket->igp_requested ? 'true' : 'false',
                'igp_details' => $ticket->igp_details,
            ],
        ]);

        $this->js("localStorage.removeItem('ticket_draft_".auth()->id()."')");

        return redirect()->route('student-org.submit-ticket');
    }

    public function render()
    {
        return view('livewire.student-org.history');
    }
}
