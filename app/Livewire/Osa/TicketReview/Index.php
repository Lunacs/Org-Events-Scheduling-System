<?php

namespace App\Livewire\Osa\TicketReview;

use App\Models\Ticket;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Title('Ticket Review & Attachments - OSA Admin')]
    #[Layout('components.layouts.app')]

    #[Url(except: '')]
    public $search = '';

    #[Url(except: 'pending')]
    public $statusFilter = 'pending';

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = 'pending';
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

    #[Computed]
    public function tickets()
    {
        return Ticket::select([
            'ticket_id', 'ticket_number', 'title', 'description', 'status',
            'date-requested', 'venue-requested', 'user_id', 'event_type_id', 'created_at',
        ])
            ->with([
                'user' => fn ($q) => $q->select(['user_id', 'org_id'])
                    ->with('studentOrganization:org_id,org_name'),
                'events.eventSchedules',
                'attachments:attachment_id,ticket_id,file_path,file_name',
                'eventType:event_type_id,type_name',
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%')
                        ->orWhere('ticket_number', 'like', '%'.$this->search.'%')
                        ->orWhereHas('user.studentOrganization', function ($orgQuery) {
                            $orgQuery->where('org_name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);
    }

    public function render()
    {

        return view('livewire.osa.ticket-review.index', [
            'tickets' => $this->tickets(),
        ]);
    }
}
