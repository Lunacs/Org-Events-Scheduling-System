<?php

namespace App\Livewire\Gso;

use App\Models\Office_Approval;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Approvals extends Component
{
    #[Title('Approvals Management - GSO')]
    #[Layout('components.layouts.gso-layout')]
    public string $search = '';

    public string $statusFilter = 'pending';

    public string $priorityFilter = 'all';

    public array $selectedRequests = [];

    public bool $showConfirmationModal = false;
    public ?int $selectedApprovalId = null;
    public string $actionType = '';
    public string $confirmationInput = '';

    public function render()
    {
        $user = Auth::user();
        $officeId = $user?->office_id;

        $baseQuery = $this->baseQuery($officeId);

        $pendingQuery = (clone $baseQuery)->where('decision', 'pending');

        $stats = [
            'pending' => (clone $pendingQuery)->count(),
            'todayApproved' => (clone $baseQuery)
                ->where('decision', 'approved')
                ->whereDate('updated_at', Carbon::today())
                ->count(),
            'urgent' => (clone $pendingQuery)
                ->whereHas('ticket', fn(Builder $query) => $query->where('total_participants', '>=', 200))
                ->count(),
        ];

        $decision = $this->normalizeStatusFilter($this->statusFilter);

        $approvals = (clone $baseQuery)
            ->where('decision', $decision)
            ->when($this->search !== '', fn(Builder $query) => $this->applySearchFilter($query, $this->search))
            ->when($this->priorityFilter !== 'all', fn(Builder $query) => $this->applyPriorityFilter($query, $this->priorityFilter))
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn(Office_Approval $approval) => $this->transformApproval($approval));

        // If a search term is present, sort results by relevance: event name matches first,
        // then organization name matches, then fallback to most recently updated.
        if ($this->search !== '') {
            $term = Str::lower(trim($this->search));

            $approvals = $approvals->sort(function ($a, $b) use ($term) {
                $aEvent = strtolower($a['event_name'] ?? '');
                $bEvent = strtolower($b['event_name'] ?? '');
                $aOrg = strtolower($a['organization'] ?? '');
                $bOrg = strtolower($b['organization'] ?? '');

                $score = function ($event, $org) use ($term) {
                    if ($term !== '' && strpos($event, $term) !== false) return 1;
                    if ($term !== '' && strpos($org, $term) !== false) return 2;
                    return 3;
                };

                $sa = $score($aEvent, $aOrg);
                $sb = $score($bEvent, $bOrg);

                if ($sa !== $sb) {
                    return $sa <=> $sb;
                }

                return ($b['updated_at_ts'] ?? 0) <=> ($a['updated_at_ts'] ?? 0);
            })->values();
        }

        return view('livewire.gso.approvals', [
            'approvals' => $approvals,
            'stats' => $stats,
            'statusDefinitions' => $this->statusDefinitions(),
        ]);
    }

    protected $listeners = [
        // allow external callers or self to trigger a re-render
        'refreshApprovals' => '$refresh',
    ];

    protected function baseQuery(?int $officeId): Builder
    {
        return Office_Approval::query()
            ->with([
                'ticket.eventType',
                'ticket.user.studentOrganization',
            ])
            ->when($officeId, fn(Builder $query) => $query->where('office_id', $officeId));
    }

    protected function normalizeStatusFilter(string $status): string
    {
        $normalized = Str::of($status)->lower()->snake()->toString();

        return $this->statusDefinitions()[$normalized]['key'] ?? 'pending';
    }

    protected function statusDefinitions(): array
    {
        return [
            'pending' => ['key' => 'pending', 'label' => 'Pending'],
            'approved' => ['key' => 'approved', 'label' => 'Approved'],
            'rejected' => ['key' => 'rejected', 'label' => 'Rejected'],
        ];
    }

    protected function applySearchFilter(Builder $query, string $search): void
    {
        $term = Str::lower(trim($search));

        if ($term === '') {
            return;
        }

        $likeTerm = '%' . $term . '%';

        $query->whereHas('ticket', function (Builder $ticketQuery) use ($likeTerm) {
            $ticketQuery
                ->whereRaw('LOWER(title) LIKE ?', [$likeTerm])
                ->orWhereRaw('LOWER(ticket_number) LIKE ?', [$likeTerm])
                ->orWhereHas('user.studentOrganization', fn(Builder $orgQuery) => $orgQuery->whereRaw('LOWER(org_name) LIKE ?', [$likeTerm]));
        });
    }

    protected function applyPriorityFilter(Builder $query, string $priority): void
    {
        $query->whereHas('ticket', function (Builder $ticketQuery) use ($priority) {
            if ($priority === 'high') {
                $ticketQuery->where('total_participants', '>=', 200);

                return;
            }

            if ($priority === 'medium') {
                $ticketQuery->whereBetween('total_participants', [100, 199]);

                return;
            }

            if ($priority === 'low') {
                $ticketQuery->where('total_participants', '<', 100);
            }
        });
    }

    protected function transformApproval(Office_Approval $approval): array
    {
        $ticket = $approval->ticket;

        $eventDate = $this->parseDate($ticket?->getAttribute('date-from'));
        $dueDate = $this->parseDate($ticket?->getAttribute('date-to'));

        $requirements = collect(preg_split('/[\,\n]+/', (string) ($ticket?->special_requirements ?? '')))
            ->map(fn(string $item) => trim($item))
            ->filter()
            ->values()
            ->all();

        $priority = $this->resolvePriority($ticket?->total_participants);

        $statusDefinitions = $this->statusDefinitions();
        $status = $approval->decision ?? 'pending';
        $statusLabel = $statusDefinitions[$status]['label'] ?? ucfirst($status);

        return [
            'id' => $approval->id,
            'ticket_id' => $ticket?->ticket_id,
            'ticket_number' => $ticket?->ticket_number ?? 'N/A',
            'event_name' => $ticket?->title ?? 'N/A',
            'organization' => $ticket?->user?->studentOrganization?->org_name
                ?? $ticket?->user?->name
                ?? 'N/A',
            'request_type' => $ticket?->eventType?->type_name ?? 'N/A',
            'event_date' => $eventDate?->format('M d, Y') ?? 'N/A',
            'priority' => $priority['key'],
            'priority_label' => $priority['label'],
            'status' => $status,
            'status_label' => $statusLabel,
            'description' => $ticket?->description ?? 'No description provided.',
            'requirements' => $requirements,
            'submitted_date' => optional($ticket?->created_at)->format('M d, Y') ?? '—',
            'due_date' => $dueDate?->format('M d, Y') ?? '—',
            'remarks' => $approval->remarks ?? null,
            'participants' => $ticket?->total_participants,
            // include timestamp for client-side sorting when searching
            'updated_at_ts' => $approval->updated_at?->timestamp ?? 0,
        ];
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function resolvePriority(?int $totalParticipants): array
    {
        if ($totalParticipants === null) {
            return ['key' => 'low', 'label' => 'Low Priority'];
        }

        if ($totalParticipants >= 200) {
            return ['key' => 'high', 'label' => 'High Priority'];
        }

        if ($totalParticipants >= 100) {
            return ['key' => 'medium', 'label' => 'Medium Priority'];
        }

        return ['key' => 'low', 'label' => 'Low Priority'];
    }

    public function confirmAction(int $approvalId, string $action)
    {
    // reset any previous confirmation input/errors and open modal
    $this->resetValidation();
    $this->confirmationInput = '';
    $this->selectedApprovalId = $approvalId;
    $this->actionType = $action;
    $this->showConfirmationModal = true;
    }

    public function cancelConfirmation()
    {
        // reset modal state and confirmation input
        $this->reset(['showConfirmationModal', 'selectedApprovalId', 'actionType', 'confirmationInput']);
        $this->resetValidation();
    }

    /**
     * Apply filters triggered by the UI 'Okay' button.
     * Using wire:model.defer on the inputs ensures values are sent when this
     * action is called.
     */
    public function applyFilters(): void
    {
        $this->search = trim((string) $this->search);
        // clear any selected bulk requests when user runs a new search
        $this->selectedRequests = [];
        // Livewire will re-render the component after this action automatically
    }

    public function performAction()
    {
    if (! $this->selectedApprovalId) {
        return;
    }

    $approval = \App\Models\Office_Approval::find($this->selectedApprovalId);
    if (! $approval) {
        return;
    }

    // Figure out the required confirmation word depending on action
    $requiredWord = null;
    if ($this->actionType === 'approve') {
        $requiredWord = 'approve';
    } elseif ($this->actionType === 'reject') {
        $requiredWord = 'reject';
    }

    if ($requiredWord) {
        if (strtolower(trim($this->confirmationInput)) !== $requiredWord) {
            $this->addError('confirmationInput', 'Type "' . $requiredWord . '" to proceed.');
            return;
        }
    }

    // Determine final decision and persist
    $decision = $this->actionType === 'approve'
        ? 'approved'
        : ($this->actionType === 'reject' ? 'rejected' : $this->actionType);

    $approval->decision = $decision;
    $approval->updated_at = now();
    $approval->save();
    // trigger component refresh so UI reflects updated status immediately
    // Livewire v3 provides a server-side dispatch() for events
    $this->dispatch('refreshApprovals');

    // Reset modal state and show a clear success message using the persisted decision
    $this->reset(['showConfirmationModal', 'selectedApprovalId', 'actionType', 'confirmationInput']);
    $this->resetValidation();
    session()->flash('message', 'Request ' . $decision . ' successfully.');
    }
}
