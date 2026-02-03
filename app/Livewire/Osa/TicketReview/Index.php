<?php

namespace App\Livewire\Osa\TicketReview;

use App\Models\Student_Organization;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Title('Ticket Review - OSA Admin')]
    #[Layout('components.layouts.app')]

    #[Url(except: '')]
    public $search = '';

    #[Url(except: '')]
    public $statusFilter = '';

    #[Url(except: '')]
    public $organizationFilter = '';

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'organizationFilter']);
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedOrganizationFilter()
    {
        $this->resetPage();
    }

    #[Computed(persist: true, seconds: 3600)]
    public function organizations()
    {
        return Cache::remember('osa_ticket_review_organizations', 3600, function () {
            return Student_Organization::withTrashed()
                ->select(['org_id', 'org_name', 'deleted_at'])
                ->orderBy('org_name')
                ->get();
        });
    }

    public function render()
    {
        // Use the computed property
        return view('livewire.osa.ticket-review.index', [
            'tickets' => $this->tickets(),
        ]);
    }

    #[Computed(persist: true, seconds: 300)]
    public function tickets()
    {
        return Ticket::select([
            'ticket_id',
            'ticket_number',
            'title',
            'description',
            'status',
            'venue_requested',
            'venue_other',
            'date_from',
            'user_id',
            'event_type_id',
            'created_at',
        ])
            ->with([
                'user' => fn($q) => $q->select(['user_id', 'org_id'])
                    ->with('studentOrganization:org_id,org_name'),
                'events' => fn($q) => $q->select(['event_id', 'ticket_id'])
                    ->with('eventSchedules:schedule_id,event_id,start_date,start_time,venue'),
                'attachments:attachment_id,ticket_id,file_path,file_name',
                'eventType:event_type_id,type_name',
            ])
            ->when($this->search, function ($query) {
                // Optimize search - use index-friendly queries
                $searchTerm = '%' . $this->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', $searchTerm)
                        ->orWhere('ticket_number', 'like', $searchTerm);
                });
            })
            ->when($this->statusFilter === '', function ($query) {
                // When no filter is selected, exclude completed tickets by default
                $query->where('status', '!=', 'completed');
            })
            ->when($this->statusFilter !== '', function ($query) {
                // When a specific filter is selected, show only those tickets
                $query->where('status', $this->statusFilter);
            })
            ->when($this->organizationFilter, fn($query) => $query->whereHas('user', function ($q) {
                $q->where('org_id', $this->organizationFilter);
            }))
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Reduced from 12 to 10 for faster loads
    }
}
