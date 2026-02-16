<?php

namespace App\Livewire\Osa;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Archive extends Component
{
    use WithPagination;

    #[Title('Archive Access - OSA Admin')]
    #[Layout('components.layouts.app')]

    #[Url(except: '')]
    public $search = '';

    #[Url(except: '')]
    public $statusFilter = '';

    #[Url(except: '')]
    public $organizationFilter = '';

    #[Url(except: '')]
    public $yearFilter = '';

    #[Url(except: '')]
    public $eventTypeFilter = '';

    public $selectedEvent = null; // deprecated; kept for BC, not used now

    public ?int $selectedEventId = null;

    public $showModal = false;

    public function mount()
    {
        $this->yearFilter = Carbon::now()->year;
    }

    public function viewArchivedEvent($eventId)
    {
        // Open modal immediately; child component will fetch details
        $this->selectedEventId = (int) $eventId;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedEvent = null;
        $this->selectedEventId = null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingOrganizationFilter()
    {
        $this->resetPage();
    }

    public function updatingYearFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->organizationFilter = '';
        $this->yearFilter = Carbon::now()->year;
        $this->eventTypeFilter = '';
        $this->resetPage();
    }

    public function applyFilters()
    {
        // Explicitly re-query with current deferred filter values
        $this->resetPage();
    }

    #[Computed]
    public function archivedEvents()
    {
        return Event::select(['event_id', 'ticket_id', 'event__type_id', 'notes', 'created_at'])
            ->with([
                'ticket' => fn($q) => $q->select(['ticket_id', 'title', 'description', 'status', 'user_id', 'venue_requested', 'updated_at'])
                    ->with([
                        'user' => fn($qu) => $qu->select(['user_id', 'org_id'])
                            ->with('studentOrganization:org_id,org_name,logo'),
                    ])
                    ->withCount('attachments'),
                'eventSchedules:schedule_id,event_id,start_date,start_time,venue',
                'eventType:event_type_id,type_name',
            ])
            ->whereHas('ticket', fn($query) => $query->where('status', 'completed'))
            ->when($this->search, fn($query) => $query->whereHas('ticket', fn($q) => $q->where('title', 'like', '%' . $this->search . '%')))
            ->when($this->statusFilter, fn($query) => $query->whereHas('ticket', fn($q) => $q->where('status', $this->statusFilter)))
            ->when($this->organizationFilter, fn($query) => $query->whereHas('ticket.user', fn($q) => $q->where('org_id', $this->organizationFilter)))
            ->when($this->yearFilter, fn($query) => $query->whereYear('created_at', $this->yearFilter))
            ->when($this->eventTypeFilter, fn($query) => $query->where('event__type_id', $this->eventTypeFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Reduced from 12 to 10 for faster loads
    }

    public function render()
    {
        return view('livewire.osa.archive', [
            'archivedEvents' => $this->archivedEvents,
            'availableYears' => $this->availableYears,
            'organizations' => $this->organizations,
        ]);
    }

    #[Computed]
    public function availableYears()
    {
        return Cache::remember('osa_archive_available_years', 600, function () {
            return Event::selectRaw('YEAR(created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');
        });
    }

    #[Computed(persist: true, seconds: 3600)]
    public function organizations()
    {
        return \Illuminate\Support\Facades\Cache::remember('osa_organizations_list', 3600, function () {
            return \App\Models\Student_Organization::select(['org_id', 'org_name'])
                ->orderBy('org_name')
                ->get();
        });
    }
}
