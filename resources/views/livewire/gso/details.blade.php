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
            ];

            $eventInfoItems = [
                ['label' => 'Event Title', 'value' => $eventDetails['title'] ?? '—'],
                ['label' => 'Event Type', 'value' => $eventDetails['event_type'] ?? '—'],
                ['label' => 'Date Range', 'value' => $eventDetails['date_range'] ?? '—'],
                ['label' => 'Time Range', 'value' => $eventDetails['time_range'] ?? '—'],
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

        <x-mary-card title="Actions" subtitle="Decide on this ticket once you've reviewed all sections">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="text-sm text-base-content/60">
                    These actions are read-only placeholders. Accept or reject functionality will be added later.
                </p>
                <div class="flex flex-wrap gap-3">
                    <x-mary-button label="Reject" icon="s-x-mark" class="btn-error btn-outline" />
                    <x-mary-button label="Accept" icon="s-check" class="btn-success" />
                </div>
            </div>
        </x-mary-card>
    </div>
</div>
