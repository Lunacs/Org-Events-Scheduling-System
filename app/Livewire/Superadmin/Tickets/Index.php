<?php

namespace App\Livewire\Superadmin\Tickets;

use App\Models\Office;
use App\Models\Ticket;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    private const STATUS_REJECTED = 'for_revision';

    #[Title('Ticket Management - SuperAdmin')]
    #[Layout('components.layouts.superadmin')]

    #[Url(except: '')]
    public $search = '';

    #[Url(except: 'all')]
    public $statusFilter = 'all';

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

    // Delete modal
    public $showDeleteModal = false;

    public $deletingTicketId = null;

    public $deletingTicketTitle = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

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
                $q->where(function ($q2) {
                    $q2->where('title', 'like', "%{$this->search}%")
                        ->orWhere('ticket_number', 'like', "%{$this->search}%")
                        ->orWhereHas('user.studentOrganization', function ($q3) {
                            $q3->where('org_name', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
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

    // ── View ─────────────────────────────────────────────────────────

    public function viewTicketDetails(int $ticketId): void
    {
        $this->selectedTicket = Ticket::with([
            'user.studentOrganization',
            'events.eventSchedules',
            'eventType',
            'attachments',
            'venue',
            'alternateVenue',
            'fundSource',
        ])->find($ticketId);

        if ($this->selectedTicket) {
            $this->showDetailDrawer = true;
        }
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedTicket = null;
    }

    // ── Delete ───────────────────────────────────────────────────────

    public function openDeleteModal(int $ticketId, string $ticketTitle): void
    {
        $this->deletingTicketId = $ticketId;
        $this->deletingTicketTitle = $ticketTitle;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->reset(['deletingTicketId', 'deletingTicketTitle']);
    }

    public function confirmDelete(): void
    {
        $ticket = Ticket::find($this->deletingTicketId);

        if (! $ticket) {
            $this->error('Ticket not found!', position: 'toast-top');
            $this->closeDeleteModal();

            return;
        }

        DB::beginTransaction();
        try {
            TransactionLogService::logTicketOperation('deleted', $ticket);

            $ticket->delete();

            DB::commit();

            $this->closeDeleteModal();
            $this->refreshTickets(resetPage: true);

            $this->success('Ticket deleted successfully!', position: 'toast-top');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Ticket deletion failed', [
                'ticket_id' => $this->deletingTicketId,
                'error' => $e->getMessage(),
            ]);
            $this->error('Failed to delete ticket: '.$e->getMessage(), position: 'toast-top');
            $this->closeDeleteModal();
        }
    }

    // ── Reassign ─────────────────────────────────────────────────────

    public function openReassignModal(int $ticketId): void
    {
        $this->reassignTicketId = $ticketId;
        $this->newOfficeId = null;
        $this->showReassignModal = true;
    }

    public function reassignTicket(): void
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

            TransactionLogService::log(
                'REASSIGN',
                "SuperAdmin reassigned ticket {$ticket->ticket_number} from {$oldOffice} to {$newOffice}",
                Auth::user()->user_id
            );

            $this->success('Ticket reassigned successfully!', position: 'toast-top');
            $this->showReassignModal = false;
            $this->reassignTicketId = null;
            $this->newOfficeId = null;
            $this->refreshTickets();
        } catch (\Exception $e) {
            $this->error('Failed to reassign ticket: '.$e->getMessage(), position: 'toast-top');
        }
    }

    // ── Bulk Actions ─────────────────────────────────────────────────

    public function openBulkModal(string $action): void
    {
        if (empty($this->selectedTickets)) {
            $this->warning('Please select tickets first!', position: 'toast-top');

            return;
        }

        $this->bulkAction = $action;
        $this->showBulkModal = true;
    }

    public function executeBulkAction(): void
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
                        ->update(['status' => self::STATUS_REJECTED]);
                    $message = "Bulk rejected {$count} tickets";
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

            TransactionLogService::log(
                'BULK_ACTION',
                $message,
                Auth::user()->user_id
            );

            $this->success($message.' successfully!', position: 'toast-top');
            $this->selectedTickets = [];
            $this->showBulkModal = false;
            $this->bulkAction = '';
            $this->refreshTickets();
        } catch (\Exception $e) {
            $this->error('Failed to execute bulk action: '.$e->getMessage(), position: 'toast-top');
        }
    }

    // ── Filter Hooks ─────────────────────────────────────────────────

    public function updatedSearch(): void
    {
        $this->refreshTickets(resetPage: true);
    }

    public function updatedStatusFilter(): void
    {
        $this->refreshTickets(resetPage: true);
    }

    private function refreshTickets(bool $resetPage = false): void
    {
        if ($resetPage) {
            $this->resetPage();
        }

        unset($this->tickets);
    }

    // ── Computed Dropdown Data ───────────────────────────────────────

    #[Computed(persist: true, seconds: 1800)]
    public function offices()
    {
        return Office::select(['office_id', 'office_name', 'office_code'])
            ->orderBy('office_name')
            ->get();
    }

    // ── Render ───────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.superadmin.tickets.index')->with([
            'ticketsData' => $this->tickets,
            'headers' => $this->headers,
            'offices' => $this->offices,
        ]);
    }
}
