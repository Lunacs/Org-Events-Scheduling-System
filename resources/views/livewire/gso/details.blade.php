<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div>
            <x-mary-button label="Back to Tickets" icon="s-arrow-left" class="btn-ghost"
                link="{{ route('gso.ticket-review') }}" wire:navigate />
        </div>

        @php
            $overviewItems = [
                ['label' => 'Ticket Number', 'value' => $overview['ticket_number'] ?? '—'],
                ['label' => 'Request Type', 'value' => $overview['event_type'] ?? '—'],
                ['label' => 'Submitted', 'value' => $overview['submitted_at'] ?? '—'],
                ['label' => 'Last Updated', 'value' => $overview['last_updated'] ?? '—'],
            ];

            $organizationItems = [
                ['label' => 'Organization Name', 'value' => $organization['organization_name'] ?? '—'],
                ['label' => 'Organization Code', 'value' => $organization['organization_code'] ?? '—'],
                ['label' => 'Course / Cluster', 'value' => $organization['course'] ?? '—'],
                ['label' => 'Organization Adviser', 'value' => $organization['adviser'] ?? '—'],
                ['label' => 'Proponent', 'value' => $organization['proponent'] ?? '—'],
                ['label' => 'Position', 'value' => $organization['position'] ?? '—'],
                ['label' => 'Contact Email', 'value' => $organization['email'] ?? '—', 'span' => 2],
                ['label' => 'Proponent Contact', 'value' => $organization['proponent_contact'] ?? '—'],
                ['label' => 'Adviser Contact', 'value' => $organization['adviser_contact'] ?? '—'],
            ];

            $eventInfoItems = [
                ['label' => 'Event Title', 'value' => $eventDetails['title'] ?? '—'],
                ['label' => 'Event Type', 'value' => $eventDetails['event_type'] ?? '—'],
                ['label' => 'Date Range', 'value' => $eventDetails['date_range'] ?? '—'],
                ['label' => 'Time Range', 'value' => $eventDetails['time_range'] ?? '—'],
                ['label' => 'Off-Campus Activity', 'value' => $eventDetails['off_campus_label'] ?? 'No'],
                ['label' => 'Primary Venue', 'value' => $eventDetails['venue_requested'] ?? '—'],
                ['label' => 'Alternative Venue', 'value' => $eventDetails['alternate_venue'] ?? '—'],
                ['label' => 'Sponsoring Body', 'value' => $eventDetails['sponsoring_body'] ?? '—', 'span' => 2],
            ];

            $participantStats = [
                [
                    'label' => 'PLV Participants',
                    'value' => number_format($participants['plv'] ?? 0),
                    'wrapper' => 'bg-emerald-50 dark:bg-emerald-900/20',
                    'valueClass' => 'text-emerald-700 dark:text-emerald-300',
                ],
                [
                    'label' => 'External Participants',
                    'value' => number_format($participants['external'] ?? 0),
                    'wrapper' => 'bg-emerald-50 dark:bg-emerald-900/20',
                    'valueClass' => 'text-emerald-700 dark:text-emerald-300',
                ],
                [
                    'label' => 'Total Participants',
                    'value' => number_format($participants['total'] ?? 0),
                    'wrapper' => 'bg-emerald-100 dark:bg-emerald-900/40',
                    'valueClass' => 'text-emerald-800 dark:text-emerald-200',
                ],
            ];

            $fundingItems = [
                ['label' => 'Funding Source', 'value' => $financial['fund_source'] ?? '—'],
                ['label' => 'Estimated Budget', 'value' => $financial['estimated_budget'] ?? '—'],
                ['label' => 'IGP Request', 'value' => $igp['request_label'] ?? 'Not Requested'],
            ];

            $hasBudgetBreakdown = $financial['has_breakdown'] ?? false;
            $budgetBreakdown = $financial['budget_breakdown'] ?? null;
            $igpDetails = $igp['details'] ?? null;
            $igpHasDetails = $igp['has_details'] ?? false;

            $showOffCampusCard = $offCampus['has_details'] ?? false;
            $offCampusItems = [
                ['label' => 'Accommodation Provider', 'value' => $offCampus['accommodation'] ?? '—'],
                ['label' => 'Transport Service Provider', 'value' => $offCampus['transport_provider_label'] ?? '—'],
                ['label' => 'Driver Name', 'value' => $offCampus['driver_name'] ?? '—'],
                ['label' => 'Driver Contact', 'value' => $offCampus['driver_contact'] ?? '—'],
                ['label' => 'Vehicle Type', 'value' => $offCampus['vehicle_type'] ?? '—'],
                ['label' => 'Vehicle Plate Number', 'value' => $offCampus['vehicle_plate'] ?? '—'],
            ];

            $scheduleHeaders = [
                ['key' => 'event_label', 'label' => 'Event Segment'],
                ['key' => 'datetime', 'label' => 'Date & Time'],
                ['key' => 'venue', 'label' => 'Venue'],
                ['key' => 'status_label', 'label' => 'Status'],
                ['key' => 'remarks', 'label' => 'Remarks'],
            ];

            $osaHeaders = [
                ['key' => 'approver', 'label' => 'Reviewer'],
                ['key' => 'decision_label', 'label' => 'Status'],
                ['key' => 'remarks', 'label' => 'Remarks'],
                ['key' => 'timestamp', 'label' => 'Date'],
            ];

            $officeHeaders = [
                ['key' => 'office', 'label' => 'Office'],
                ['key' => 'approver', 'label' => 'Reviewer'],
                ['key' => 'decision_label', 'label' => 'Status'],
                ['key' => 'remarks', 'label' => 'Remarks'],
                ['key' => 'timestamp', 'label' => 'Date'],
            ];
        @endphp

        <div class="flex flex-col gap-3">
            <div>
                <p class="text-sm font-medium text-emerald-600 uppercase tracking-wide">Ticket Details</p>
                <h1 class="text-3xl font-semibold text-base-content">
                    {{ $eventDetails['title'] ?? 'Event Ticket' }}
                </h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-mary-badge :value="$overview['status_label'] ?? 'Pending'"
                    class="badge-lg {{ $overview['status_badge'] ?? 'badge-warning' }}" />
                <x-mary-badge :value="'Priority: ' . ($overview['priority_label'] ?? 'Low')"
                    class="badge-lg {{ $overview['priority_badge'] ?? 'badge-success' }}" />
            </div>
        </div>

        <x-mary-card title="Ticket Overview" subtitle="Summary of the request">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                @foreach ($overviewItems as $item)
                    <div class="space-y-1">
                        <p class="text-sm text-base-content/60">{{ $item['label'] }}</p>
                        <p class="text-base font-semibold text-base-content">{{ $item['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-mary-card>

        <x-mary-card title="Organization Information" subtitle="Submitted by the student organization">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach ($organizationItems as $item)
                    <div @class(['space-y-1', 'md:col-span-2' => ($item['span'] ?? 1) === 2])>
                        <p class="text-sm text-base-content/60">{{ $item['label'] }}</p>
                        <p class="text-base font-semibold text-base-content">{{ $item['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-mary-card>

        <x-mary-card title="Event Details" subtitle="What the organization is requesting">
            <div class="space-y-6 text-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($eventInfoItems as $item)
                        <div @class(['space-y-1', 'md:col-span-2' => ($item['span'] ?? 1) === 2])>
                            <p class="text-sm text-base-content/60">{{ $item['label'] }}</p>
                            <p class="text-base font-semibold text-base-content">{{ $item['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-2">
                    <p class="text-sm text-base-content/60">Event Description</p>
                    <p class="text-base text-base-content/80 leading-relaxed">
                        {{ $eventDetails['description'] ?? 'No description provided.' }}
                    </p>
                </div>

                @if (!empty($eventDetails['organizer_notes']))
                    <div class="space-y-2">
                        <p class="text-sm text-base-content/60">Organizer Notes</p>
                        <p class="text-base text-base-content/80 leading-relaxed whitespace-pre-line">
                            {{ $eventDetails['organizer_notes'] }}
                        </p>
                    </div>
                @endif

                @if (!empty($eventDetails['notes']))
                    <div class="space-y-2">
                        <p class="text-sm text-base-content/60">Additional Notes</p>
                        <ul class="list-disc list-inside space-y-1 text-base-content/80">
                            @foreach ($eventDetails['notes'] as $note)
                                <li>{{ $note }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </x-mary-card>

        <x-mary-card title="Budget & Funding" subtitle="Financial plan and income generation">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach ($fundingItems as $item)
                    <div class="space-y-1">
                        <p class="text-sm text-base-content/60">{{ $item['label'] }}</p>
                        <p class="text-base font-semibold text-base-content">{{ $item['value'] }}</p>
                    </div>
                @endforeach
            </div>

            @if ($hasBudgetBreakdown)
                <div class="space-y-2 mt-6">
                    <p class="text-sm text-base-content/60">Budget Breakdown</p>
                    <p class="text-base text-base-content/80 whitespace-pre-line">{{ $budgetBreakdown }}</p>
                </div>
            @endif

            <div class="space-y-2 mt-6">
                <p class="text-sm text-base-content/60">IGP Details</p>
                @if ($igpHasDetails)
                    <p class="text-base text-base-content/80 whitespace-pre-line">{{ $igpDetails }}</p>
                @else
                    <span class="text-sm text-base-content/50">
                        {{ ($igp['requested'] ?? false) ? 'No details provided.' : 'No IGP requested for this event.' }}
                    </span>
                @endif
            </div>
        </x-mary-card>

        @if ($showOffCampusCard)
            <x-mary-card title="Off-Campus Logistics" subtitle="Transportation and accommodation details">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    @foreach ($offCampusItems as $item)
                        <div class="space-y-1">
                            <p class="text-sm text-base-content/60">{{ $item['label'] }}</p>
                            <p class="text-base font-semibold text-base-content">{{ $item['value'] ?? '—' }}</p>
                        </div>
                    @endforeach
                </div>
            </x-mary-card>
        @endif

        <x-mary-card title="Participants & Requirements" subtitle="Headcount and logistical needs">
            <div class="space-y-6 text-sm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($participantStats as $metric)
                        <div class="rounded-xl p-4 {{ $metric['wrapper'] }}">
                            <p class="text-sm text-base-content/60">{{ $metric['label'] }}</p>
                            <p class="text-2xl font-semibold {{ $metric['valueClass'] }}">
                                {{ $metric['value'] }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-2">
                    <p class="text-sm text-base-content/60">Special Requirements</p>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($requirements as $requirement)
                            <x-mary-badge :value="$requirement" class="badge-outline badge-sm" />
                        @empty
                            <span class="text-sm text-base-content/50">No additional requirements noted.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-mary-card>

        <x-mary-card title="Schedule & Logistics" subtitle="Planned sessions and approval status">
            @if (count($schedules) > 0)
                <x-mary-table :headers="$scheduleHeaders" :rows="$schedules">
                    @scope('cell_event_label', $row)
                        <div class="font-medium text-base-content">{{ $row['event_label'] }}</div>
                        @if (!empty($row['event_notes']))
                            <div class="text-xs text-base-content/60">
                                {{ $row['event_notes'] }}
                            </div>
                        @endif
                    @endscope

                    @scope('cell_status_label', $row)
                        <x-mary-badge :value="$row['status_label']" class="badge-lg {{ $row['status_badge'] }}" />
                    @endscope

                    @scope('cell_remarks', $row)
                        {{ $row['remarks'] ?? '—' }}
                    @endscope
                </x-mary-table>
            @else
                <p class="text-sm text-base-content/60">No schedules have been submitted for this ticket.</p>
            @endif
        </x-mary-card>

        <x-mary-card title="Attachments" subtitle="Supporting documents provided with the request">
            @if (count($attachments) > 0)
                <ul class="space-y-3">
                    @foreach ($attachments as $attachment)
                        @php
                            $meta = collect([$attachment['type'] ?? null, $attachment['size'] ?? null])
                                ->filter()
                                ->implode(' • ');
                        @endphp
                        <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border border-base-200 dark:border-base-300 rounded-xl px-4 py-3">
                            <x-mary-list-item :item="[
                                'title' => $attachment['name'],
                                'subtitle' => $meta,
                            ]" value="title" sub-value="subtitle" class="flex-1" no-separator />

                            @if (!empty($attachment['url']))
                                <x-mary-button label="Download" icon="s-arrow-down-tray"
                                    class="btn-sm btn-outline btn-emerald"
                                    link="{{ $attachment['url'] }}" target="_blank" />
                            @else
                                <span class="text-xs text-base-content/50">File unavailable</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-base-content/60">No attachments were uploaded for this ticket.</p>
            @endif
        </x-mary-card>

        <x-mary-card title="Approval History" subtitle="How this request has progressed">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-base-content">OSA Decisions</h3>

                    @if (count($osaApprovals) > 0)
                        <x-mary-table :headers="$osaHeaders" :rows="$osaApprovals">
                            @scope('cell_decision_label', $row)
                                <x-mary-badge :value="$row['decision_label']"
                                    class="badge-lg {{ $row['badge'] }}" />
                            @endscope

                            @scope('cell_remarks', $row)
                                {{ $row['remarks'] ?? '—' }}
                            @endscope

                            @scope('cell_timestamp', $row)
                                {{ $row['timestamp'] ?? '—' }}
                            @endscope
                        </x-mary-table>
                    @else
                        <p class="text-sm text-base-content/60">No OSA approvals recorded yet.</p>
                    @endif
                </div>

                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-base-content">Office Decisions</h3>

                    @if (count($officeApprovals) > 0)
                        <x-mary-table :headers="$officeHeaders" :rows="$officeApprovals">
                            @scope('cell_decision_label', $row)
                                <x-mary-badge :value="$row['decision_label']"
                                    class="badge-lg {{ $row['badge'] }}" />
                            @endscope

                            @scope('cell_remarks', $row)
                                {{ $row['remarks'] ?? '—' }}
                            @endscope

                            @scope('cell_timestamp', $row)
                                {{ $row['timestamp'] ?? '—' }}
                            @endscope
                        </x-mary-table>
                    @else
                        <p class="text-sm text-base-content/60">No office approvals recorded yet.</p>
                    @endif
                </div>
            </div>
        </x-mary-card>

        @php
            $status = $overview['status'] ?? 'pending';
            $showActions = !in_array($status, ['approved', 'rejected'], true);
            $actionTargetId = $actionApprovalId ?? null;
            $actionsEnabled = $showActions && $actionTargetId !== null;
        @endphp

        @if ($actionsEnabled)
            <x-mary-card title="Actions" subtitle="Decide on this ticket once you've reviewed all sections">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-sm text-base-content/60">
                        Confirm your decision for this office review below. Once approved or rejected, the ticket status will update automatically.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <x-mary-button label="Reject" icon="s-x-mark" class="btn-error btn-outline"
                            wire:click="openActionModal('reject')" />
                        <x-mary-button label="Accept" icon="s-check" class="btn-success"
                            wire:click="openActionModal('approve')" />
                    </div>
                </div>
            </x-mary-card>
        @endif

        @php
            $currentActionType = $this->actionType ?? null;
            $modalRequiredWord = match ($currentActionType) {
                'approve' => 'approve',
                'reject' => 'reject',
                default => null,
            };
            $modalActionLabel = $currentActionType ? ucfirst($currentActionType) : 'Action';
        @endphp

    <x-mary-modal wire:model="showActionModal" title="Confirm {{ $modalActionLabel }}">
        <div x-data="{
                confirmationInput: @entangle('confirmationInput').live,
                currentAction: @entangle('actionType').live,
                requiredWord() {
                    if (this.currentAction === 'approve') {
                        return 'approve';
                    }

                    if (this.currentAction === 'reject') {
                        return 'reject';
                    }

                    return null;
                },
                matchesRequired() {
                    const required = this.requiredWord();

                    if (! required) {
                        return true;
                    }

                    if (! this.confirmationInput) {
                        return false;
                    }

                    return this.confirmationInput.trim().toLowerCase() === required;
                },
                confirmButtonClasses() {
                    if (! this.matchesRequired()) {
                        return 'btn-neutral btn-disabled opacity-60 cursor-not-allowed';
                    }

                    if (this.currentAction === 'approve') {
                        return 'btn-success';
                    }

                    if (this.currentAction === 'reject') {
                        return 'btn-error';
                    }

                    return 'btn-secondary';
                }
            }">
            <p class="mb-2 text-sm text-base-content/80">
                Are you sure you want to {{ strtolower($modalActionLabel) }} this ticket for your office?
            </p>

            @if ($modalRequiredWord)
                <div class="mt-2 space-y-2">
                    <label class="block text-sm font-medium text-base-content/80">Type '{{ $modalRequiredWord }}' to confirm</label>
                    <div class="flex items-center gap-2 w-full">
                        <div class="flex-1">
                            <x-mary-input x-model="confirmationInput" placeholder="{{ $modalRequiredWord }}" class="w-full h-10" />
                        </div>
                        <div class="w-5 h-5 flex items-center justify-center">
                            <svg x-cloak x-show="requiredWord() && matchesRequired()" class="w-4 h-4 text-emerald-500"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <svg x-cloak x-show="requiredWord() && !matchesRequired()" class="w-4 h-4 text-base-content/30"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></circle>
                            </svg>
                        </div>
                    </div>
                    @error('confirmationInput')
                        <p class="text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <x-slot:actions>
                <x-mary-button color="secondary" wire:click="cancelActionModal">
                    Cancel
                </x-mary-button>

                <button type="button"
                    class="btn"
                    x-bind:class="confirmButtonClasses()"
                    x-bind:disabled="!matchesRequired()"
                    wire:click="performAction"
                    wire:loading.attr="disabled"
                    wire:target="performAction">
                    Yes, {{ $modalActionLabel }}
                </button>
            </x-slot:actions>
        </div>
    </x-mary-modal>
    </div>
</div>
