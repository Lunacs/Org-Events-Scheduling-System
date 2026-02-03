<?php

namespace App\Livewire\Osa;

use App\Models\Student_Organization;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
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

    #[Computed]
    public function tickets()
    {
        return Ticket::select([
            'ticket_id',
            'ticket_number',
            'title',
            'description',
            'status',
            'created_at',
            'user_id',
            'event_type_id',
        ])
            ->with([
                'user' => fn ($q) => $q->withTrashed()
                    ->select(['user_id', 'org_id'])
                    ->with('studentOrganization:org_id,org_name,org_code,logo'),
                'eventType:event_type_id,type_name',
            ])
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->organizationFilter, fn ($query) => $query->whereHas('user', function ($q) {
                $q->withTrashed()->where('org_id', $this->organizationFilter);
            }))
            ->when($this->dateFilter, fn ($query) => $query->whereDate('created_at', $this->dateFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.osa.ticket-management', [
            'tickets' => $this->tickets,
            'organizations' => $this->organizations,
        ]);
    }

    #[Computed(persist: true, seconds: 3600)]
    public function organizations()
    {
        return Cache::remember('osa_organizations_list', 3600, function () {
            return Student_Organization::select(['org_id', 'org_name'])
                ->orderBy('org_name')
                ->get();
        });
    }
}
