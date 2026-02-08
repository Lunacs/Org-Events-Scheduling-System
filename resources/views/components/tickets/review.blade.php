@props(['ticket', 'allowedActions' => [], 'backRoute' => null, 'statusOverview' => null])

<div x-data="{
    showApproval: false,
    showRevision: false,
    showForward: false,
    showFinalApproval: false,
    approvalRemarks: '',
    revisionRemarks: '',
    forwardRemarks: '',
    finalApprovalRemarks: ''
}" x-on:ticket-approved.window="showApproval = false; approvalRemarks = ''"
    x-on:ticket-forwarded.window="showForward = false; forwardRemarks = ''"
    x-on:ticket-for-revision.window="showRevision = false; revisionRemarks = ''"
    x-on:ticket-final-approved.window="showFinalApproval = false; finalApprovalRemarks = ''" x-cloak>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success mb-6" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error mb-6" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="alert alert-info mb-6" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="alert alert-warning mb-6" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ $backRoute }}" class="btn btn-ghost btn-sm" wire:navigate>
                            <x-mary-icon name="o-arrow-left" class="w-4 h-4" />
                            Back to Tickets
                        </a>
                    </div>
                    <h1 class="text-3xl font-bold text-base-content">Ticket Review</h1>
                    <p class="text-base-content/70 mt-1">Review event proposal and attached documents</p>
                </div>
                @php
                    $statusOverview = is_array($statusOverview) ? $statusOverview : null;
                @endphp
                <div class="flex flex-wrap items-center gap-2">
                    @php
                        $currentViewer = auth()->user();
                        $statusClasses = [
                            'received' => 'badge-info',
                            'gso_review' => 'badge-secondary',
                            'pending_osa_approval' => 'badge-warning',
                            'amended' => 'badge-info',
                            'approved' => 'badge-success',
                            'for_revision' => 'badge-warning',
                        ];
                        $ticketStatusLabel = ucfirst(str_replace('_', ' ', $ticket->status));
                        $ticketBadgeClass = $statusClasses[$ticket->status] ?? 'badge-neutral';
                        $ticketTextClass =
                            $ticketBadgeClass === 'badge-warning'
                                ? 'text-neutral-900 dark:text-neutral-900'
                                : 'text-white';
                        $officeStatusLabel = $statusOverview['status_label'] ?? null;
                        $officeBadgeClass = $statusOverview['status_badge'] ?? null;
                        $officeName = $statusOverview['office_name'] ?? null;
                        $officeLabel = $officeName
                            ? \Illuminate\Support\Str::headline($officeName) . ' Decision: '
                            : 'Office Decision: ';
                        $showTicketStatusBadge = !($currentViewer && $currentViewer->isGSO());
                    @endphp
                    @if ($showTicketStatusBadge)
                        <span class="badge {{ $ticketBadgeClass }} {{ $ticketTextClass }}">Ticket:
                            {{ $ticketStatusLabel }}</span>
                    @endif
                    @if ($officeStatusLabel)
                        @php
                            $resolvedOfficeBadge = $officeBadgeClass ?? 'badge-warning';
                            $resolvedOfficeTextClass =
                                $resolvedOfficeBadge === 'badge-warning'
                                    ? 'text-neutral-900 dark:text-neutral-900'
                                    : 'text-white';
                        @endphp
                        <span
                            class="badge {{ $resolvedOfficeBadge }} {{ $resolvedOfficeTextClass }} text-white whitespace-normal h-auto">{{ $officeLabel }}{{ $officeStatusLabel }}</span>
                    @endif
                </div>
            </div>
            @if ($statusOverview && !empty($statusOverview['status_detail']))
                <p class="text-sm text-base-content/70 mt-3">{{ $statusOverview['status_detail'] }}</p>
            @endif
        </div>
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Ticket Details - Now using modular components --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Organization Information --}}
            <x-tickets.sections.organization-info :ticket="$ticket" />

            {{-- Event Details --}}
            <x-tickets.sections.event-details :ticket="$ticket" />

            {{-- Schedule & Venue --}}
            <x-tickets.sections.schedule-venue :ticket="$ticket" />

            {{-- Budget Information --}}
            <x-tickets.sections.budget-info :ticket="$ticket" />

            {{-- Additional Information --}}
            <x-tickets.sections.additional-info :ticket="$ticket" />

            {{-- Attachments --}}
            <x-tickets.sections.attachments-list :ticket="$ticket" />

            {{-- Comments --}}
            <livewire:components.ticket-comments :ticket="$ticket" :key="'ticket-comments-' . $ticket->ticket_id" />
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Ticket Info --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Ticket Details</h2>
                @php $userDeleted = $ticket->user?->trashed(); @endphp
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-base-content/70">Ticket Number</label>
                        <p class="text-base-content font-mono">{{ $ticket->ticket_number }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Submitted By</label>
                        <p class="text-base-content {{ $userDeleted ? 'italic text-base-content/50' : '' }}">
                            {{ $userDeleted ? 'Deleted User' : $ticket->user?->name }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Email</label>
                        <p class="text-base-content {{ $userDeleted ? 'italic text-base-content/50' : '' }}">
                            {{ $userDeleted ? 'N/A' : $ticket->user?->email }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Submitted</label>
                        <p class="text-base-content">
                            {{ $ticket->created_at ? $ticket->created_at->format('F d, Y g:i A') : 'TBD' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Last Updated</label>
                        <p class="text-base-content">
                            {{ $ticket->updated_at ? $ticket->updated_at->format('F d, Y g:i A') : 'TBD' }}</p>
                    </div>
                </div>
            </div>

            {{-- Event Status --}}
            @if ($ticket->events->isNotEmpty())
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    <h2 class="text-xl font-bold text-base-content mb-4">Event Created</h2>
                    @php
                        $event = $ticket->events->first();
                        $schedule = $event->eventSchedules->first();
                    @endphp
                    @if ($schedule)
                        <div class="space-y-3">
                            <div class="alert alert-success">
                                <div class="flex-1">
                                    <p class="font-medium">Event is scheduled!</p>
                                    <p class="text-sm mt-1">
                                        {{ $schedule->start_date ? $schedule->start_date->format('F d, Y') : 'TBD' }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-base-content/70">Venue</label>
                                <p class="text-base-content">{{ $schedule->venue ?? 'TBD' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-base-content/70">Time</label>
                                <p class="text-base-content">
                                    {{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') : 'TBD' }}
                                    -
                                    {{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') : 'TBD' }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-base-content/70">Status</label>
                                <span
                                    class="badge {{ $schedule->status === 'approved' ? 'badge-success' : 'badge-info' }} text-white">{{ ucfirst($schedule->status) }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-base-content/70">Event created but schedule is pending.</p>
                    @endif
                </div>
            @endif

            {{-- Approval History Timeline --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Approval History Timeline</h2>
                <p class="text-sm text-base-content/70 mb-6">Complete workflow progression from submission to final
                    decision</p>

                @php
                    // Use approval_history table for timeline (immutable audit trail)
                    // Reverse order to show chronological timeline (oldest first)
                    $allApprovals = $ticket->approvalHistory()->orderBy('created_at', 'asc')->get();

                    // Action badge classes (approval_history uses 'action' not 'decision')
                    $actionClasses = [
                        'approved' => 'badge-success',
                        'for_revision' => 'badge-warning',
                        'pending' => 'badge-warning',
                        'forwarded' => 'badge-info',
                        'revision_requested' => 'badge-warning',
                    ];

                    // Get workflow context for each approval based on the real workflow
                    $getWorkflowContext = function ($approval) use ($ticket) {
                        $action = $approval->action; // Use 'action' field from approval_history
                        $type = strtolower($approval->approval_type); // Use 'approval_type' field

                        if ($action === 'forwarded' && $type === 'osa') {
                            return 'Ticket forwarded to GSO for venue and logistics review. OSA approval remains pending while waiting for GSO response. GSO will provide their decision before OSA makes the final approval.';
                        }

                        if ($action === 'revision_requested' && $type === 'osa') {
                            return 'OSA requested changes. Student organization must revise and resubmit the ticket. Ticket status changed to "for_revision".';
                        }

                        if ($action === 'approved' && $type === 'osa') {
                            // Check if this is final approval after GSO review or direct approval
                            $hasOfficeApproval = $ticket->officeApprovals()->whereNotNull('office_id')->exists();
                            if ($hasOfficeApproval) {
                                return 'Final approval granted by OSA after GSO review. Event and schedule have been created and will appear on the calendar. Ticket status changed to "approved".';
                            } else {
                                return 'Direct approval granted by OSA (no GSO review needed). Event and schedule have been created and will appear on the calendar. Ticket status changed to "approved".';
                            }
                        }

                        if ($action === 'approved' && $type === 'office') {
                            return 'Office approved the request. Ticket status changed to "pending_osa_approval". OSA will now make the final decision.';
                        }

                        if ($action === 'revision_requested' && $type === 'office') {
                            return 'Office requested revision. Ticket status changed to "pending_osa_approval". OSA will review and make the final decision (can override or agree with the request).';
                        }

                        if ($action === 'pending' && $type === 'osa') {
                            return 'Initial submission received. OSA is reviewing the ticket. Ticket status set to "received".';
                        }

                        if ($action === 'pending' && $type === 'office') {
                            return 'Ticket forwarded to office. Awaiting office review and decision.';
                        }

                        return null;
                    };
                @endphp

                @if ($allApprovals->count() > 0)
                    <div class="relative">
                        {{-- Timeline Line --}}
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-base-300"></div>

                        <div class="space-y-6">
                            @foreach ($allApprovals as $index => $approval)
                                @php
                                    $action = $approval->action; // Use 'action' field from approval_history
                                    $type = strtolower($approval->approval_type); // Use 'approval_type' field
                                    $workflowContext = $getWorkflowContext($approval);

                                    // Determine dot color based on action and type
                                    $dotColor = match ($action) {
                                        'approved' => 'bg-success',
                                        'for_revision' => 'bg-warning',
                                        'forwarded' => 'bg-info',
                                        'revision_requested' => 'bg-warning',
                                        default => 'bg-warning',
                                    };

                                    // Determine icon based on action
                                    $icon = match ($action) {
                                        'approved' => 'o-check-circle',
                                        '' => 'o-x-circle',
                                        'forwarded' => 'o-arrow-right',
                                        'revision_requested' => 'o-arrow-path',
                                        default => 'o-clock',
                                    };
                                @endphp

                                <div class="relative pl-10">
                                    {{-- Timeline Dot --}}
                                    <div
                                        class="absolute left-2 top-1 w-4 h-4 rounded-full {{ $dotColor }} ring-4 ring-base-100 flex items-center justify-center">
                                        <x-mary-icon :name="$icon" class="w-2.5 h-2.5 text-white" />
                                    </div>

                                    {{-- Content Card --}}
                                    <div class="bg-base-200 rounded-lg p-4 hover:bg-base-300 transition-colors">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <p class="font-semibold text-base-content text-sm">
                                                        {{ $approval->office_name ?? 'Office of Student Affairs' }}
                                                    </p>
                                                    <span class="badge badge-xs badge-outline">
                                                        {{ strtoupper($approval->approval_type) }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-base-content/70">
                                                    {{ $approval->user->name ?? 'System' }}
                                                </p>
                                            </div>
                                            <span
                                                class="badge badge-sm {{ $actionClasses[$action] ?? 'badge-neutral' }} text-white">
                                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                                            </span>
                                        </div>

                                        {{-- Workflow Context --}}
                                        @if ($workflowContext)
                                            <div
                                                class="mt-3 p-2 bg-base-100 rounded border-l-4 {{ $action === 'approved' ? 'border-success' : ($action === '' ? 'border-warning' : ($action === 'forwarded' ? 'border-info' : 'border-warning')) }}">
                                                <p class="text-xs text-base-content/80 leading-relaxed">
                                                    {{ $workflowContext }}
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Remarks --}}
                                        @if ($approval->remarks)
                                            <div class="mt-3 pt-3 border-t border-base-300">
                                                <p class="text-xs font-medium text-base-content/70 mb-1">
                                                    Remarks:
                                                </p>
                                                <p class="text-sm text-base-content/80 whitespace-pre-line">
                                                    {{ $approval->remarks }}
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Timestamp --}}
                                        <div
                                            class="mt-3 pt-2 border-t border-base-300 flex items-center justify-between">
                                            <p class="text-xs text-base-content/50">
                                                {{ $approval->created_at->format('F d, Y g:i A') }}
                                            </p>
                                            <p class="text-xs text-base-content/50">
                                                {{ $approval->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <x-mary-icon name="o-clock" class="w-12 h-12 text-base-content/30 mx-auto mb-3" />
                        <p class="text-base-content/70 font-medium">No approval actions yet</p>
                        <p class="text-sm text-base-content/50 mt-1">This ticket is awaiting initial review by OSA</p>
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Actions</h2>

                @php
                    $overviewData = is_array($statusOverview ?? null) ? $statusOverview : [];
                    $hasOfficeActions =
                        in_array('approve', $allowedActions, true) || in_array('reject', $allowedActions, true);
                    $currentUser = auth()->user();

                    $targetOfficeId = $overviewData['office_id'] ?? null;
                    $officeApprovalRecord = null;

                    if ($targetOfficeId !== null) {
                        $officeApprovalRecord = $ticket->officeApprovals->firstWhere(
                            'office_id',
                            (int) $targetOfficeId,
                        );
                    }

                    if (!$officeApprovalRecord && $currentUser && $currentUser->isGSO()) {
                        $fallbackOfficeId = $currentUser->office_id;

                        if ($fallbackOfficeId) {
                            $officeApprovalRecord = $ticket->officeApprovals->firstWhere(
                                'office_id',
                                (int) $fallbackOfficeId,
                            );
                        }
                    }

                    $officeDecisionStatus = $officeApprovalRecord
                        ? \Illuminate\Support\Str::of($officeApprovalRecord->decision)->lower()->toString()
                        : (isset($overviewData['status'])
                            ? \Illuminate\Support\Str::of($overviewData['status'])->lower()->toString()
                            : null);

                    $officeDecisionPending = false;

                    if ($hasOfficeActions) {
                        if ($officeApprovalRecord) {
                            $officeDecisionPending = strcasecmp($officeApprovalRecord->decision ?? '', 'pending') === 0;
                        } elseif ($officeDecisionStatus === 'pending') {
                            $officeDecisionPending = true;
                        }
                    }

                    $canPerformOfficeDecision =
                        $hasOfficeActions &&
                        $currentUser &&
                        $currentUser->isGSO() &&
                        ($currentUser->can('approve', $ticket) || $currentUser->can('reject', $ticket));

                    $shouldRenderOfficeActions = $officeDecisionPending && $canPerformOfficeDecision;

                    $resolvedGsoApproval = $officeApprovalRecord;

                    if (!$resolvedGsoApproval && $currentUser && $currentUser->isGSO() && $currentUser->office_id) {
                        $resolvedGsoApproval = $ticket->officeApprovals->firstWhere(
                            'office_id',
                            (int) $currentUser->office_id,
                        );
                    }

                    $resolvedDecision = $resolvedGsoApproval?->decision;

                    $officeDecisionDetails = null;

                    if ($resolvedGsoApproval && $resolvedDecision && strcasecmp($resolvedDecision, 'pending') !== 0) {
                        $decisionKey = \Illuminate\Support\Str::of($resolvedDecision)->lower()->toString();

                        $officeDecisionDetails = [
                            'status' => $decisionKey,
                            'message' =>
                                $decisionKey === 'approved'
                                    ? 'This ticket has been approved.'
                                    : 'This ticket has been put for revision.',
                            'wrapper' =>
                                $decisionKey === 'approved'
                                    ? 'flex items-start gap-3 rounded-2xl bg-info/10 border border-info/30 px-4 py-3 text-info'
                                    : 'flex items-start gap-3 rounded-2xl bg-warning/10 border border-warning/30 px-4 py-3 text-warning',
                            'icon' => $decisionKey === 'approved' ? 'o-check-circle' : 'o-x-circle',
                        ];
                    }
                @endphp

                @if ($ticket->status === 'pending_osa_approval')
                    {{-- Pending OSA decision; GSO may still need to act --}}
                    @if ($shouldRenderOfficeActions)
                        <div class="space-y-3">
                            @if (in_array('approve', $allowedActions))
                                @can('approve', $ticket)
                                    <button
                                        class="btn btn-success w-full text-base-200 dark:text-white flex justify-between"
                                        @click="showApproval = true">
                                        Approve Ticket
                                    </button>
                                @endcan
                            @endif
                        </div>
                    @else
                        @if ($officeDecisionDetails)
                            <div class="{{ $officeDecisionDetails['wrapper'] }}">
                                <x-mary-icon :name="$officeDecisionDetails['icon']" class="w-5 h-5 shrink-0" />
                                <p class="font-medium leading-tight">{{ $officeDecisionDetails['message'] }}</p>
                            </div>
                        @endif

                        <div class="space-y-3">
                            @if (in_array('final_approve', $allowedActions))
                                @can('finalApprove', $ticket)
                                    <button
                                        class="btn btn-success w-full text-base-200 dark:text-white flex justify-between"
                                        @click="showFinalApproval = true">
                                        Final Approval
                                    </button>
                                @endcan
                            @endif

                            @if (in_array('for_revision', $allowedActions))
                                @can('requestRevision', $ticket)
                                    <button
                                        class="btn btn-warning w-full text-base-200 dark:text-white flex justify-between"
                                        @click="showRevision = true">
                                        Request Revision
                                    </button>
                                @endcan
                            @endif
                        </div>
                    @endif
                @elseif ($ticket->status === 'gso_review' || ($officeDecisionPending && $canPerformOfficeDecision))
                    {{-- GSO Review Actions --}}
                    <div class="space-y-3">
                        @if (in_array('approve', $allowedActions))
                            @can('approve', $ticket)
                                <button class="btn btn-success w-full text-base-200 dark:text-white flex justify-between"
                                    @click="showApproval = true">
                                    Approve Ticket
                                </button>
                            @endcan
                        @endif

                        @if (in_array('for_revision', $allowedActions))
                            @can('requestRevision', $ticket)
                                <button class="btn btn-warning w-full text-base-200 dark:text-white flex justify-between"
                                    @click="showRevision = true">
                                    Request Revision
                                </button>
                            @endcan
                        @endif
                    </div>
                @elseif (in_array($ticket->status, ['received', 'amended']))
                    {{-- Initial Review Actions --}}
                    <div class="space-y-3">
                        @if (in_array('approve', $allowedActions))
                            @can('approve', $ticket)
                                <button class="btn btn-success w-full text-base-200 dark:text-white flex justify-between"
                                    @click="showApproval = true">
                                    Approve Ticket
                                </button>
                            @endcan
                        @endif

                        @if (in_array('for_revision', $allowedActions))
                            @can('requestRevision', $ticket)
                                <button class="btn btn-warning w-full text-base-200 dark:text-white flex justify-between"
                                    @click="showRevision = true">
                                    Request Revision
                                </button>
                            @endcan
                        @endif

                        @if (in_array('forward', $allowedActions))
                            @can('forwardToGso', $ticket)
                                <button class="btn btn-info w-full text-base-200 dark:text-white flex justify-between"
                                    @click="showForward = true">
                                    Forward to GSO
                                </button>
                            @endcan
                        @endif
                    </div>
                @else
                    @if ($officeDecisionDetails)
                        <div class="{{ $officeDecisionDetails['wrapper'] }}">
                            <x-mary-icon :name="$officeDecisionDetails['icon']" class="w-5 h-5 shrink-0" />
                            <p class="font-medium leading-tight">{{ $officeDecisionDetails['message'] }}</p>
                        </div>
                    @else
                        @php
                            $resolvedStatus = \Illuminate\Support\Str::of($ticket->status)->lower()->toString();
                            $statusConfig = [
                                'approved' => [
                                    'wrapper' =>
                                        'flex items-start gap-3 rounded-2xl bg-info/10 border border-info/30 px-4 py-3 text-info',
                                    'icon' => 'o-check-circle',
                                    'label' => 'This ticket has been approved.',
                                ],
                                'for_revision' => [
                                    'wrapper' =>
                                        'flex items-start gap-3 rounded-2xl bg-warning/10 border border-warning/30 px-4 py-3 text-warning',
                                    'icon' => 'o-arrow-path',
                                    'label' => 'This ticket has been put for revision.',
                                ],
                                'gso_review' => [
                                    'wrapper' =>
                                        'flex items-start gap-3 rounded-2xl bg-secondary/10 border border-secondary/30 px-4 py-3 text-secondary',
                                    'icon' => 'o-eye',
                                    'label' => 'Ticket is currently under GSO review.',
                                ],
                            ];

                            $statusDisplay = $statusConfig[$resolvedStatus] ?? null;
                        @endphp

                        @if ($statusDisplay)
                            <div class="{{ $statusDisplay['wrapper'] }}">
                                <x-mary-icon :name="$statusDisplay['icon']" class="w-5 h-5 shrink-0" />
                                <span class="font-medium leading-tight">{{ $statusDisplay['label'] }}</span>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <x-mary-icon name="o-information-circle" class="w-5 h-5" />
                                <span>No actions available for current ticket status.</span>
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Approval Modal (Alpine) --}}
    <div x-show="showApproval" x-transition class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-base-300/60 backdrop-blur" @click="showApproval=false"></div>
        <div class="relative bg-base-100 rounded-box shadow-xl w-full max-w-xl p-6">
            <h3 class="text-lg font-bold mb-4">Confirm Ticket Approval</h3>
            <div class="space-y-4">
                <div class="alert alert-success">
                    <x-mary-icon name="o-check-circle" class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold">You are about to approve this ticket</h3>
                        <p class="text-sm">This action will create an event and schedule it on the calendar.</p>
                    </div>
                </div>
                <label class="text-sm font-medium">Approval Remarks</label>
                <textarea x-model="approvalRemarks" class="textarea textarea-bordered w-full" rows="4"
                    placeholder="Enter your remarks for approving this ticket..."></textarea>
                <p class="text-xs text-base-content/60">Provide a brief explanation for this approval</p>
                @error('approvalRemarks')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <x-mary-button label="Cancel" class="btn" @click="showApproval=false" />
                <x-mary-button label="Confirm Approval" class="btn-success text-neutral-content"
                    wire:click="approveTicket" spinner="approveTicket"
                    x-bind:disabled="approvalRemarks.trim().length < 3"
                    @click="$wire.set('approvalRemarks', approvalRemarks)" />
            </div>
        </div>
    </div>

    {{-- For Revision Modal (unified) --}}
    <div x-show="showRevision" x-transition class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-base-300/60 backdrop-blur" @click="showRevision=false"></div>
        <div class="relative bg-base-100 rounded-box shadow-xl w-full max-w-xl p-6">
            <h3 class="text-lg font-bold mb-4">Request Ticket Revision</h3>
            <div class="space-y-4">
                <div class="alert alert-warning">
                    <x-mary-icon name="o-arrow-path" class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold">Request changes to this ticket</h3>
                        <p class="text-sm">The student organization will need to revise and resubmit.</p>
                    </div>
                </div>
                <label class="text-sm font-medium">Revision Instructions</label>
                <textarea x-model="revisionRemarks" class="textarea textarea-bordered w-full" rows="5"
                    placeholder="Clearly explain what needs to be changed or added..."></textarea>
                <p class="text-xs text-base-content/60">Be specific about what needs to be revised (minimum 10
                    characters)</p>
                @error('revisionRemarks')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <x-mary-button label="Cancel" class="btn" @click="showRevision=false" />
                <x-mary-button label="Request Revision" class="btn-warning text-neutral-content"
                    wire:click="forRevision" spinner="forRevision"
                    x-bind:disabled="revisionRemarks.trim().length < 10"
                    @click="$wire.set('revisionRemarks', revisionRemarks)" />
            </div>
        </div>
    </div>


    {{-- Forward to GSO Modal (Alpine) --}}
    <div x-show="showForward" x-transition class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-base-300/60 backdrop-blur" @click="showForward=false"></div>
        <div class="relative bg-base-100 rounded-box shadow-xl w-full max-w-xl p-6">
            <h3 class="text-lg font-bold mb-4">Forward to GSO</h3>
            <div class="space-y-4">
                <div class="alert alert-info">
                    <x-mary-icon name="o-arrow-right" class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold">Forward this ticket to GSO</h3>
                        <p class="text-sm">GSO will review and provide their decision. You'll make the final approval.
                        </p>
                    </div>
                </div>
                <label class="text-sm font-medium">Forwarding Remarks</label>
                <textarea x-model="forwardRemarks" class="textarea textarea-bordered w-full" rows="4"
                    placeholder="Enter remarks for GSO..."></textarea>
                <p class="text-xs text-base-content/60">Explain why this needs GSO review or what specific approval is
                    needed</p>
                @error('forwardRemarks')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <x-mary-button label="Cancel" class="btn" @click="showForward=false" />
                <x-mary-button label="Forward to GSO" class="btn-info text-neutral-content" wire:click="forwardToGso"
                    spinner="forwardToGso" x-bind:disabled="forwardRemarks.trim().length < 3"
                    @click="$wire.set('forwardRemarks', forwardRemarks)" />
            </div>
        </div>
    </div>

    {{-- Final Approval Modal (Alpine) --}}
    <div x-show="showFinalApproval" x-transition class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-base-300/60 backdrop-blur" @click="showFinalApproval=false"></div>
        <div class="relative bg-base-100 rounded-box shadow-xl w-full max-w-xl p-6">
            <h3 class="text-lg font-bold mb-4">Final Approval</h3>
            <div class="space-y-4">
                <div class="alert alert-success">
                    <x-mary-icon name="o-check-badge" class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold">Final approval after GSO review</h3>
                        <p class="text-sm">This will create the event and schedule it on the calendar.</p>
                    </div>
                </div>
                <label class="text-sm font-medium">Final Approval Remarks</label>
                <textarea x-model="finalApprovalRemarks" class="textarea textarea-bordered w-full" rows="4"
                    placeholder="Enter your final approval remarks..."></textarea>
                <p class="text-xs text-base-content/60">Document your final decision after considering GSO's input</p>
                @error('finalApprovalRemarks')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <x-mary-button label="Cancel" class="btn" @click="showFinalApproval=false" />
                <x-mary-button label="Confirm Final Approval" class="btn-success text-neutral-content"
                    wire:click="finalApproval" spinner="finalApproval"
                    x-bind:disabled="finalApprovalRemarks.trim().length < 3"
                    @click="$wire.set('finalApprovalRemarks', finalApprovalRemarks)" />
            </div>
        </div>
    </div>

    {{-- Add JavaScript for handling Livewire events --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('ticket-approved', () => {
                // Dispatch Alpine event to close modal
                window.dispatchEvent(new CustomEvent('ticket-approved'));
            });

            Livewire.on('ticket-forwarded', () => {
                // Dispatch Alpine event to close modal
                window.dispatchEvent(new CustomEvent('ticket-forwarded'));
            });

            Livewire.on('ticket-for-revision', () => {
                window.dispatchEvent(new CustomEvent('ticket-for-revision'));
            });

            Livewire.on('ticket-final-approved', () => {
                // Dispatch Alpine event to close modal
                window.dispatchEvent(new CustomEvent('ticket-final-approved'));
            });

            Livewire.on('comment-added', () => {
                // Dispatch Alpine event for client-side avatar initialization
                window.dispatchEvent(new CustomEvent('comment-added'));
            });

            Livewire.on('open-attachment-preview', ({
                url
            }) => {
                if (url) {
                    window.open(url, '_blank');
                }
            });

            // Add this download listener
            Livewire.on('download-attachment', ({
                url
            }) => {
                if (url) {
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = '';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });

            // Re-initialize avatars when returning from a new tab or refocusing
            window.addEventListener('pageshow', () => {
                if (window.AvatarHelper) window.AvatarHelper.initAvatars(true);
            });
            window.addEventListener('focus', () => {
                if (window.AvatarHelper) window.AvatarHelper.initAvatars(false);
            });
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && window.AvatarHelper) {
                    window.AvatarHelper.initAvatars(false);
                }
            });
        });
    </script>
</div>
