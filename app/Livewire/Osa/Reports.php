<?php

namespace App\Livewire\Osa;

use App\Models\Student_Organization;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

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

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function generateReport()
    {
        $this->validate([
            'reportType' => 'required|in:approved_events,rejected_events,org_participation,monthly_summary',
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

        $cacheKey = "osa_report_{$this->reportType}_{$this->dateFrom}_{$this->dateTo}_{$this->organizationFilter}";

        return Cache::remember($cacheKey, 600, function () use ($dateFrom, $dateTo) {
            switch ($this->reportType) {
                case 'approved_events':
                    return Ticket::select(['ticket_id', 'ticket_number', 'title', 'status', 'created_at', 'user_id', 'event_type_id'])
                        ->with([
                            'user' => fn($q) => $q->select(['user_id', 'org_id'])
                                ->with('studentOrganization:org_id,org_name'),
                            'events:event_id,ticket_id',
                            'eventType:event_type_id,type_name',
                        ])
                        ->where('status', 'approved')
                        ->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->when($this->organizationFilter, fn($query) => $query->whereHas('user', fn($q) => $q->where('org_id', $this->organizationFilter)))
                        ->orderBy('created_at', 'desc')
                        ->limit(1000) // Add limit for performance
                        ->get();

                case 'rejected_events':
                    return Ticket::select(['ticket_id', 'ticket_number', 'title', 'status', 'created_at', 'user_id', 'event_type_id'])
                        ->with([
                            'user' => fn($q) => $q->select(['user_id', 'org_id'])
                                ->with('studentOrganization:org_id,org_name'),
                            'events:event_id,ticket_id',
                            'eventType:event_type_id,type_name',
                        ])
                        ->where('status', 'rejected')
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
                        'rejected_tickets' => Ticket::where('status', 'rejected')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
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
                case 'rejected_events':
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
                    fputcsv($handle, ['Rejected', $data['rejected_tickets']]);
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
}
