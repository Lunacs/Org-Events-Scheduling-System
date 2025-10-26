<?php

namespace App\Livewire\Osa;

use App\Models\OSA_Approval;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Approvals extends Component
{
    use WithPagination;

    #[Title('Approvals - OSA Admin')]
    #[Layout('components.layouts.app')]
    
    public $selectedTicket = null;
    public $showModal = false;
    public $approvalAction = '';
    public $comments = '';

    #[Url(except: '')]
    public $search = '';

    #[Url(except: 'pending_osa_approval')]
    public $statusFilter = 'pending_osa_approval';

    public function viewApproval($ticketId)
    {
        $this->selectedTicket = Ticket::select([
                'ticket_id', 'ticket_number', 'title', 'description', 'status',
                'user_id', 'event_type_id', 'created_at'
            ])
            ->with([
                'user' => fn($q) => $q->select(['user_id', 'org_id'])
                    ->with('studentOrganization:org_id,org_name'),
                'events:event_id,ticket_id,title,expected_attendees,venue',
                'attachments:attachment_id,ticket_id',
                'osaApprovals:osa_approval_id,ticket_id,status,comments,approved_at,approved_by',
                'eventType:event_type_id,type_name'
            ])
            ->find($ticketId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedTicket = null;
        $this->approvalAction = '';
        $this->comments = '';
    }

    public function processApproval()
    {
        $this->validate([
            'approvalAction' => 'required|in:approved,rejected',
            'comments' => 'nullable|string|max:1000',
        ]);

        if ($this->selectedTicket) {
            // Create or update OSA approval
            OSA_Approval::updateOrCreate(
                ['ticket_id' => $this->selectedTicket->ticket_id],
                [
                    'status' => $this->approvalAction,
                    'comments' => $this->comments,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]
            );

            // Update ticket status
            $this->selectedTicket->update([
                'status' => $this->approvalAction === 'approved' ? 'approved' : 'rejected',
            ]);

            session()->flash('message', 'Approval processed successfully!');
            $this->closeModal();
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = 'pending_osa_approval';
        $this->resetPage();
    }

    public function render()
    {
        $tickets = Ticket::select([
                'ticket_id', 'ticket_number', 'title', 'description', 'status', 
                'created_at', 'user_id', 'event_type_id'
            ])
            ->with([
                'user' => fn($q) => $q->select(['user_id', 'org_id'])
                    ->with('studentOrganization:org_id,org_name'),
                'events:event_id,ticket_id,expected_attendees,venue',
                'osaApprovals:osa_approval_id,ticket_id,status,comments,approved_at',
                'eventType:event_type_id,type_name,name'
            ])
            ->withCount('attachments')
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.osa.approvals', compact('tickets'));
    }
}
