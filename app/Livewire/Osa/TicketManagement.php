<?php

namespace App\Livewire\Osa;

use App\Models\OSA_Approval;
use App\Models\Ticket;
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

    public function approveTicket($ticketId)
    {
        // Optimize: Load ticket with required relationships to avoid N+1
        $ticket = Ticket::select(['ticket_id', 'ticket_number', 'status', 'user_id', 'event_type_id'])
            ->with(['user:user_id,name,email'])
            ->findOrFail($ticketId);

        $oldStatus = $ticket->status;

        $ticket->update(['status' => 'approved']);

        // Create OSA approval record
        OSA_Approval::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => auth()->id(),
            'decision' => 'approved',
            'remarks' => 'Ticket approved from ticket management.',
        ]);

        // Notify ticket owner about status change
        $ticket->user->notify(new \App\Notifications\TicketStatusUpdatedNotification(
            $ticket,
            $oldStatus,
            'approved',
            'Ticket approved from ticket management.'
        ));

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $ticket->ticket_id, newStatus: 'approved');
        $this->dispatch('notification-received', [
            'title' => 'Ticket Approved',
            'message' => "Your ticket {$ticket->ticket_number} has been approved!",
            'type' => 'success',
        ])->to($ticket->user);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Success',
            'description' => 'Ticket has been approved successfully.',
        ]);
    }

    public function rejectTicket($ticketId)
    {
        // Optimize: Load ticket with required relationships to avoid N+1
        $ticket = Ticket::select(['ticket_id', 'ticket_number', 'status', 'user_id'])
            ->with(['user:user_id,name,email'])
            ->findOrFail($ticketId);

        $oldStatus = $ticket->status;

        $ticket->update(['status' => 'rejected']);

        // Create OSA approval record
        OSA_Approval::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => auth()->id(),
            'decision' => 'rejected',
            'remarks' => 'Ticket rejected from ticket management.',
        ]);

        // Notify ticket owner about status change
        $ticket->user->notify(new \App\Notifications\TicketStatusUpdatedNotification(
            $ticket,
            $oldStatus,
            'rejected',
            'Ticket rejected from ticket management.'
        ));

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $ticket->ticket_id, newStatus: 'rejected');
        $this->dispatch('notification-received', [
            'title' => 'Ticket Rejected',
            'message' => "Your ticket {$ticket->ticket_number} has been rejected.",
            'type' => 'error',
        ])->to($ticket->user);

        $this->dispatch('toast', [
            'type' => 'error',
            'title' => 'Rejected',
            'description' => 'Ticket has been rejected.',
        ]);
    }

    public function rescheduleTicket($ticketId)
    {
        // Optimize: Load ticket with required relationships to avoid N+1
        $ticket = Ticket::select(['ticket_id', 'ticket_number', 'status', 'user_id'])
            ->with(['user:user_id,name,email'])
            ->findOrFail($ticketId);

        $oldStatus = $ticket->status;

        $ticket->update(['status' => 'for_rescheduling']);

        // Create OSA approval record
        OSA_Approval::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => auth()->id(),
            'decision' => 'for_rescheduling',
            'remarks' => 'Reschedule requested from ticket management.',
        ]);

        // Notify ticket owner about status change
        $ticket->user->notify(new \App\Notifications\TicketStatusUpdatedNotification(
            $ticket,
            $oldStatus,
            'for_rescheduling',
            'Reschedule requested from ticket management.'
        ));

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $ticket->ticket_id, newStatus: 'for_rescheduling');
        $this->dispatch('notification-received', [
            'title' => 'Reschedule Requested',
            'message' => "Your ticket {$ticket->ticket_number} has been marked for rescheduling.",
            'type' => 'info',
        ])->to($ticket->user);

        $this->dispatch('toast', [
            'type' => 'info',
            'title' => 'Reschedule Requested',
            'description' => 'Ticket has been marked for rescheduling.',
        ]);
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
                'user' => fn($q) => $q->select(['user_id', 'org_id'])
                    ->with('studentOrganization:org_id,org_name,org_code'),
                'eventType:event_type_id,type_name',
            ])
            ->when($this->search, fn($query) => $query->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter, fn($query) => $query->where('status', $this->statusFilter))
            ->when($this->organizationFilter, fn($query) => $query->whereHas('user', function ($q) {
                $q->where('org_id', $this->organizationFilter);
            }))
            ->when($this->dateFilter, fn($query) => $query->whereDate('created_at', $this->dateFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Increased from 5 to 10 for better UX
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
        return \Illuminate\Support\Facades\Cache::remember('osa_organizations_list', 3600, function () {
            return \App\Models\Student_Organization::select(['org_id', 'org_name'])
                ->orderBy('org_name')
                ->get();
        });
    }
}
