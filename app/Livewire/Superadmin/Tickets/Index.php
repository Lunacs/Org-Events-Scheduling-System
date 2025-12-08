<?php

namespace App\Livewire\Superadmin\Tickets;

use App\Models\Office;
use App\Models\Ticket;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast, WithPagination;

    #[Title('Ticket Management - SuperAdmin')]
    #[Layout('components.layouts.superadmin')]

    // Filters with URL state
    #[Url(except: '')]
    public $search = '';

    #[Url(except: 'all')]
    public $statusFilter = 'all';

    #[Url(except: '')]
    public $officeFilter = '';

    public $dateFrom;

    public $dateTo;

    // Selected ticket details
    public $selectedTicket = null;

    public $showDetailDrawer = false;

    // Reassignment
    public $showReassignModal = false;

    public $reassignTicketId = null;

    public $newOfficeId = null;

    // Bulk actions
    public $selectedTickets = [];

    public $showBulkModal = false;

    public $bulkAction = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function mount()
    {
        // Default to last 30 days
        $this->dateTo = now()->format('Y-m-d');
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
    }

    #[Computed]
    public function tickets()
    {
        return Ticket::select([
            'ticket_id',
            'ticket_number',
            'title',
            'status',
            'created_at',
            'user_id',
            'event_type_id',
        ])
            ->with([
                'user' => fn ($q) => $q->select(['user_id', 'org_id'])
                    ->with('studentOrganization:org_id,org_name,org_code,logo'),
                'eventType:event_type_id,type_name',
                'events:event_id,ticket_id',
            ])
            ->when($this->search, function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('ticket_number', 'like', "%{$this->search}%")
                    ->orWhereHas('user.studentOrganization', function ($q2) {
                        $q2->where('org_name', 'like', "%{$this->search}%");
                    });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->officeFilter, function ($q) {
                $q->where('office_id', $this->officeFilter);
            })
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->orderBy(...array_values($this->sortBy))
            ->paginate(15);
    }

    #[Computed]
    public function headers(): array
    {
        return [
            ['key' => 'ticket_number', 'label' => 'Ticket #', 'sortable' => true],
            ['key' => 'title', 'label' => 'Title', 'sortable' => true],
            ['key' => 'organization', 'label' => 'Organization'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true],
            ['key' => 'created_at', 'label' => 'Submitted', 'sortable' => true],
            ['key' => 'actions', 'label' => 'Actions'],
        ];
    }

    public function viewTicketDetails($ticketId)
    {
        $this->selectedTicket = Ticket::with([
            'user.studentOrganization',
            'events.eventSchedules',
            'eventType',
            'attachments',
        ])->find($ticketId);

        if ($this->selectedTicket) {
            $this->showDetailDrawer = true;
        }
    }

    public function closeDetailDrawer()
    {
        $this->showDetailDrawer = false;
        $this->selectedTicket = null;
    }

    public function openReassignModal($ticketId)
    {
        $this->reassignTicketId = $ticketId;
        $this->newOfficeId = null;
        $this->showReassignModal = true;
    }

    public function reassignTicket()
    {
        $this->validate([
            'newOfficeId' => 'required|exists:offices,office_id',
        ]);

        try {
            $ticket = Ticket::find($this->reassignTicketId);

            if (! $ticket) {
                $this->error('Ticket not found!', position: 'toast-top');

                return;
            }

            $oldOffice = $ticket->office->office_name ?? 'None';
            $newOffice = Office::find($this->newOfficeId)->office_name;

            $ticket->update(['office_id' => $this->newOfficeId]);

            // Log action
            TransactionLogService::log(
                'REASSIGN',
                "SuperAdmin reassigned ticket {$ticket->ticket_number} from {$oldOffice} to {$newOffice}",
                Auth::user()->user_id
            );

            $this->success('Ticket reassigned successfully!', position: 'toast-top');
            $this->showReassignModal = false;
            $this->reassignTicketId = null;
            $this->newOfficeId = null;
            unset($this->tickets);
        } catch (\Exception $e) {
            $this->error('Failed to reassign ticket: '.$e->getMessage(), position: 'toast-top');
        }
    }

    public function openBulkModal($action)
    {
        if (empty($this->selectedTickets)) {
            $this->warning('Please select tickets first!', position: 'toast-top');

            return;
        }

        $this->bulkAction = $action;
        $this->showBulkModal = true;
    }

    public function executeBulkAction()
    {
        if (empty($this->selectedTickets)) {
            $this->warning('No tickets selected!', position: 'toast-top');

            return;
        }

        try {
            $count = count($this->selectedTickets);

            switch ($this->bulkAction) {
                case 'approve':
                    Ticket::whereIn('ticket_id', $this->selectedTickets)
                        ->update(['status' => 'approved']);
                    $message = "Bulk approved {$count} tickets";
                    break;

                case 'reject':
                    Ticket::whereIn('ticket_id', $this->selectedTickets)
                        ->update(['status' => '']);
                    $message = "Bulk  {$count} tickets";
                    break;

                case 'cancel':
                    Ticket::whereIn('ticket_id', $this->selectedTickets)
                        ->update(['status' => 'cancelled']);
                    $message = "Bulk cancelled {$count} tickets";
                    break;

                default:
                    $this->warning('Invalid action!', position: 'toast-top');

                    return;
            }

            // Log action
            TransactionLogService::log(
                'BULK_ACTION',
                $message,
                Auth::user()->user_id
            );

            $this->success($message.' successfully!', position: 'toast-top');
            $this->selectedTickets = [];
            $this->showBulkModal = false;
            $this->bulkAction = '';
            unset($this->tickets);
        } catch (\Exception $e) {
            $this->error('Failed to execute bulk action: '.$e->getMessage(), position: 'toast-top');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
        unset($this->tickets);
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
        unset($this->tickets);
    }

    public function updatedOfficeFilter()
    {
        $this->resetPage();
        unset($this->tickets);
    }

    public function render()
    {
        return view('livewire.superadmin.tickets.index')->with([
            'ticketsData' => $this->tickets,
            'headers' => $this->headers,
            'offices' => $this->offices,
        ]);
    }

    #[Computed(persist: true, seconds: 1800)] // Cache for 30 minutes
    public function offices()
    {
        return Office::select(['office_id', 'office_name', 'office_code'])
            ->orderBy('office_name')
            ->get();
    }
}
