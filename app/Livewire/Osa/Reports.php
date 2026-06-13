<?php

namespace App\Livewire\Osa;

use App\Models\Student_Organization;
use App\Models\Ticket;
use App\Models\Event_Type;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Lazy]
class Reports extends Component
{
    #[Title('Reports - OSA Admin')]
    #[Layout('components.layouts.app')]

    #[Url(except: 'approved_events')]
    public $reportType = 'approved_events';

    #[Url(except: '')]
    public $dateFrom = '';

    #[Url(except: '')]
    public $dateTo = '';

    #[Url(except: '')]
    public $organizationFilter = '';

    public $exportFormat = 'pdf';

    // Chart data for analytics
    public $chartData = [];

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->loadChartData();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="p-6">
            <div class="animate-pulse space-y-6">
                <div class="h-8 bg-base-200 rounded w-1/3"></div>
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    <div class="space-y-4">
                        <div class="h-10 bg-base-200 rounded"></div>
                        <div class="h-10 bg-base-200 rounded"></div>
                        <div class="h-96 bg-base-200 rounded"></div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function generateReport()
    {
        $this->validate([
            'reportType' => 'required|in:approved_events,for_revision_events,org_participation,monthly_summary',
            'dateFrom' => 'required|date',
            'dateTo' => 'required|date|after_or_equal:dateFrom',
            'exportFormat' => 'required|in:pdf,excel,csv',
        ]);

        return $this->export();
    }

    public function clearFilters()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->organizationFilter = '';
    }

    public function render()
    {
        return view('livewire.osa.reports', [
            'organizations' => $this->organizations,
            'reportData' => $this->reportData,
        ]);
    }

    #[Computed]
    public function organizations()
    {
        return Student_Organization::select(['org_id', 'org_name', 'org_code'])
            ->orderBy('org_name')
            ->get();
    }

    #[Computed(persist: true, seconds: 300)]
    public function reportData()
    {
        if (empty($this->dateFrom) || empty($this->dateTo)) {
            return collect();
        }

        $dateFrom = Carbon::parse($this->dateFrom);
        $dateTo = Carbon::parse($this->dateTo)->endOfDay();

        return \App\Services\Cache\ReportCacheService::getReport(
            $this->reportType,
            $dateFrom->toDateString(),
            $dateTo->toDateString(),
            $this->organizationFilter,
            function () use ($dateFrom, $dateTo) {
            switch ($this->reportType) {
                case 'approved_events':
                    return Ticket::select(['ticket_id', 'ticket_number', 'title', 'status', 'created_at', 'user_id', 'event_type_id'])
                        ->with([
                            'user' => fn($q) => $q->select(['user_id', 'org_id'])
                                ->with('studentOrganization:org_id,org_name,logo'),
                            'events:event_id,ticket_id',
                            'eventType:event_type_id,type_name',
                        ])
                        ->where('status', 'approved')
                        ->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->when($this->organizationFilter, fn($query) => $query->whereHas('user', fn($q) => $q->where('org_id', $this->organizationFilter)))
                        ->orderBy('created_at', 'desc')
                        ->limit(1000) // Add limit for performance
                        ->get();

                case 'for_revision_events':
                    return Ticket::select(['ticket_id', 'ticket_number', 'title', 'status', 'created_at', 'user_id', 'event_type_id'])
                        ->with([
                            'user' => fn($q) => $q->select(['user_id', 'org_id'])
                                ->with('studentOrganization:org_id,org_name,logo'),
                            'events:event_id,ticket_id',
                            'eventType:event_type_id,type_name',
                        ])
                        ->where('status', 'for_revision')
                        ->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->when($this->organizationFilter, fn($query) => $query->whereHas('user', fn($q) => $q->where('org_id', $this->organizationFilter)))
                        ->orderBy('created_at', 'desc')
                        ->limit(1000) // Add limit for performance
                        ->get();

                case 'org_participation':
                    return Student_Organization::select(['org_id', 'org_name', 'org_code'])
                        ->withCount([
                            'tickets' => fn($query) => $query->whereBetween('tickets.created_at', [$dateFrom, $dateTo]),
                        ])
                        ->when($this->organizationFilter, fn($query) => $query->where('org_id', $this->organizationFilter))
                        ->orderBy('tickets_count', 'desc')
                        ->limit(100) // Add limit for performance
                        ->get();

                case 'monthly_summary':
                    // Use more efficient counting
                    return [
                        'total_tickets' => Ticket::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                        'approved_tickets' => Ticket::where('status', 'approved')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                        'for_revision_tickets' => Ticket::where('status', 'for_revision')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                        'pending_tickets' => Ticket::whereIn('status', ['pending', 'under_review'])->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                    ];

                default:
                    return collect();
            }
        });
    }

    protected function export()
    {
        $data = $this->reportData;

        // Excel fallback to CSV to avoid extra package dependency
        if ($this->exportFormat === 'excel') {
            $this->exportFormat = 'csv';
        }

        if ($this->exportFormat === 'csv') {
            return $this->exportCsv($data);
        }

        if ($this->exportFormat === 'pdf') {
            return $this->exportPdf($data);
        }

        abort(400, 'Unsupported export format.');
    }

    protected function exportCsv($data)
    {
        $fileName = 'osa-' . $this->reportType . '-' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, ['OSA Reports']);
            fputcsv($handle, ['Report Type', ucfirst(str_replace('_', ' ', $this->reportType))]);
            fputcsv($handle, ['Date Range', $this->dateFrom, $this->dateTo]);
            if ($this->organizationFilter) {
                fputcsv($handle, ['Organization Filter', (string) $this->organizationFilter]);
            }
            fputcsv($handle, []);

            switch ($this->reportType) {
                case 'approved_events':
                case 'for_revision_events':
                    fputcsv($handle, ['Ticket #', 'Title', 'Organization', 'Event Type', 'Status', 'Created At', 'Updated At']);
                    foreach ($data as $ticket) {
                        fputcsv($handle, [
                            $ticket->ticket_number,
                            $ticket->title,
                            optional($ticket->user->studentOrganization)->org_name ?? 'N/A',
                            optional($ticket->eventType)->type_name ?? 'N/A',
                            $ticket->status,
                            optional($ticket->created_at)?->format('Y-m-d H:i'),
                            optional($ticket->updated_at)?->format('Y-m-d H:i'),
                        ]);
                    }
                    break;

                case 'org_participation':
                    fputcsv($handle, ['Organization', 'Tickets Count']);
                    foreach ($data as $org) {
                        fputcsv($handle, [
                            $org->org_name,
                            $org->tickets_count,
                        ]);
                    }
                    break;

                case 'monthly_summary':
                    fputcsv($handle, ['Metric', 'Value']);
                    fputcsv($handle, ['Total Tickets', $data['total_tickets']]);
                    fputcsv($handle, ['Approved', $data['approved_tickets']]);
                    fputcsv($handle, ['For Revision', $data['for_revision_tickets']]);
                    fputcsv($handle, ['Pending', $data['pending_tickets']]);
                    break;
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function exportPdf($data)
    {
        if (! class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            throw new \RuntimeException('PDF export requires the barryvdh/laravel-dompdf package.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.osa.summary-pdf', [
            'reportType' => $this->reportType,
            'data' => $data,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'organizationFilter' => $this->organizationFilter,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $fileName = 'osa-' . $this->reportType . '-' . now()->format('YmdHis') . '.pdf';

        return response()->streamDownload(fn() => print($pdf->output()), $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Load all chart data for analytics (independent of report filters)
     */
    public function loadChartData()
    {
        // Charts show last 12 months data regardless of report filters
        $dateFrom = Carbon::now()->subMonths(12)->startOfMonth();
        $dateTo = Carbon::now()->endOfDay();

        $this->chartData = [
            'eventsByMonth' => \App\Services\Cache\ReportCacheService::getChartData('eventsByMonth', fn() => $this->getEventsByMonth($dateFrom, $dateTo)),
            'eventsByType' => \App\Services\Cache\ReportCacheService::getChartData('eventsByType', fn() => $this->getEventsByType($dateFrom, $dateTo)),
            'statusDistribution' => \App\Services\Cache\ReportCacheService::getChartData('statusDistribution', fn() => $this->getStatusDistribution()),
            'topOrganizations' => \App\Services\Cache\ReportCacheService::getChartData('topOrganizations', fn() => $this->getTopOrganizations()),
        ];
    }

    /**
     * Get events grouped by month for line chart (last 12 months)
     */
    protected function getEventsByMonth($dateFrom, $dateTo): array
    {
        $tickets = Ticket::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'labels' => $tickets->pluck('month')->map(fn($m) => Carbon::parse($m)->format('M Y'))->toArray(),
            'data' => $tickets->pluck('count')->toArray(),
        ];
    }

    /**
     * Get events grouped by type for bar chart (all time)
     */
    protected function getEventsByType($dateFrom, $dateTo): array
    {
        $tickets = Ticket::join('event__types', 'tickets.event_type_id', '=', 'event__types.event_type_id')
            ->select('event__types.type_name', DB::raw('COUNT(*) as count'))
            ->whereBetween('tickets.created_at', [$dateFrom, $dateTo])
            ->groupBy('event__types.event_type_id', 'event__types.type_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'labels' => $tickets->pluck('type_name')->toArray(),
            'data' => $tickets->pluck('count')->toArray(),
        ];
    }

    /**
     * Get ticket status distribution for doughnut chart (all time)
     */
    protected function getStatusDistribution(): array
    {
        $tickets = Ticket::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Define status colors and labels
        $statusConfig = [
            'approved' => ['label' => 'Approved', 'color' => 'rgb(34, 197, 94)'],
            'pending' => ['label' => 'Pending', 'color' => 'rgb(251, 191, 36)'],
            'under_review' => ['label' => 'Under Review', 'color' => 'rgb(59, 130, 246)'],
            'for_revision' => ['label' => 'For Revision', 'color' => 'rgb(239, 68, 68)'],
            'cancelled' => ['label' => 'Cancelled', 'color' => 'rgb(156, 163, 175)'],
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($tickets as $ticket) {
            $config = $statusConfig[$ticket->status] ?? ['label' => ucfirst(str_replace('_', ' ', $ticket->status)), 'color' => 'rgb(168, 85, 247)'];
            $labels[] = $config['label'];
            $data[] = $ticket->count;
            $colors[] = $config['color'];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
        ];
    }

    /**
     * Get top organizations by event count for horizontal bar chart (all time)
     */
    protected function getTopOrganizations(): array
    {
        $organizations = Student_Organization::select(['org_id', 'org_name'])
            ->withCount('tickets')
            ->orderByDesc('tickets_count')
            ->limit(8)
            ->get();

        return [
            'labels' => $organizations->pluck('org_name')->toArray(),
            'data' => $organizations->pluck('tickets_count')->toArray(),
        ];
    }
}
