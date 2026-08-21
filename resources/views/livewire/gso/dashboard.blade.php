<div>
    <div class="p-6 space-y-6">
        @php
            $normalizeRows = fn($rows) => collect($rows)
                ->map(fn($row) => is_array($row) ? $row : (array) $row)
                ->map(function ($row) {
                    $row['submitted_at'] = $row['event_date'] ?? 'N/A';
                    return $row;
                });

            $pendingApprovalCollection = $normalizeRows($pendingApprovals);
            $approvalSnapshotCollection = $normalizeRows($approvalSnapshot ?? []);

            $defaultRequestTypeBadge = 'badge-ghost text-base-content';

            $requestTypeBadgeDefaults = [
                'venue booking' => 'badge-primary text-white',
                'venue' => 'badge-primary text-white',
                'equipment' => 'badge-info text-white',
                'logistics' => 'badge-secondary text-white',
                'catering' => 'badge-accent text-white',
            ];

            $requestTypeBadgePalette = [
                'badge-success text-white',
                'badge-warning text-white',
                'badge-error text-white',
                'badge-neutral text-white',
                'badge-info text-white',
                'badge-secondary text-white',
                'badge-accent text-white',
                'badge-primary text-white',
            ];

            $requestTypeBadgeMap = $requestTypeBadgeDefaults;
            $paletteIndex = 0;

            $combinedApprovalCollection = $pendingApprovalCollection->concat($approvalSnapshotCollection);

            foreach ($combinedApprovalCollection->pluck('request_type')->filter()->unique()->values() as $typeLabel) {
                $lookupKey = \Illuminate\Support\Str::of($typeLabel)->lower()->toString();

                if (!array_key_exists($lookupKey, $requestTypeBadgeMap)) {
                    $requestTypeBadgeMap[$lookupKey] =
                        $requestTypeBadgePalette[$paletteIndex % count($requestTypeBadgePalette)];
                    $paletteIndex++;
                }
            }

            $assignBadgeClass = fn($collection) => $collection
                ->map(function ($row) use ($requestTypeBadgeMap, $defaultRequestTypeBadge) {
                    $typeKey = \Illuminate\Support\Str::of($row['request_type'] ?? '')
                        ->lower()
                        ->toString();
                    $row['request_type_badge_class'] = $requestTypeBadgeMap[$typeKey] ?? $defaultRequestTypeBadge;

                    return $row;
                })
                ->values();

            $pendingApprovalRows = $assignBadgeClass($pendingApprovalCollection);
            $approvalSnapshotRows = $assignBadgeClass($approvalSnapshotCollection);

            $recentActivityItems = collect($recentActivities)->map(fn($row) => is_array($row) ? $row : (array) $row);
        @endphp

        <section
            class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
            <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold font-heading text-base-content">GSO Dashboard</h1>
                        <p class="text-sm text-base-content/70 mt-1">General Services Office - Event Management
                            System</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics -->
        <div id="overview-metrics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.metric-card label="Pending Approvals" value="{{ number_format(data_get($stats, 'pending', 0)) }}"
                meta="Awaiting review" icon="o-clock" color="warning" />

            <x-ui.metric-card label="Approved Today" value="{{ number_format(data_get($stats, 'approvedToday', 0)) }}"
                meta="Processed today" icon="o-check-circle" color="success" />

            <x-ui.metric-card label="For Revision" value="{{ number_format(data_get($stats, 'for_revisionToday', 0)) }}"
                meta="Needs correction" icon="o-x-circle" color="error" />

            <x-ui.metric-card label="Upcoming Events" value="{{ number_format(data_get($stats, 'upcomingEvents', 0)) }}"
                meta="Scheduled approved" icon="o-calendar-days" color="accent" />

            <x-ui.metric-card label="High Priority" value="{{ number_format($pendingApprovalRows->where('priority_key', 'high')->count()) }}"
                meta="Urgent requests" icon="o-exclamation-triangle" color="warning" />

            <x-ui.metric-card label="Tickets In Queue" value="{{ number_format($ticketsInQueue ?? 0) }}"
                meta="Total in pipeline" icon="o-ticket" color="info" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-ui.card id="ticket-review-section" title="Pending Ticket Review"
                subtitle="Requests requiring your attention" class="col-span-1 lg:col-span-2" shadow>
                <x-slot:menu>
                    <x-ui.button icon-right="o-arrow-right" link="{{ route('gso.ticket-review') }}"
                        class="btn-sm btn-ghost" label="View All" wire:navigate />
                </x-slot:menu>

                @if ($pendingApprovalRows->count() > 0)
                    <div class="space-y-3">
                        @foreach ($pendingApprovalRows as $approval)
                            <div class="p-3 bg-base-200/50 rounded-lg border border-base-300"
                                wire:key="pending-{{ $approval['approval_id'] }}">
                                <!-- Mobile Layout-->
                                <div class="md:hidden space-y-2">
                                    <div class="flex items-start mb-2">
                                        <x-ui.badge :value="$approval['request_type']"
                                            class="{{ $approval['request_type_badge_class'] }} badge-sm whitespace-normal h-auto" />
                                    </div>

                                    <div class="flex flex-col items-start gap-2">
                                        <x-ui.badge :value="$approval['ticket_number']" class="badge-ghost badge-sm" />
                                        <span class="font-semibold text-base-content">{{ $approval['event_title'] }}</span>
                                    </div>

                                    <div class="flex items-center gap-2 text-sm text-base-content/70">
                                        <x-ui.icon name="o-building-office" class="w-4 h-4" />
                                        <span>{{ $approval['organization'] }}</span>
                                    </div>

                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="text-base-content/50">{{ $approval['event_date'] }}</span>
                                    </div>
                                </div>

                                <!-- Tablet/Desktop Layout -->
                                <div class="hidden md:block">
                                    <div class="flex justify-start items-center gap-4 px-1 py-1">
                                        <div class="flex-1 space-y-2">
                                            <div class="flex flex-col gap-2">
                                                <x-ui.badge :value="$approval['request_type']"
                                                    class="{{ $approval['request_type_badge_class'] }} badge-sm" />
                                                <div class="flex items-center gap-2">
                                                    <x-ui.badge :value="$approval['ticket_number']" class="badge-ghost badge-sm" />
                                                    <span class="font-semibold text-base-content">{{ $approval['event_title'] }}</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 text-sm text-base-content/70">
                                                <x-ui.icon name="o-building-office" class="w-4 h-4" />
                                                <span>{{ $approval['organization'] }}</span>
                                                <span class="text-base-content/30">•</span>
                                                <span class="text-base-content/50">{{ $approval['event_date'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state title="No pending approvals" description="All caught up! Great work."
                        icon="o-check-circle" tone="success" iconColor="text-success" />
                @endif
            </x-ui.card>

            <x-ui.card id="recent-activity-section" title="Recent Activity" subtitle="Latest actions from your team"
                shadow>
                @if ($recentActivityItems->count() > 0)
                    <div class="space-y-3">
                        @foreach ($recentActivityItems as $activity)
                            @php
                                $actionText = \Illuminate\Support\Str::of($activity['action'] ?? '')
                                    ->lower()
                                    ->toString();
                                $icon = 'o-chat-bubble-bottom-center-text';
                                $iconClasses = 'text-info';

                                if (str_contains($actionText, 'approve')) {
                                    $icon = 'o-check-circle';
                                    $iconClasses = 'text-success';
                                } elseif (str_contains($actionText, 'reject')) {
                                    $icon = 'o-x-circle';
                                    $iconClasses = 'text-error';
                                }
                            @endphp

                            <div class="p-3 bg-base-200/50 rounded-lg border border-base-300"
                                wire:key="activity-{{ $activity['id'] ?? \Illuminate\Support\Str::uuid()->toString() }}">
                                <div class="flex items-start gap-3">
                                    <x-ui.icon :name="$icon" class="w-5 h-5 {{ $iconClasses }} mt-0.5" />
                                    <div class="flex-1">
                                        <p class="font-medium text-sm text-base-content">
                                            {{ $activity['action'] ?? 'Activity' }}</p>
                                        <p class="text-xs text-base-content/60">
                                            {{ $activity['details'] ?? 'Details unavailable.' }}</p>
                                        <p class="text-xs text-base-content/40 mt-1">
                                            {{ $activity['time_ago'] ?? 'Just now' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-ui.icon name="o-document" class="w-8 h-8 mx-auto mb-2 text-base-content/30" />
                        <p class="text-sm text-base-content/60">Activity logs will appear here once actions
                            are taken.</p>
                    </div>
                @endif
            </x-ui.card>
        </div>

        <x-ui.card id="approval-snapshot-section" title="Approval Snapshot" subtitle="Quick view of recent tickets"
            shadow>

            @if ($approvalSnapshotRows->count() > 0)
                <!-- Mobile Layout (Card-based) -->
                <div class="md:hidden space-y-3">
                    @foreach ($approvalSnapshotRows as $row)
                        <div class="p-4 bg-base-200/50 rounded-lg border border-base-300"
                            wire:key="snapshot-{{ $row['ticket_number'] }}">
                            <div class="space-y-3">
                                <div>
                                    <span class="text-xs text-base-content/50 uppercase">Ticket</span>
                                    <div class="mt-1">
                                        <x-ui.badge :value="$row['ticket_number']" class="badge-primary badge-outline" />
                                    </div>
                                </div>

                                <div>
                                    <span class="text-xs text-base-content/50 uppercase">Event</span>
                                    <p class="font-medium mt-1 text-base-content">{{ $row['event_title'] }}</p>
                                </div>

                                <div>
                                    <span class="text-xs text-base-content/50 uppercase">Organization</span>
                                    <p class="mt-1 text-base-content/70">{{ $row['organization'] }}</p>
                                </div>

                                <div>
                                    <span class="text-xs text-base-content/50 uppercase">Event Date</span>
                                    <p class="mt-1 text-base-content/70">{{ $row['event_date'] }}</p>
                                </div>

                                <div>
                                    <span class="text-xs text-base-content/50 uppercase">Priority</span>
                                    <div class="mt-1">
                                        @php
                                            $priorityClass = match ($row['priority_key'] ?? 'low') {
                                                'high' => 'badge-error',
                                                'medium' => 'badge-warning',
                                                'low' => 'badge-success',
                                                default => 'badge-ghost',
                                            };
                                        @endphp
                                        <x-ui.badge :value="$row['priority'] ?? 'Low'" :class="$priorityClass . ' text-white'" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Tablet/Desktop Layout (Table) -->
                <div class="hidden md:block">
                    <x-ui.table :headers="[
                        ['key' => 'ticket_number', 'label' => 'Ticket'],
                        ['key' => 'event_title', 'label' => 'Event'],
                        ['key' => 'organization', 'label' => 'Organization'],
                        ['key' => 'event_date', 'label' => 'Event Date'],
                        ['key' => 'priority', 'label' => 'Priority'],
                    ]">
                        @foreach ($approvalSnapshotRows as $row)
                            @php
                                $priorityClass = match ($row['priority_key'] ?? 'low') {
                                    'high' => 'badge-error',
                                    'medium' => 'badge-warning',
                                    'low' => 'badge-success',
                                    default => 'badge-ghost',
                                };
                            @endphp
                            <tr wire:key="snapshot-row-{{ $row['ticket_number'] }}">
                                <x-ui.table-column>
                                    <x-ui.badge :value="$row['ticket_number']" class="badge-primary badge-outline" />
                                </x-ui.table-column>
                                <x-ui.table-column>{{ $row['event_title'] }}</x-ui.table-column>
                                <x-ui.table-column>{{ $row['organization'] }}</x-ui.table-column>
                                <x-ui.table-column>{{ $row['event_date'] }}</x-ui.table-column>
                                <x-ui.table-column>
                                    <x-ui.badge :value="$row['priority'] ?? 'Low'" :class="$priorityClass . ' text-white'" />
                                </x-ui.table-column>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </div>
                <div class="mt-4 text-center">
                    <x-ui.button label="Go to Ticket Review" link="{{ route('gso.ticket-review') }}"
                        class="btn-primary" icon-right="o-arrow-right" wire:navigate />
                </div>
            @else
                <x-ui.empty-state title="No approval data to display"
                    description="New tickets will appear here once submitted." icon="o-inbox" />
            @endif
        </x-ui.card>

        <!-- Quick Actions -->
        <div>
            <div class="mb-4">
                <h2 class="text-xl font-bold font-heading text-base-content">Quick Actions</h2>
                <p class="text-sm text-base-content/60">Frequently used features</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-ui.quick-action label="Ticket Review" description="Process office approvals"
                    icon="o-clipboard-document-check" color="primary" link="{{ route('gso.ticket-review') }}" />

                <x-ui.quick-action label="Event Calendar" description="View schedule" icon="o-calendar-days"
                    color="accent" link="{{ route('gso.calendar') }}" />

                <x-ui.quick-action label="Reports" description="Download insights" icon="o-document-chart-bar"
                    color="info" link="{{ route('gso.reports') }}" />
            </div>
        </div>
    </div>
</div>
