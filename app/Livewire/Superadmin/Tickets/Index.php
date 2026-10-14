<?php

namespace App\Livewire\Superadmin\Tickets;

use App\Models\Ticket;
use App\Services\TicketWorkflowService;
use App\Services\TransactionLogService;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use Toast, WithPagination;

    #[Title('Ticket Management - SuperAdmin')]
    #[Layout('components.layouts.superadmin')]

    #[Url(except: '')]
    public $search = '';

    #[Url(except: 'all')]
    public $statusFilter = 'all';

    // Selected ticket details
    public $selectedTicket = null;

    public $showDetailDrawer = false;

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

        if (! in_array($this->bulkAction, ['approve', 'reject'], true)) {
            $this->warning('Invalid action!', position: 'toast-top');

            return;
        }

        // Each ticket goes through the same workflow as an OSA decision, so approved
        // tickets get their event and schedule, history, audit log and notifications.
        $workflow = app(TicketWorkflowService::class);
        $actor = Auth::user();
        $remarks = $this->bulkAction === 'approve'
            ? 'Approved by the superadmin (bulk action).'
            : 'Sent back for revision by the superadmin (bulk action).';

        $tickets = Ticket::query()->whereIn('ticket_id', $this->selectedTickets)->get();
        $done = 0;

        foreach ($tickets as $ticket) {
            if (! in_array($ticket->status, TicketWorkflowService::OSA_DECIDABLE_STATUSES, true)) {
                continue;
            }

            try {
                $this->bulkAction === 'approve'
                    ? $workflow->approve($ticket, $actor, $remarks)
                    : $workflow->requestRevision($ticket, $actor, $remarks);
                $done++;
            } catch (\DomainException) {
                // The ticket moved on since the list was loaded; leave it as it is.
            }
        }

        $verb = $this->bulkAction === 'approve' ? 'approved' : 'sent back for revision';
        $skipped = count($this->selectedTickets) - $done;
        $message = "Bulk {$verb} {$done} tickets".($skipped > 0 ? " ({$skipped} skipped: not awaiting an OSA decision)" : '');

        TransactionLogService::log('BULK_ACTION', $message, $actor->user_id);

        $done > 0 ? $this->success($message.'.', position: 'toast-top') : $this->warning($message.'.', position: 'toast-top');
        $this->selectedTickets = [];
        $this->showBulkModal = false;
        $this->bulkAction = '';
        $this->refreshTickets();
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

    // ── Render ───────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.superadmin.tickets.index')->with([
            'ticketsData' => $this->tickets,
            'headers' => $this->headers,
        ]);
    }
}
