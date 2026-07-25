<?php

namespace App\Livewire\Superadmin\Reports;

use App\Models\Event_Type;
use App\Models\Office;
use App\Models\Student_Organization;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Session;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Lazy]
class Index extends Component
{
    use Toast;

    #[Title('Reports & Analytics - SuperAdmin')]
    #[Layout('components.layouts.superadmin')]
    public function placeholder()
    {
        return view('livewire.superadmin.placeholders.reports');
    }

    // Filters with URL state
    #[Url(except: '')]
    public $dateFrom;

    #[Url(except: '')]
    public $dateTo;

    #[Session]
    public $selectedOffices = [];

    #[Session]
    public $selectedEventTypes = [];

    // Filter drawer visibility
    public bool $showFilterDrawer = false;

    #[Url(except: '')]
    public $searchTerm = '';

    #[Url(except: 'overview')]
    public $reportType = 'overview'; // overview, events, users, tickets

    // Data for charts
    public $chartData = [];

    public function mount()
    {
        // Set default dates (last 30 days)
        $this->dateTo = now()->format('Y-m-d');
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');

        $this->loadChartData();
    }

    public function clearFilters()
    {
        $this->reset(['selectedOffices', 'selectedEventTypes', 'searchTerm']);
        $this->loadChartData();
        $this->success('Filters cleared!', position: 'toast-top');
    }

    public function resetDateRange()
    {
        $this->dateTo = now()->format('Y-m-d');
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->loadChartData();
    }

    public function resetOffices()
    {
        $this->selectedOffices = [];
        $this->loadChartData();
    }

    public function resetEventTypes()
    {
        $this->selectedEventTypes = [];
        $this->loadChartData();
    }

    public function resetSearch()
    {
        $this->searchTerm = '';
        $this->loadChartData();
    }

    // MaryUI Chart configurations
    public array $eventsByMonthChart = [];

    public array $eventsByTypeChart = [];

    public array $eventsByOfficeChart = [];

    public array $ticketStatusChart = [];

    public array $usersByRoleChart = [];

    public function loadChartData()
    {
        $this->chartData = [
            'overview' => $this->getOverviewData(),
            'eventsByMonth' => $this->getEventsByMonth(),
            'eventsByType' => $this->getEventsByType(),
            'eventsByOffice' => $this->getEventsByOffice(),
            'ticketStatusDistribution' => $this->getTicketStatusDistribution(),
            'usersByRole' => $this->getUsersByRole(),
        ];

        // Build MaryUI chart configurations
        $this->buildChartConfigs();
    }

    protected function buildChartConfigs(): void
    {
        // Events by Month - Line Chart
        $eventsByMonth = $this->chartData['eventsByMonth'];
        $this->eventsByMonthChart = [
            'type' => 'line',
            'data' => [
                'labels' => $eventsByMonth['labels'] ?? [],
                'datasets' => [
                    [
                        'label' => 'Events',
                        'data' => $eventsByMonth['data'] ?? [],
                        'borderColor' => 'rgb(79, 209, 197)',
                        'backgroundColor' => 'rgba(79, 209, 197, 0.1)',
                        'tension' => 0.4,
                        'fill' => true,
                        'pointBackgroundColor' => 'rgb(79, 209, 197)',
                        'pointRadius' => 4,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['display' => false],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true, 'grid' => ['display' => false]],
                    'x' => ['grid' => ['display' => false]],
                ],
            ],
        ];

        // Events by Type - Doughnut Chart
        $eventsByType = $this->chartData['eventsByType'];
        $this->eventsByTypeChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $eventsByType['labels'] ?? [],
                'datasets' => [
                    [
                        'data' => $eventsByType['data'] ?? [],
                        'backgroundColor' => [
                            'rgba(79, 209, 197, 0.8)',
                            'rgba(99, 102, 241, 0.8)',
                            'rgba(251, 191, 36, 0.8)',
                            'rgba(248, 113, 113, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(14, 165, 233, 0.8)',
                        ],
                        'borderWidth' => 0,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                        'labels' => ['usePointStyle' => true, 'padding' => 20],
                    ],
                ],
                'cutout' => '70%',
            ],
        ];

        // Events by Office - Bar Chart
        $eventsByOffice = $this->chartData['eventsByOffice'];
        $this->eventsByOfficeChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $eventsByOffice['labels'] ?? [],
                'datasets' => [
                    [
                        'label' => 'Events',
                        'data' => $eventsByOffice['data'] ?? [],
                        'backgroundColor' => 'rgba(79, 209, 197, 0.8)',
                        'borderRadius' => 6,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['display' => false],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true, 'grid' => ['borderDash' => [2, 4]]],
                    'x' => ['grid' => ['display' => false]],
                ],
            ],
        ];

        // Ticket Status Distribution - Pie Chart
        $ticketStatus = $this->chartData['ticketStatusDistribution'];
        $this->ticketStatusChart = [
            'type' => 'pie',
            'data' => [
                'labels' => $ticketStatus['labels'] ?? [],
                'datasets' => [
                    [
                        'data' => $ticketStatus['data'] ?? [],
                        'backgroundColor' => [
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(251, 191, 36, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                        ],
                        'borderWidth' => 0,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                        'labels' => ['usePointStyle' => true, 'padding' => 20],
                    ],
                ],
            ],
        ];

        // Users by Role - Horizontal Bar Chart
        $usersByRole = $this->chartData['usersByRole'];
        $this->usersByRoleChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $usersByRole['labels'] ?? [],
                'datasets' => [
                    [
                        'label' => 'Users',
                        'data' => $usersByRole['data'] ?? [],
                        'backgroundColor' => 'rgba(99, 102, 241, 0.8)',
                        'borderRadius' => 6,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'indexAxis' => 'y',
                'plugins' => [
                    'legend' => ['display' => false],
                ],
                'scales' => [
                    'x' => ['beginAtZero' => true, 'grid' => ['borderDash' => [2, 4]]],
                    'y' => ['grid' => ['display' => false]],
                ],
            ],
        ];
    }

    protected function getOverviewData(): array
    {
        $ticketQuery = $this->getBaseTicketQuery();

        return [
            'total_events' => (clone $ticketQuery)->whereHas('events')->count(),
            'approved_events' => (clone $ticketQuery)->where('status', 'approved')->count(),
            'pending_events' => (clone $ticketQuery)->where('status', 'pending')->count(),
            'cancelled_events' => (clone $ticketQuery)->where('status', 'cancelled')->count(),
            'total_tickets' => (clone $ticketQuery)->count(),
            'total_users' => User::count(),
            'active_orgs' => Student_Organization::whereHas('tickets', function ($q) {
                $q->whereBetween('date_from', [$this->dateFrom, $this->dateTo]);
            })->count(),
        ];
    }

    protected function getEventsByMonth(): array
    {
        $tickets = $this->getBaseTicketQuery()
            ->select(
                DB::raw('DATE_FORMAT(date_from, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'labels' => $tickets->pluck('month')->map(fn ($m) => Carbon::parse($m)->format('M Y'))->toArray(),
            'data' => $tickets->pluck('count')->toArray(),
        ];
    }

    protected function getEventsByType(): array
    {
        $tickets = $this->getBaseTicketQuery()
            ->join('event__types', 'tickets.event_type_id', '=', 'event__types.event_type_id')
            ->select('event__types.type_name', DB::raw('COUNT(*) as count'))
            ->groupBy('event__types.event_type_id', 'event__types.type_name')
            ->get();

        return [
            'labels' => $tickets->pluck('type_name')->toArray(),
            'data' => $tickets->pluck('count')->toArray(),
        ];
    }

    protected function getEventsByOffice(): array
    {
        // Get tickets grouped by the user's student organization
        $tickets = $this->getBaseTicketQuery()
            ->join('users', 'tickets.user_id', '=', 'users.user_id')
            ->join('student__organizations', 'users.org_id', '=', 'student__organizations.org_id')
            ->select('student__organizations.org_name', DB::raw('COUNT(*) as count'))
            ->groupBy('student__organizations.org_id', 'student__organizations.org_name')
            ->get();

        return [
            'labels' => $tickets->pluck('org_name')->toArray(),
            'data' => $tickets->pluck('count')->toArray(),
        ];
    }

    protected function getTicketStatusDistribution(): array
    {
        $tickets = $this->getBaseTicketQuery()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return [
            'labels' => $tickets->pluck('status')->map(fn ($s) => ucfirst(str_replace('_', ' ', $s)))->toArray(),
            'data' => $tickets->pluck('count')->toArray(),
        ];
    }

    protected function getUsersByRole(): array
    {
        $users = User::join('roles', 'users.role_id', '=', 'roles.role_id')
            ->select('roles.role_name', DB::raw('COUNT(*) as count'))
            ->groupBy('roles.role_id', 'roles.role_name')
            ->get();

        return [
            'labels' => $users->pluck('role_name')->map(fn ($r) => ucfirst(str_replace('_', ' ', $r)))->toArray(),
            'data' => $users->pluck('count')->toArray(),
        ];
    }

    protected function getBaseTicketQuery()
    {
        $query = Ticket::query()
            ->where('date_from', '<=', $this->dateTo)
            ->where('date_to', '>=', $this->dateFrom);

        if (! empty($this->selectedEventTypes)) {
            $query->whereIn('tickets.event_type_id', $this->selectedEventTypes);
        }

        // Filter by organization if offices are selected (treating them as orgs)
        if (! empty($this->selectedOffices)) {
            $query->whereHas('user', function ($q) {
                $q->whereIn('org_id', $this->selectedOffices);
            });
        }

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('tickets.ticket_number', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('tickets.title', 'like', '%'.$this->searchTerm.'%');
            });
        }

        return $query;
    }

    public function updatedDateFrom()
    {
        $this->loadChartData();
    }

    public function updatedDateTo()
    {
        $this->loadChartData();
    }

    public function updatedSelectedOffices()
    {
        $this->loadChartData();
    }

    public function updatedSelectedEventTypes()
    {
        $this->loadChartData();
    }

    public function updatedSearchTerm()
    {
        $this->loadChartData();
    }

    public function exportReport($format = 'csv')
    {
        try {
            $data = $this->getDetailedReportData();

            $filename = "report_{$this->reportType}_".now()->format('Y-m-d_His').".{$format}";

            if ($format === 'csv') {
                return $this->exportToCsv($data, $filename);
            } elseif ($format === 'pdf') {
                return $this->exportToPdf($data, $filename);
            }

            $this->success('Report exported successfully!', position: 'toast-top');
        } catch (\Exception $e) {
            $this->error('Failed to export report: '.$e->getMessage(), position: 'toast-top');
        }
    }

    protected function getDetailedReportData(): array
    {
        $query = $this->getBaseTicketQuery();

        return $query->with(['eventType', 'user.studentOrganization'])
            ->get()
            ->map(function ($ticket) {
                $orgDeleted = $ticket->user?->studentOrganization?->trashed();

                return [
                    'Ticket Number' => $ticket->ticket_number,
                    'Event Title' => $ticket->title,
                    'Date From' => Carbon::parse($ticket->date_from)->format('M d, Y'),
                    'Date To' => Carbon::parse($ticket->date_to)->format('M d, Y'),
                    'Event Type' => $ticket->eventType->type_name ?? 'N/A',
                    'Organization' => $orgDeleted ? 'Deleted Organization' : ($ticket->user?->studentOrganization?->org_name ?? 'N/A'),
                    'Status' => ucfirst(str_replace('_', ' ', $ticket->status)),
                    'Venue' => $ticket->venue_requested ?? 'N/A',
                    'Created At' => $ticket->created_at->format('M d, Y H:i:s'),
                ];
            })
            ->toArray();
    }

    protected function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            if (! empty($data)) {
                fputcsv($file, array_keys($data[0]));

                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportToPdf($data, $filename)
    {
        try {
            // Prepare data for PDF
            $reportData = [
                'title' => 'Events Report',
                'date_from' => Carbon::parse($this->dateFrom)->format('M d, Y'),
                'date_to' => Carbon::parse($this->dateTo)->format('M d, Y'),
                'generated_at' => now()->format('M d, Y g:i A'),
                'events' => $data,
                'overview' => $this->chartData['overview'] ?? [],
            ];

            // Generate PDF
            $pdf = Pdf::loadView('reports.events-pdf', $reportData);

            // Set paper size and orientation
            $pdf->setPaper('a4', 'landscape');

            // Return download response
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to generate PDF: '.$e->getMessage(), position: 'toast-top');

            return null;
        }
    }

    public function render()
    {
        return view('livewire.superadmin.reports.index')->with([
            'offices' => $this->offices,
            'eventTypes' => $this->eventTypes,
        ]);
    }

    #[Computed(persist: true, seconds: 1800)] // Cache for 30 minutes
    public function offices()
    {
        return Student_Organization::select(['org_id', 'org_name', 'org_code'])
            ->orderBy('org_name')
            ->get();
    }

    #[Computed(persist: true, seconds: 1800)] // Cache for 30 minutes
    public function eventTypes()
    {
        return Event_Type::select(['event_type_id', 'type_name'])
            ->orderBy('type_name')
            ->get();
    }
}
