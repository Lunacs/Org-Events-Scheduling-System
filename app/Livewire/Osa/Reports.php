<?php

namespace App\Livewire\Osa;

use App\Models\Student_Organization;
use App\Models\Ticket;
use Carbon\Carbon;
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

        // Logic to generate and download report
        session()->flash('message', 'Report generated successfully!');
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

    #[Computed]
    public function reportData()
    {
        if (empty($this->dateFrom) || empty($this->dateTo)) {
            return collect();
        }

        $dateFrom = Carbon::parse($this->dateFrom);
        $dateTo = Carbon::parse($this->dateTo)->endOfDay();

        switch ($this->reportType) {
            case 'approved_events':
                return Ticket::select(['ticket_id', 'ticket_number', 'title', 'status', 'created_at', 'user_id', 'event_type_id'])
                    ->with([
                        'user' => fn ($q) => $q->select(['user_id', 'org_id'])
                            ->with('studentOrganization:org_id,org_name'),
                        'events:event_id,ticket_id',
                        'eventType:event_type_id,type_name',
                    ])
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->when($this->organizationFilter, fn ($query) => $query->whereHas('user', fn ($q) => $q->where('org_id', $this->organizationFilter)))
                    ->orderBy('created_at', 'desc')
                    ->get();

            case 'rejected_events':
                return Ticket::select(['ticket_id', 'ticket_number', 'title', 'status', 'created_at', 'user_id', 'event_type_id'])
                    ->with([
                        'user' => fn ($q) => $q->select(['user_id', 'org_id'])
                            ->with('studentOrganization:org_id,org_name'),
                        'events:event_id,ticket_id',
                        'eventType:event_type_id,type_name',
                    ])
                    ->where('status', 'rejected')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->when($this->organizationFilter, fn ($query) => $query->whereHas('user', fn ($q) => $q->where('org_id', $this->organizationFilter)))
                    ->orderBy('created_at', 'desc')
                    ->get();

            case 'org_participation':
                return Student_Organization::select(['org_id', 'org_name', 'org_code'])
                    ->withCount([
                        'tickets' => fn ($query) => $query->whereBetween('tickets.created_at', [$dateFrom, $dateTo]),
                    ])
                    ->when($this->organizationFilter, fn ($query) => $query->where('org_id', $this->organizationFilter))
                    ->orderBy('tickets_count', 'desc')
                    ->get();

            case 'monthly_summary':
                return [
                    'total_tickets' => Ticket::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                    'approved_tickets' => Ticket::where('status', 'approved')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                    'rejected_tickets' => Ticket::where('status', 'rejected')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                    'pending_tickets' => Ticket::whereIn('status', ['pending', 'under_review'])->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                ];

            default:
                return collect();
        }
    }
}
