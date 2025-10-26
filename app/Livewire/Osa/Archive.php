<?php

namespace App\Livewire\Osa;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Event;
use App\Models\Ticket;
use Carbon\Carbon;

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
    
    public $selectedEvent = null;
    public $showModal = false;

    public function mount()
    {
        $this->yearFilter = Carbon::now()->year;
    }

    public function viewArchivedEvent($eventId)
    {
        $this->selectedEvent = Event::select(['event_id', 'title', 'ticket_id', 'event__type_id', 'created_at'])
            ->with([
                'ticket:ticket_id,ticket_number,title,description,status,user_id' => fn($q) => $q->with([
                    'user:user_id,org_id' => fn($q) => $q->with('studentOrganization:org_id,org_name'),
                    'osaApprovals:osa_approval_id,ticket_id,status,comments,approved_at',
                    'attachments:attachment_id,ticket_id,file_path,file_name'
                ]),
                'eventSchedules:schedule_id,event_id,start_date,end_date,start_time,end_time',
                'eventType:event_type_id,type_name'
            ])
            ->find($eventId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedEvent = null;
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

    public function render()
    {
        $archivedEvents = Event::select(['event_id', 'title', 'ticket_id', 'event__type_id', 'created_at'])
            ->with([
                'ticket' => fn($q) => $q->select(['ticket_id', 'status', 'user_id'])
                    ->with([
                        'user' => fn($q) => $q->select(['user_id', 'org_id'])
                            ->with('studentOrganization:org_id,org_name')
                    ]),
                'eventSchedules:schedule_id,event_id,start_date,start_time',
                'eventType:event_type_id,type_name'
            ])
            ->whereHas('ticket', fn($query) => $query->whereIn('status', ['approved', 'rejected', 'completed']))
            ->when($this->search, fn($query) => $query->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter, fn($query) => $query->whereHas('ticket', fn($q) => $q->where('status', $this->statusFilter)))
            ->when($this->organizationFilter, fn($query) => $query->whereHas('ticket.user', fn($q) => $q->where('org_id', $this->organizationFilter)))
            ->when($this->yearFilter, fn($query) => $query->whereYear('created_at', $this->yearFilter))
            ->when($this->eventTypeFilter, fn($query) => $query->where('event__type_id', $this->eventTypeFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.osa.archive', [
            'archivedEvents' => $archivedEvents,
            'availableYears' => $this->availableYears
        ]);
    }

    #[Computed]
    public function availableYears()
    {
        return Event::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    }
}
