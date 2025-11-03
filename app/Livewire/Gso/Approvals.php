<?php

namespace App\Livewire\Gso;

use App\Livewire\Gso\Concerns\ResolvesOfficeContext;
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
    use ResolvesOfficeContext;
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

    public bool $showBulkApprovalModal = false;
    public string $bulkConfirmationInput = '';

    protected $listeners = [
        'refreshApprovals' => '$refresh',
    ];

    public function render()
    {
    $user = Auth::user();
    $officeId = $this->resolveOfficeId($user);

        $baseQuery = $this->baseQuery($officeId);

        $pendingQuery = (clone $baseQuery)->where('decision', 'pending');

        $pendingApprovalsForStats = (clone $pendingQuery)->get();

        $stats = [
            'pending' => $pendingApprovalsForStats->count(),
            'todayApproved' => (clone $baseQuery)
                ->where('decision', 'approved')
                ->whereDate('updated_at', Carbon::today())
                ->count(),
            'urgent' => $pendingApprovalsForStats
                ->filter(fn(Office_Approval $approval) => $this->determinePriorityKey($this->extractEventDate($approval)) === 'high')
                ->count(),
        ];

        $decision = $this->normalizeStatusFilter($this->statusFilter);

        $approvalsQuery = clone $baseQuery;

        if ($decision !== 'all') {
            $approvalsQuery->where('decision', $decision);
        }

        $approvalsCollection = $approvalsQuery
            ->when($this->search !== '', fn(Builder $query) => $this->applySearchFilter($query, $this->search))
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn(Office_Approval $approval) => $this->transformApproval($approval));

        if ($this->priorityFilter !== 'all') {
            $approvalsCollection = $approvalsCollection->filter(
                fn(array $approval) => ($approval['priority'] ?? 'low') === $this->priorityFilter
            );
        }

        $approvals = $approvalsCollection->values();

        if ($this->search !== '') {
            $term = Str::lower(trim($this->search));

            $approvals = $approvals->sort(function ($a, $b) use ($term) {
                $aEvent = strtolower($a['event_name'] ?? '');
                $bEvent = strtolower($b['event_name'] ?? '');
                $aOrg = strtolower($a['organization'] ?? '');
                $bOrg = strtolower($b['organization'] ?? '');

                $score = function ($event, $org) use ($term) {
                    if ($term !== '' && strpos($event, $term) !== false) {
                        return 1;
                    }
                    if ($term !== '' && strpos($org, $term) !== false) {
                        return 2;
                    }
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

    protected function baseQuery(int $officeId): Builder
    {
        return Office_Approval::query()
            ->with([
                'ticket.eventType',
                'ticket.user.studentOrganization',
            ])
            ->where('office_id', $officeId);
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

    protected function transformApproval(Office_Approval $approval): array
    {
        $ticket = $approval->ticket;

        $eventDate = $this->parseDate($ticket?->getAttribute('date_from'));
        $dueDate = $this->parseDate($ticket?->getAttribute('date_to'));

        $requirements = collect(preg_split('/[\,\n]+/', (string) ($ticket?->special_requirements ?? '')))
            ->map(fn(string $item) => trim($item))
            ->filter()
            ->values()
            ->all();

        $priority = $this->resolvePriority($eventDate);

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
            'priority_days_until' => $priority['days_until'],
            'status' => $status,
            'status_label' => $statusLabel,
            'office_id' => $approval->office_id ?? $this->resolveOfficeId(Auth::user()),
            'description' => $ticket?->description ?? 'No description provided.',
            'requirements' => $requirements,
            'submitted_date' => optional($ticket?->created_at)->format('M d, Y') ?? '—',
            'due_date' => $dueDate?->format('M d, Y') ?? '—',
            'remarks' => $approval->remarks ?? null,
            'participants' => $ticket?->total_participants,
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

    protected function resolvePriority(?Carbon $eventDate): array
    {
        $priorityKey = $this->determinePriorityKey($eventDate);

        $labels = [
            'high' => 'High Priority',
            'medium' => 'Medium Priority',
            'low' => 'Low Priority',
        ];

        return [
            'key' => $priorityKey,
            'label' => $labels[$priorityKey] ?? 'Low Priority',
            'days_until' => $this->daysUntilEvent($eventDate),
        ];
    }

    protected function determinePriorityKey(?Carbon $eventDate): string
    {
        $daysUntil = $this->daysUntilEvent($eventDate);

        if ($daysUntil === null) {
            return 'low';
        }

        if ($daysUntil <= 3) {
            return 'high';
        }

        if ($daysUntil <= 7) {
            return 'medium';
        }

        return 'low';
    }

    protected function daysUntilEvent(?Carbon $eventDate): ?int
    {
        if (! $eventDate) {
            return null;
        }

        return Carbon::now()->startOfDay()->diffInDays($eventDate->copy()->startOfDay(), false);
    }

    protected function extractEventDate(Office_Approval $approval): ?Carbon
    {
        $ticket = $approval->ticket;

        if (! $ticket) {
            return null;
        }

        return $this->parseDate($ticket->getAttribute('date_from'));
    }

    public function confirmAction(int $approvalId, string $action)
    {
        $this->resetValidation();
        $this->confirmationInput = '';
        $this->selectedApprovalId = $approvalId;
        $this->actionType = $action;
        $this->showConfirmationModal = true;
    }

    public function cancelConfirmation()
    {
        $this->reset(['showConfirmationModal', 'selectedApprovalId', 'actionType', 'confirmationInput']);
        $this->resetValidation();
    }

    public function updatingSearch()
    {
        $this->selectedRequests = [];
    }

    public function updatingPriorityFilter()
    {
        $this->selectedRequests = [];
    }

    public function bulkApprove()
    {
        if (empty($this->selectedRequests)) {
            return;
        }

        $this->bulkConfirmationInput = '';
        $this->showBulkApprovalModal = true;
    }

    public function cancelBulkApproval()
    {
        $this->showBulkApprovalModal = false;
        $this->bulkConfirmationInput = '';
        $this->resetValidation();
    }

    public function performBulkApproval()
    {
        // Validate confirmation input
        if (strtolower(trim($this->bulkConfirmationInput)) !== 'approve') {
            $this->addError('bulkConfirmationInput', 'Type "approve" to proceed.');
            return;
        }

        $approvedCount = 0;
        $gsoUser = \Illuminate\Support\Facades\Auth::user();

        foreach ($this->selectedRequests as $approvalId) {
            $approval = Office_Approval::find($approvalId);
            
            if ($approval && $approval->decision === 'pending') {
                $approval->decision = 'approved';
                $approval->user_id = $gsoUser->user_id;
                $approval->updated_at = now();
                $approval->save();

                // Create a copy back to OSA approvals with pending status
                \App\Models\OSA_Approval::create([
                    'ticket_id' => $approval->ticket_id,
                    'user_id' => $gsoUser->user_id,
                    'decision' => 'pending',
                    'remarks' => 'Ticket approved by GSO - awaiting final OSA review',
                ]);

                $approvedCount++;
            }
        }

        $this->selectedRequests = [];
        $this->showBulkApprovalModal = false;
        $this->bulkConfirmationInput = '';
        $this->resetValidation();
        
        $this->dispatch('refreshApprovals');
        
        session()->flash('message', "{$approvedCount} request(s) approved successfully.");
    }

    public function performAction()
    {
        if (! $this->selectedApprovalId) {
            return;
        }

        $approval = Office_Approval::find($this->selectedApprovalId);
        if (! $approval) {
            return;
        }

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

        $decision = $this->actionType === 'approve'
            ? 'approved'
            : ($this->actionType === 'reject' ? 'rejected' : $this->actionType);

        $approval->decision = $decision;
        $approval->updated_at = now();
        $approval->save();

        // If approved, create a copy back to OSA approvals with pending status
        if ($decision === 'approved') {
            \App\Models\OSA_Approval::create([
                'ticket_id' => $approval->ticket_id,
                'user_id' => auth()->id(),
                'decision' => 'pending',
                'remarks' => 'Ticket approved by GSO - awaiting final OSA review',
            ]);
        }

        $this->dispatch('refreshApprovals');

        $this->reset(['showConfirmationModal', 'selectedApprovalId', 'actionType', 'confirmationInput']);
        $this->resetValidation();
        session()->flash('message', 'Request ' . $decision . ' successfully.');
    }
}
