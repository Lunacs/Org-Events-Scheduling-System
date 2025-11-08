@props(['ticket', 'allowedActions' => [], 'backRoute' => null, 'statusOverview' => null])

<div x-data="{
    showApproval: false,
    showRejection: false,
    showRevision: false,
    showForward: false,
    showFinalApproval: false,
    showFinalRejection: false,
    approvalRemarks: '',
    rejectionRemarks: '',
    revisionRemarks: '',
    forwardRemarks: '',
    finalApprovalRemarks: '',
    finalRejectionRemarks: ''
}" x-on:ticket-approved.window="showApproval = false; approvalRemarks = ''"
    x-on:ticket-forwarded.window="showForward = false; forwardRemarks = ''"
    x-on:ticket-revision-requested.window="showRevision = false; revisionRemarks = ''"
    x-on:ticket-rejected.window="showRejection = false; rejectionRemarks = ''"
    x-on:ticket-final-approved.window="showFinalApproval = false; finalApprovalRemarks = ''"
    x-on:ticket-final-rejected.window="showFinalRejection = false; finalRejectionRemarks = ''" x-cloak>
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
                        @php
                            $backUrl =
                                $backRoute ??
                                (auth()->user()->isGSO()
                                    ? route('gso.ticket-review')
                                    : route('osa.ticket-review.index'));
                        @endphp
                        <a href="{{ $backUrl }}" class="btn btn-ghost btn-sm" wire:navigate>
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
                            'for_rescheduling' => 'badge-warning',
                            'rescheduled' => 'badge-success',
                            'needs_revision' => 'badge-warning',
                            'amended' => 'badge-info',
                            'approved' => 'badge-success',
                            'rejected' => 'badge-error',
                        ];
                        $ticketStatusLabel = ucfirst(str_replace('_', ' ', $ticket->status));
                        $ticketBadgeClass = $statusClasses[$ticket->status] ?? 'badge-neutral';
                        $ticketTextClass = $ticketBadgeClass === 'badge-warning'
                            ? 'text-neutral-900 dark:text-neutral-900'
                            : 'text-white';
                        $officeStatusLabel = $statusOverview['status_label'] ?? null;
                        $officeBadgeClass = $statusOverview['status_badge'] ?? null;
                        $officeName = $statusOverview['office_name'] ?? null;
                        $officeLabel = $officeName
                            ? (\Illuminate\Support\Str::headline($officeName) . ' Decision: ')
                            : 'Office Decision: ';
                        $showTicketStatusBadge = ! ($currentViewer && $currentViewer->isGSO());
                    @endphp
                    @if ($showTicketStatusBadge)
                        <span class="badge {{ $ticketBadgeClass }} {{ $ticketTextClass }}">Ticket: {{ $ticketStatusLabel }}</span>
                    @endif
                    @if ($officeStatusLabel)
                        @php
                            $resolvedOfficeBadge = $officeBadgeClass ?? 'badge-warning';
                            $resolvedOfficeTextClass = $resolvedOfficeBadge === 'badge-warning'
                                ? 'text-neutral-900 dark:text-neutral-900'
                                : 'text-white';
                        @endphp
                        <span class="badge {{ $resolvedOfficeBadge }} {{ $resolvedOfficeTextClass }}">{{ $officeLabel }}{{ $officeStatusLabel }}</span>
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
        {{-- Ticket Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Organization Information --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Organization Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-base-content/70">Organization Name</label>
                        <p class="text-base-content font-medium">
                            {{ $ticket->user->studentOrganization->org_name ?? 'No Organization' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Organization Course</label>
                        <p class="text-base-content">
                            {{ $ticket->user->studentOrganization->course->course_name ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Name of Proponent</label>
                        <p class="text-base-content">{{ $ticket->user->name }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Proponent Position</label>
                        <p class="text-base-content">{{ $ticket->user->position->position_name ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Contact Email</label>
                        <p class="text-base-content">{{ $ticket->user->email }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Proponent Contact</label>
                        <p class="text-base-content">{{ $ticket->proponent_contact ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Organization Adviser</label>
                        <p class="text-base-content">
                            {{ $ticket->user->studentOrganization->adviser_name ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Adviser Contact</label>
                        <p class="text-base-content">{{ $ticket->adviser_contact ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            {{-- Event Details --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Event Details</h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-base-content/70">Event Title</label>
                        <p class="text-base-content font-medium text-lg">{{ $ticket->title }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Event Type</label>
                        <p class="text-base-content">{{ $ticket->eventType->type_name ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Event Description</label>
                        <p class="text-base-content whitespace-pre-wrap">{{ $ticket->description }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium text-base-content/70">PLV Participants</label>
                            <p class="text-base-content font-semibold">{{ $ticket->plv_participants ?? 0 }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-base-content/70">External Participants</label>
                            <p class="text-base-content font-semibold">{{ $ticket->external_participants ?? 0 }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-base-content/70">Total Expected Participants</label>
                            <p class="text-primary font-semibold">
                                {{ $ticket->total_participants ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Schedule & Venue --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Schedule & Venue</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-base-content/70">Event Start Date</label>
                        <p class="text-base-content">
                            {{ $ticket->date_from ? \Carbon\Carbon::parse($ticket->date_from)->format('F d, Y') : 'TBD' }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Event End Date</label>
                        <p class="text-base-content">
                            {{ $ticket->date_to ? \Carbon\Carbon::parse($ticket->date_to)->format('F d, Y') : 'TBD' }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Event Start Time</label>
                        <p class="text-base-content">
                            {{ $ticket->time_from ? \Carbon\Carbon::parse($ticket->time_from)->format('g:i A') : 'TBD' }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Event End Time</label>
                        <p class="text-base-content">
                            {{ $ticket->time_to ? \Carbon\Carbon::parse($ticket->time_to)->format('g:i A') : 'TBD' }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Preferred Venue</label>
                        <p class="text-base-content">{{ $ticket->venue_requested ?? 'TBD' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Alternative Venue</label>
                        <p class="text-base-content">{{ $ticket->alternate_venue ?? 'None' }}</p>
                    </div>
                </div>

                @if ($ticket->special_requirements)
                    <div class="mt-4">
                        <label class="text-sm font-medium text-base-content/70">Special Requirements</label>
                        <p class="text-base-content whitespace-pre-wrap bg-base-200 p-3 rounded">
                            {{ $ticket->special_requirements }}</p>
                    </div>
                @endif

                {{-- Off-Campus Activity Details --}}
                @if ($ticket->oc_accommodation || $ticket->oc_tsp)
                    <div class="mt-4 p-4 bg-warning/10 border-l-4 border-warning rounded">
                        <h3 class="font-semibold text-base-content mb-3">Off-Campus Activity Details</h3>

                        @if ($ticket->oc_accommodation)
                            <div class="mb-3">
                                <label class="text-sm font-medium text-base-content/70">Accommodation
                                    Provider</label>
                                <p class="text-base-content">{{ $ticket->oc_accommodation }}</p>
                            </div>
                        @endif

                        @if ($ticket->oc_tsp)
                            <div class="mb-2">
                                <label class="text-sm font-medium text-base-content/70">Transportation
                                    Service
                                    Provider</label>
                                <p class="text-base-content">{{ ucfirst($ticket->oc_tsp) }}</p>
                            </div>

                            @if ($ticket->oc_tsp === 'outsourced')
                                <div class="grid grid-cols-2 gap-3 mt-2">
                                    <div>
                                        <label class="text-xs font-medium text-base-content/70">Driver
                                            Name</label>
                                        <p class="text-sm text-base-content">
                                            {{ $ticket->oc_driver_name ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-base-content/70">Contact
                                            Number</label>
                                        <p class="text-sm text-base-content">
                                            {{ $ticket->oc_driver_contact_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-base-content/70">Vehicle
                                            Type</label>
                                        <p class="text-sm text-base-content">
                                            {{ $ticket->oc_vehicle_type ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-base-content/70">Plate
                                            Number</label>
                                        <p class="text-sm text-base-content">
                                            {{ $ticket->oc_vehicle_plate_number ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                @endif
            </div>

            {{-- Budget Information --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Budget Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-base-content/70">Estimated Total
                            Budget</label>
                        <p class="text-base-content font-semibold text-lg">
                            ₱{{ number_format($ticket->estimated_budget ?? 0, 2) }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Funding Source</label>
                        <p class="text-base-content">{{ $ticket->fundSource->source_name ?? 'N/A' }}</p>
                    </div>
                </div>

                @if ($ticket->budget_breakdown)
                    <div class="mt-4">
                        <label class="text-sm font-medium text-base-content/70">Budget Breakdown</label>
                        <p class="text-base-content whitespace-pre-wrap bg-base-200 p-3 rounded">
                            {{ $ticket->budget_breakdown }}</p>
                    </div>
                @endif

                {{-- IGP Request --}}
                <div class="mt-4">
                    <label class="text-sm font-medium text-base-content/70">IGP Request</label>
                    <p class="text-base-content">
                        @if ($ticket->igp_requested)
                            <span class="badge badge-success text-white">Requested</span>
                            @if ($ticket->igp_details)
                                <span
                                    class="block mt-2 bg-base-200 p-3 rounded whitespace-pre-wrap">{{ $ticket->igp_details }}</span>
                            @endif
                        @else
                            <span class="badge badge-neutral">Not Requested</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Additional Information --}}
            @if ($ticket->additional_notes)
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    <h2 class="text-xl font-bold text-base-content mb-4">Additional Information</h2>
                    <p class="text-base-content whitespace-pre-wrap bg-base-200 p-4 rounded">
                        {{ $ticket->additional_notes }}</p>
                </div>
            @endif

            {{-- Attachments --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Attachments</h2>
                @if ($ticket->attachments->count() > 0)
                    <div class="space-y-3">
                        @foreach ($ticket->attachments as $attachment)
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <button type="button" class="link link-neutral font-medium"
                                            wire:click="previewAttachment({{ $attachment->attachment_id }})">
                                            {{ $attachment->file_name }}
                                        </button>
                                        <p class="text-sm text-base-content/70">
                                            {{ $attachment->file_type ? strtoupper($attachment->file_type) : (strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION)) ?: 'FILE') }}
                                        </p>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm"
                                    wire:click="downloadAttachment({{ $attachment->attachment_id }})">
                                    Download
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-mary-icon name="o-document-text" class="w-12 h-12 text-base-content/30 mx-auto mb-3" />
                        <p class="text-base-content/70">No attachments uploaded</p>
                    </div>
                @endif
            </div>

            {{-- Comments --}}
            <x-comment-boxes.ticket-comments :ticket="$ticket" />
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Ticket Info --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Ticket Details</h2>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-base-content/70">Ticket Number</label>
                        <p class="text-base-content font-mono">{{ $ticket->ticket_number }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Submitted By</label>
                        <p class="text-base-content">{{ $ticket->user->name }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Email</label>
                        <p class="text-base-content">{{ $ticket->user->email }}</p>
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

            {{-- Approval History --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Approval History</h2>

                @php
                    // Combine all approvals into one collection with timestamps
                    $allApprovals = collect();

                    // Add OSA approvals
                    foreach ($ticket->osaApprovals as $approval) {
                        $allApprovals->push([
                            'type' => 'OSA',
                            'office_name' => 'Office of Student Affairs',
                            'user' => $approval->user,
                            'decision' => $approval->decision,
                            'remarks' => $approval->remarks,
                            'created_at' => $approval->created_at,
                        ]);
                    }

                    // Add Office approvals (GSO, etc.)
                    foreach ($ticket->officeApprovals as $approval) {
                        $allApprovals->push([
                            'type' => 'Office',
                            'office_name' => $approval->office->office_name ?? 'Unknown Office',
                            'user' => $approval->user,
                            'decision' => $approval->decision,
                            'remarks' => $approval->remarks,
                            'created_at' => $approval->created_at,
                        ]);
                    }

                    // Sort by date (most recent first)
                    $allApprovals = $allApprovals->sortByDesc('created_at');

                    // Decision badge classes
                    $decisionClasses = [
                        'approved' => 'badge-success',
                        'rejected' => 'badge-error',
                        'pending' => 'badge-warning',
                        'forwarded' => 'badge-info',
                        'under_review' => 'badge-info',
                        'revision_requested' => 'badge-warning',
                        'needs_revision' => 'badge-warning',
                    ];
                @endphp

                @if ($allApprovals->count() > 0)
                    <div class="relative">
                        {{-- Timeline Line --}}
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-base-300"></div>

                        <div class="space-y-6">
                            @foreach ($allApprovals as $index => $approval)
                                <div class="relative pl-10">
                                    {{-- Timeline Dot --}}
                                    <div
                                        class="absolute left-2 top-1 w-4 h-4 rounded-full {{ $approval['decision'] === 'approved' ? 'bg-success' : ($approval['decision'] === 'rejected' ? 'bg-error' : ($approval['decision'] === 'pending' ? 'bg-warning' : ($approval['decision'] === 'forwarded' ? 'bg-info' : 'bg-info'))) }} ring-4 ring-base-100">
                                    </div>

                                    {{-- Content --}}
                                    <div class="bg-base-200 rounded-lg p-3">
                                        <div class="flex justify-between items-start mb-1">
                                            <div>
                                                <p class="font-semibold text-base-content text-sm">
                                                    {{ $approval['office_name'] }}
                                                </p>
                                                <p class="text-xs text-base-content/70">
                                                    {{ $approval['user']->name ?? 'System' }}
                                                </p>
                                            </div>
                                            <span
                                                class="badge badge-sm {{ $decisionClasses[$approval['decision']] ?? 'badge-neutral' }} text-white">{{ ucfirst(str_replace('_', ' ', $approval['decision'])) }}</span>
                                        </div>

                                        @if ($approval['remarks'])
                                            <div class="mt-2 pt-2 border-t border-base-300">
                                                <p class="text-xs font-medium text-base-content/70 mb-1">
                                                    Remarks:</p>
                                                <p class="text-sm text-base-content/80">
                                                    {{ $approval['remarks'] }}</p>
                                            </div>
                                        @endif

                                        <p class="text-xs text-base-content/50 mt-2">
                                            {{ $approval['created_at']->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-base-content/70">No approval actions yet</p>
                        <p class="text-sm text-base-content/50 mt-1">This ticket is awaiting review</p>
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Actions</h2>

                @php
                    $overviewData = is_array($statusOverview ?? null) ? $statusOverview : [];
                    $hasOfficeActions = in_array('approve', $allowedActions, true) || in_array('reject', $allowedActions, true);
                    $currentUser = auth()->user();

                    $targetOfficeId = $overviewData['office_id'] ?? null;
                    $officeApprovalRecord = null;

                    if ($targetOfficeId !== null) {
                        $officeApprovalRecord = $ticket->officeApprovals
                            ->firstWhere('office_id', (int) $targetOfficeId);
                    }

                    if (! $officeApprovalRecord && $currentUser && $currentUser->isGSO()) {
                        $fallbackOfficeId = $currentUser->office_id;

                        if ($fallbackOfficeId) {
                            $officeApprovalRecord = $ticket->officeApprovals
                                ->firstWhere('office_id', (int) $fallbackOfficeId);
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

                    $canPerformOfficeDecision = $hasOfficeActions
                        && $currentUser
                        && $currentUser->isGSO()
                        && ($currentUser->can('approve', $ticket) || $currentUser->can('reject', $ticket));

                    $shouldRenderOfficeActions = $officeDecisionPending && $canPerformOfficeDecision;

                    $resolvedGsoApproval = $officeApprovalRecord;

                    if (! $resolvedGsoApproval && $currentUser && $currentUser->isGSO() && $currentUser->office_id) {
                        $resolvedGsoApproval = $ticket->officeApprovals
                            ->firstWhere('office_id', (int) $currentUser->office_id);
                    }

                    $resolvedDecision = $resolvedGsoApproval?->decision;

                    $officeDecisionDetails = null;

                    if ($resolvedGsoApproval && $resolvedDecision && strcasecmp($resolvedDecision, 'pending') !== 0) {
                        $decisionKey = \Illuminate\Support\Str::of($resolvedDecision)->lower()->toString();

                        $officeDecisionDetails = [
                            'status' => $decisionKey,
                            'message' => $decisionKey === 'approved'
                                ? 'This ticket has been approved.'
                                : 'This ticket has been rejected.',
                            'wrapper' => $decisionKey === 'approved'
                                ? 'flex items-start gap-3 rounded-2xl bg-info/10 border border-info/30 px-4 py-3 text-info'
                                : 'flex items-start gap-3 rounded-2xl bg-error/10 border border-error/30 px-4 py-3 text-error',
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
                                    <button class="btn btn-success w-full text-base-200 flex justify-between"
                                        @click="showApproval = true">
                                        Approve Ticket
                                    </button>
                                @endcan
                            @endif

                            @if (in_array('reject', $allowedActions))
                                @can('reject', $ticket)
                                    <button class="btn btn-error w-full text-base-200 flex justify-between"
                                        @click="showRejection = true">
                                        Reject Ticket
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
                                    <button class="btn btn-success w-full text-base-200 flex justify-between"
                                        @click="showFinalApproval = true">
                                        Final Approval
                                    </button>
                                @endcan
                            @endif

                            @if (in_array('final_reject', $allowedActions))
                                @can('finalReject', $ticket)
                                    <button class="btn btn-error w-full text-base-200 flex justify-between"
                                        @click="showFinalRejection = true">
                                        Final Rejection
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
                                <button class="btn btn-success w-full text-base-200 flex justify-between"
                                    @click="showApproval = true">
                                    Approve Ticket
                                </button>
                            @endcan
                        @endif

                        @if (in_array('reject', $allowedActions))
                            @can('reject', $ticket)
                                <button class="btn btn-error w-full text-base-200 flex justify-between"
                                    @click="showRejection = true">
                                    Reject Ticket
                                </button>
                            @endcan
                        @endif
                    </div>
                @elseif (in_array($ticket->status, ['received', 'amended']))
                    {{-- Initial Review Actions --}}
                    <div class="space-y-3">
                        @if (in_array('approve', $allowedActions))
                            @can('approve', $ticket)
                                <button class="btn btn-success w-full text-base-200 flex justify-between"
                                    @click="showApproval = true">
                                    Approve Ticket
                                </button>
                            @endcan
                        @endif

                        @if (in_array('revision', $allowedActions))
                            @can('requestRevision', $ticket)
                                <button class="btn btn-warning w-full text-base-200 flex justify-between"
                                    @click="showRevision = true">
                                    Request Revision
                                </button>
                            @endcan
                        @endif

                        @if (in_array('forward', $allowedActions))
                            @can('forwardToGso', $ticket)
                                <button class="btn btn-info w-full text-base-200 flex justify-between"
                                    @click="showForward = true">
                                    Forward to GSO
                                </button>
                            @endcan
                        @endif

                        @if (in_array('reject', $allowedActions))
                            @can('reject', $ticket)
                                <button class="btn btn-error w-full text-base-200 flex justify-between"
                                    @click="showRejection = true">
                                    Reject Ticket
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
                                    'wrapper' => 'flex items-start gap-3 rounded-2xl bg-info/10 border border-info/30 px-4 py-3 text-info',
                                    'icon' => 'o-check-circle',
                                    'label' => 'This ticket has been approved.',
                                ],
                                'rejected' => [
                                    'wrapper' => 'flex items-start gap-3 rounded-2xl bg-error/10 border border-error/30 px-4 py-3 text-error',
                                    'icon' => 'o-x-circle',
                                    'label' => 'This ticket has been rejected.',
                                ],
                                'needs_revision' => [
                                    'wrapper' => 'flex items-start gap-3 rounded-2xl bg-warning/10 border border-warning/30 px-4 py-3 text-warning',
                                    'icon' => 'o-arrow-path',
                                    'label' => 'Waiting for revision from the student organization.',
                                ],
                                'gso_review' => [
                                    'wrapper' => 'flex items-start gap-3 rounded-2xl bg-secondary/10 border border-secondary/30 px-4 py-3 text-secondary',
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
                <x-mary-button label="Confirm Approval" class="btn-success" wire:click="approveTicket"
                    spinner="approveTicket" x-bind:disabled="approvalRemarks.trim().length < 3"
                    @click="$wire.set('approvalRemarks', approvalRemarks)" />
            </div>
        </div>
    </div>

    {{-- Rejection Modal (Alpine) --}}
    <div x-show="showRejection" x-transition class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-base-300/60 backdrop-blur" @click="showRejection=false"></div>
        <div class="relative bg-base-100 rounded-box shadow-xl w-full max-w-xl p-6">
            <h3 class="text-lg font-bold mb-4">Confirm Ticket Rejection</h3>
            <div class="space-y-4">
                <div class="alert alert-error">
                    <x-mary-icon name="o-x-circle" class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold">You are about to reject this ticket</h3>
                        <p class="text-sm">This action cannot be undone. No event will be created.</p>
                    </div>
                </div>
                <label class="text-sm font-medium">Rejection Remarks</label>
                <textarea x-model="rejectionRemarks" class="textarea textarea-bordered w-full" rows="4"
                    placeholder="Explain the reason for rejecting this ticket..."></textarea>
                <p class="text-xs text-base-content/60">Provide detailed explanation for the rejection (minimum 10
                    characters)</p>
                @error('rejectionRemarks')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <x-mary-button label="Cancel" class="btn" @click="showRejection=false" />
                <x-mary-button label="Confirm Rejection" class="btn-error" wire:click="rejectTicket"
                    spinner="rejectTicket" x-bind:disabled="rejectionRemarks.trim().length < 10"
                    @click="$wire.set('rejectionRemarks', rejectionRemarks)" />
            </div>
        </div>
    </div>

    {{-- Revision Request Modal (Alpine) --}}
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
                <x-mary-button label="Request Revision" class="btn-warning" wire:click="requestRevision"
                    spinner="requestRevision" x-bind:disabled="revisionRemarks.trim().length < 10"
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
                <x-mary-button label="Forward to GSO" class="btn-info" wire:click="forwardToGso"
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
                <x-mary-button label="Confirm Final Approval" class="btn-success" wire:click="finalApproval"
                    spinner="finalApproval" x-bind:disabled="finalApprovalRemarks.trim().length < 3"
                    @click="$wire.set('finalApprovalRemarks', finalApprovalRemarks)" />
            </div>
        </div>
    </div>

    {{-- Final Rejection Modal (Alpine) --}}
    <div x-show="showFinalRejection" x-transition class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-base-300/60 backdrop-blur" @click="showFinalRejection=false"></div>
        <div class="relative bg-base-100 rounded-box shadow-xl w-full max-w-xl p-6">
            <h3 class="text-lg font-bold mb-4">Final Rejection</h3>
            <div class="space-y-4">
                <div class="alert alert-error">
                    <x-mary-icon name="o-x-circle" class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold">Final rejection after GSO review</h3>
                        <p class="text-sm">This action cannot be undone. No event will be created.</p>
                    </div>
                </div>
                <label class="text-sm font-medium">Final Rejection Remarks</label>
                <textarea x-model="finalRejectionRemarks" class="textarea textarea-bordered w-full" rows="4"
                    placeholder="Explain the reason for final rejection..."></textarea>
                <p class="text-xs text-base-content/60">Provide detailed explanation considering GSO's input (minimum
                    10 characters)</p>
                @error('finalRejectionRemarks')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <x-mary-button label="Cancel" class="btn" @click="showFinalRejection=false" />
                <x-mary-button label="Confirm Final Rejection" class="btn-error" wire:click="finalRejection"
                    spinner="finalRejection" x-bind:disabled="finalRejectionRemarks.trim().length < 10"
                    @click="$wire.set('finalRejectionRemarks', finalRejectionRemarks)" />
            </div>
        </div>
    </div>

    {{-- Add JavaScript for handling Livewire events --}}
    @script
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

                Livewire.on('ticket-revision-requested', () => {
                    // Dispatch Alpine event to close modal
                    window.dispatchEvent(new CustomEvent('ticket-revision-requested'));
                });

                Livewire.on('ticket-rejected', () => {
                    // Dispatch Alpine event to close modal
                    window.dispatchEvent(new CustomEvent('ticket-rejected'));
                });

                Livewire.on('ticket-final-approved', () => {
                    // Dispatch Alpine event to close modal
                    window.dispatchEvent(new CustomEvent('ticket-final-approved'));
                });

                Livewire.on('ticket-final-rejected', () => {
                    // Dispatch Alpine event to close modal
                    window.dispatchEvent(new CustomEvent('ticket-final-rejected'));
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
    @endscript
</div>
