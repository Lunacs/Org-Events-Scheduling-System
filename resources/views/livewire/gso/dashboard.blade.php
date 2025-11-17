<div>
    <div class="p-6">
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

            $uniqueOrganizationsCount = $pendingApprovalRows->pluck('organization')->filter()->unique()->count();

            $recentActivityItems = collect($recentActivities)->map(fn($row) => is_array($row) ? $row : (array) $row);
        @endphp

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold font-heading text-primary">GSO Dashboard</h1>
                <p class="text-sm text-gray-600 mt-1">General Services Office - Event Management System</p>
            </div>
            <x-mary-button icon="o-arrow-path" class="btn-primary" wire:click="refreshData">
                <span wire:loading.remove wire:target="refreshData">Refresh Data</span>
                <span wire:loading wire:target="refreshData">Refreshing...</span>
            </x-mary-button>
        </div>

        <div id="overview-metrics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6"
            wire:loading.class="opacity-50" wire:target="refreshData">
            <x-mary-stat title="Pending Approvals" :value="number_format(data_get($stats, 'pending', 0))" icon="o-clock"
                class="bg-linear-to-br from-warning/10 to-warning/5 border border-warning/20"
                tooltip="Requests awaiting GSO review" />

            <x-mary-stat title="Approved Today" :value="number_format(data_get($stats, 'approvedToday', 0))" icon="o-check-circle"
                class="bg-linear-to-br from-success/10 to-success/5 border border-success/20"
                tooltip="Tickets approved by GSO today" />

            <x-mary-stat title="Rejected Today" :value="number_format(data_get($stats, 'rejectedToday', 0))" icon="o-x-circle"
                class="bg-linear-to-br from-error/10 to-error/5 border border-error/20"
                tooltip="Tickets rejected today" />

            <x-mary-stat title="Upcoming Events" :value="number_format(data_get($stats, 'upcomingEvents', 0))" icon="o-calendar-days"
                class="bg-linear-to-br from-primary/10 to-primary/5 border border-primary/20"
                tooltip="Scheduled approved events" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" wire:loading.class="opacity-50"
            wire:target="refreshData">
            <x-mary-card class="border-l-4 border-l-warning shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">High Priority Items</p>
                        <p class="text-3xl font-bold text-warning">
                            {{ number_format($pendingApprovalRows->where('priority_key', 'high')->count()) }}
                        </p>
                    </div>
                    <x-mary-icon name="o-exclamation-triangle" class="w-12 h-12 text-warning opacity-20" />
                </div>
            </x-mary-card>

            <x-mary-card class="border-l-4 border-l-secondary shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Tickets In Queue</p>
                        <p class="text-3xl font-bold text-secondary">
                            {{ number_format($ticketsInQueue ?? 0) }}
                        </p>
                    </div>
                    <x-mary-icon name="o-ticket" class="w-12 h-12 text-secondary opacity-20" />
                </div>
            </x-mary-card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6" wire:loading.class="opacity-50"
            wire:target="refreshData">
            <x-mary-card id="ticket-review-section" title="Pending Ticket Review"
                subtitle="Requests requiring your attention" class="col-span-1 lg:col-span-2 shadow-md">
                <x-slot:menu>
                    <x-mary-button icon="o-eye" link="{{ route('gso.ticket-review') }}" class="btn-sm btn-ghost"
                        label="View All" wire:navigate />
                </x-slot:menu>

                @if ($pendingApprovalRows->count() > 0)
                    <div class="space-y-3">
                        @foreach ($pendingApprovalRows as $approval)
                            <x-mary-list-item :item="$approval" no-separator
                                wire:key="pending-{{ $approval['approval_id'] }}">
                                <x-slot:value>
                                    <div class="flex items-center gap-2">
                                        <x-mary-badge :value="$approval['ticket_number']" class="badge-ghost badge-sm" />
                                        <span class="font-semibold">{{ $approval['event_title'] }}</span>
                                    </div>
                                </x-slot:value>
                                <x-slot:sub-value>
                                    <div class="flex items-center gap-2 text-sm">
                                        <x-mary-icon name="o-building-office" class="w-4 h-4" />
                                        <span>{{ $approval['organization'] }}</span>
                                        <span class="text-gray-400">•</span>
                                        <span class="text-gray-500">{{ $approval['event_date'] }}</span>
                                    </div>
                                </x-slot:sub-value>
                                <x-slot:actions>
                                    <x-mary-badge :value="$approval['request_type']"
                                        class="{{ $approval['request_type_badge_class'] }} badge-sm" />
                                </x-slot:actions>
                            </x-mary-list-item>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <x-mary-icon name="o-check-circle" class="w-16 h-16 mx-auto mb-3 text-success opacity-30" />
                        <p class="text-gray-500 font-medium">No pending approvals</p>
                        <p class="text-sm text-gray-400 mt-1">All caught up! Great work.</p>
                    </div>
                @endif
            </x-mary-card>

            <x-mary-card id="recent-activity-section" title="Recent Activity" subtitle="Latest actions from your team"
                class="shadow-md">
                <x-slot:menu>
                    <x-mary-button icon="o-document-text" link="{{ route('gso.communication') }}"
                        class="btn-sm btn-ghost" label="View Logs" wire:navigate />
                </x-slot:menu>

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

                            <div class="p-3 bg-base-200 rounded-lg border border-base-300"
                                wire:key="activity-{{ $activity['id'] ?? \Illuminate\Support\Str::uuid()->toString() }}">
                                <div class="flex items-start gap-3">
                                    <x-mary-icon :name="$icon" class="w-5 h-5 {{ $iconClasses }} mt-0.5" />
                                    <div class="flex-1">
                                        <p class="font-medium text-sm">{{ $activity['action'] ?? 'Activity' }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $activity['details'] ?? 'Details unavailable.' }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $activity['time_ago'] ?? 'Just now' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-mary-icon name="o-document" class="w-8 h-8 mx-auto mb-2 text-gray-400" />
                        <p class="text-sm text-gray-500">Activity logs will appear here once actions are taken.</p>
                    </div>
                @endif
            </x-mary-card>
        </div>

        <x-mary-card id="approval-snapshot-section" title="Approval Snapshot" subtitle="Quick view of recent tickets"
            class="shadow-md" wire:loading.class="opacity-50" wire:target="refreshData">
            <x-slot:menu>
                <div class="flex gap-2">
                    <x-mary-button icon="o-funnel" class="btn-sm btn-ghost" label="Filter" />
                    <x-mary-button icon="o-arrow-down-tray" class="btn-sm btn-ghost" label="Export" />
                </div>
            </x-slot:menu>

            @if ($approvalSnapshotRows->count() > 0)
                <x-mary-table :headers="[
                    ['key' => 'ticket_number', 'label' => 'Ticket'],
                    ['key' => 'event_title', 'label' => 'Event'],
                    ['key' => 'organization', 'label' => 'Organization'],
                    ['key' => 'event_date', 'label' => 'Event Date'],
                    ['key' => 'priority', 'label' => 'Priority'],
                ]" :rows="$approvalSnapshotRows">
                    @scope('cell_ticket_number', $row)
                        <x-mary-badge :value="$row['ticket_number']" class="badge-primary badge-outline" />
                    @endscope

                    @scope('cell_priority', $row)
                        @php
                            $priorityClass = match ($row['priority_key'] ?? 'low') {
                                'high' => 'badge-error',
                                'medium' => 'badge-warning',
                                'low' => 'badge-success',
                                default => 'badge-ghost',
                            };
                        @endphp
                        <x-mary-badge :value="$row['priority'] ?? 'Low'" :class="$priorityClass . ' text-white'" />
                    @endscope
                </x-mary-table>

                <div class="mt-4 text-center">
                    <x-mary-button label="Go to Ticket Review" link="{{ route('gso.ticket-review') }}"
                        class="btn-primary" icon-right="o-arrow-right" wire:navigate />
                </div>
            @else
                <div class="text-center py-12">
                    <x-mary-icon name="o-inbox" class="w-16 h-16 mx-auto mb-3 text-gray-300" />
                    <p class="text-gray-500 font-medium">No approval data to display</p>
                    <p class="text-sm text-gray-400 mt-1">New tickets will appear here once submitted.</p>
                </div>
            @endif
        </x-mary-card>

        <div class="mt-6" wire:loading.class="opacity-50" wire:target="refreshData">
            <h2 class="text-xl font-bold font-heading mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('gso.ticket-review') }}" wire:navigate
                    class="block hover:shadow-lg transition-shadow border-2 border-transparent hover:border-secondary duration-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-secondary focus:ring-offset-2">
                    <div class="text-center py-4">
                        <x-mary-icon name="o-clipboard-document-check"
                            class="w-12 h-12 mx-auto mb-3 text-secondary" />
                        <h3 class="font-semibold">Ticket Review</h3>
                        <p class="text-xs text-gray-500 mt-1">Process office approvals</p>
                    </div>
                </a>

                <a href="{{ route('gso.calendar') }}" wire:navigate
                    class="block hover:shadow-lg transition-shadow border-2 border-transparent hover:border-accent duration-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2">
                    <div class="text-center py-4">
                        <x-mary-icon name="o-calendar-days" class="w-12 h-12 mx-auto mb-3 text-accent" />
                        <h3 class="font-semibold">Event Calendar</h3>
                        <p class="text-xs text-gray-500 mt-1">View schedule</p>
                    </div>
                </a>

                <a href="{{ route('gso.reports') }}" wire:navigate
                    class="block hover:shadow-lg transition-shadow border-2 border-transparent hover:border-info duration-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-info focus:ring-offset-2">
                    <div class="text-center py-4">
                        <x-mary-icon name="o-document-chart-bar" class="w-12 h-12 mx-auto mb-3 text-info" />
                        <h3 class="font-semibold">Reports</h3>
                        <p class="text-xs text-gray-500 mt-1">Download insights</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
