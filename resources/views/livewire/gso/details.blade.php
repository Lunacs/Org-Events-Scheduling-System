<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-emerald-600 uppercase tracking-wide">Ticket Details</p>
                <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ $eventDetails['title'] ?? 'Event Ticket' }}
                </h1>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="badge {{ $overview['status_badge'] ?? 'badge-warning' }} px-3 py-2">
                        {{ $overview['status_label'] ?? 'Pending' }}
                    </span>
                    <span class="badge {{ $overview['priority_badge'] ?? 'badge-success' }} px-3 py-2">
                        Priority: {{ $overview['priority_label'] ?? 'Low' }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('gso.ticket-review') }}" class="btn btn-ghost gap-2">
                    <x-mary-icon name="s-arrow-left" class="w-5 h-5" />
                    Back to Tickets
                </a>
            </div>
        </div>

        <x-mary-card title="Ticket Overview" subtitle="Summary of the request">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                <div>
                    <p class="text-gray-500">Ticket Number</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $overview['ticket_number'] ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Request Type</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $overview['event_type'] ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Submitted</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $overview['submitted_at'] ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Last Updated</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $overview['last_updated'] ?? '—' }}
                    </p>
                </div>
            </div>
        </x-mary-card>

        <x-mary-card title="Organization Information" subtitle="Submitted by the student organization">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <p class="text-gray-500">Organization Name</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $organization['organization_name'] ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Organization Code</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $organization['organization_code'] ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Course / Cluster</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $organization['course'] ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Organization Adviser</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $organization['adviser'] ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Proponent</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $organization['proponent'] ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Position</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $organization['position'] ?? '—' }}
                    </p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-gray-500">Contact Email</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $organization['email'] ?? '—' }}
                    </p>
                </div>
            </div>
        </x-mary-card>

        <x-mary-card title="Event Details" subtitle="What the organization is requesting">
            <div class="space-y-6 text-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-500">Event Title</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ $eventDetails['title'] ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500">Event Type</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ $eventDetails['event_type'] ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500">Date Range</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ $eventDetails['date_range'] ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500">Time Range</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ $eventDetails['time_range'] ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500">Primary Venue</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ $eventDetails['venue_requested'] ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500">Alternative Venue</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ $eventDetails['alternate_venue'] ?? '—' }}
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-gray-500">Sponsoring Body</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ $eventDetails['sponsoring_body'] ?? '—' }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-gray-500 mb-2">Event Description</p>
                    <p class="text-base text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ $eventDetails['description'] ?? 'No description provided.' }}
                    </p>
                </div>

                @if (!empty($eventDetails['notes']))
                    <div>
                        <p class="text-gray-500 mb-2">Additional Notes</p>
                        <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
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
                    <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 p-4">
                        <p class="text-gray-500">PLV Participants</p>
                        <p class="text-2xl font-semibold text-emerald-700 dark:text-emerald-300">
                            {{ number_format($participants['plv'] ?? 0) }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 p-4">
                        <p class="text-gray-500">External Participants</p>
                        <p class="text-2xl font-semibold text-emerald-700 dark:text-emerald-300">
                            {{ number_format($participants['external'] ?? 0) }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-emerald-100 dark:bg-emerald-900/40 p-4">
                        <p class="text-gray-500">Total Participants</p>
                        <p class="text-2xl font-semibold text-emerald-800 dark:text-emerald-200">
                            {{ number_format($participants['total'] ?? 0) }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-gray-500 mb-3">Special Requirements</p>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($requirements as $requirement)
                            <span class="badge badge-outline badge-sm">{{ $requirement }}</span>
                        @empty
                            <span class="text-sm text-gray-500">No additional requirements noted.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-mary-card>

        <x-mary-card title="Schedule & Logistics" subtitle="Planned sessions and approval status">
            @if (count($schedules) > 0)
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                <th>Event Segment</th>
                                <th>Date &amp; Time</th>
                                <th>Venue</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedules as $schedule)
                                <tr>
                                    <td class="align-top">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $schedule['event_label'] }}
                                        </div>
                                        @if (!empty($schedule['event_notes']))
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $schedule['event_notes'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-top text-sm text-gray-600 dark:text-gray-300">
                                        {{ $schedule['datetime'] }}
                                    </td>
                                    <td class="align-top text-sm text-gray-600 dark:text-gray-300">
                                        {{ $schedule['venue'] }}
                                    </td>
                                    <td class="align-top">
                                        <span class="badge {{ $schedule['status_badge'] }} px-3 py-2">
                                            {{ $schedule['status_label'] }}
                                        </span>
                                    </td>
                                    <td class="align-top text-sm text-gray-600 dark:text-gray-300">
                                        {{ $schedule['remarks'] ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500">No schedules have been submitted for this ticket.</p>
            @endif
        </x-mary-card>

        <x-mary-card title="Attachments" subtitle="Supporting documents provided with the request">
            <div class="space-y-3">
                @forelse ($attachments as $attachment)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border border-base-200 dark:border-base-300 rounded-xl px-4 py-3">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $attachment['name'] }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $attachment['type'] }}
                                @if (!empty($attachment['size']))
                                    • {{ $attachment['size'] }}
                                @endif
                            </p>
                        </div>
                        @if (!empty($attachment['url']))
                            <a href="{{ $attachment['url'] }}" target="_blank" class="btn btn-sm btn-outline btn-emerald">
                                <x-mary-icon name="s-arrow-down-tray" class="w-4 h-4" />
                                Download
                            </a>
                        @else
                            <span class="text-xs text-gray-400">File unavailable</span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No attachments were uploaded for this ticket.</p>
                @endforelse
            </div>
        </x-mary-card>

        <x-mary-card title="Approval History" subtitle="How this request has progressed">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">OSA Decisions</h3>
                    @if (count($osaApprovals) > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Reviewer</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($osaApprovals as $approval)
                                        <tr>
                                            <td class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ $approval['approver'] }}
                                            </td>
                                            <td>
                                                <span class="badge {{ $approval['badge'] }} px-3 py-2">
                                                    {{ $approval['decision_label'] }}
                                                </span>
                                            </td>
                                            <td class="text-xs text-gray-500">
                                                {{ $approval['remarks'] ?? '—' }}
                                            </td>
                                            <td class="text-xs text-gray-500">
                                                {{ $approval['timestamp'] ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No OSA approvals recorded yet.</p>
                    @endif
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Office Decisions</h3>
                    @if (count($officeApprovals) > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Office</th>
                                        <th>Reviewer</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($officeApprovals as $approval)
                                        <tr>
                                            <td class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ $approval['office'] }}
                                            </td>
                                            <td class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ $approval['approver'] }}
                                            </td>
                                            <td>
                                                <span class="badge {{ $approval['badge'] }} px-3 py-2">
                                                    {{ $approval['decision_label'] }}
                                                </span>
                                            </td>
                                            <td class="text-xs text-gray-500">
                                                {{ $approval['remarks'] ?? '—' }}
                                            </td>
                                            <td class="text-xs text-gray-500">
                                                {{ $approval['timestamp'] ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No office approvals recorded yet.</p>
                    @endif
                </div>
            </div>
        </x-mary-card>

        <x-mary-card title="Actions" subtitle="Decide on this ticket once you've reviewed all sections">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="text-sm text-gray-500">
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
