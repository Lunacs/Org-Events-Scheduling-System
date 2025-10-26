<?php

namespace App\Livewire\Osa;

use App\Models\Ticket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TicketManagement extends Component
{
    use WithPagination;

    #[Title('Ticket Management - OSA Admin')]
    #[Layout('components.layouts.app')]

    #[Url(except: '')]
    public $search = '';

    #[Url(except: '')]
    public $statusFilter = '';

    #[Url(except: '')]
    public $organizationFilter = '';

    #[Url(except: '')]
    public $dateFilter = '';

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

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->organizationFilter = '';
        $this->dateFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $tickets = Ticket::select([
            'ticket_id', 'ticket_number', 'title', 'description', 'status',
            'created_at', 'user_id', 'event_type_id',
        ])
            ->with([
                'user' => fn ($q) => $q->select(['user_id', 'org_id'])
                    ->with('studentOrganization:org_id,org_name,org_code'),
                'eventType:event_type_id,type_name',
            ])
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->organizationFilter, fn ($query) => $query->whereHas('user', function ($q) {
                $q->where('org_id', $this->organizationFilter);
            }))
            ->when($this->dateFilter, fn ($query) => $query->whereDate('created_at', $this->dateFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('livewire.osa.ticket-management', compact('tickets'));
    }
}
