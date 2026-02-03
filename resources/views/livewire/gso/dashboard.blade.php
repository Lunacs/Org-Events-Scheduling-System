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
                <h1 class="text-3xl font-bold font-heading text-primary dark:text-primary">GSO Dashboard</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">General Services Office - Event Management
                    System</p>
            </div>
            <div class="hidden md:block">
                <x-mary-button icon="o-arrow-path" class="btn-primary" wire:click="refreshData">
                    <span wire:loading.remove wire:target="refreshData">Refresh Data</span>
                    <span wire:loading wire:target="refreshData">Refreshing...</span>
                </x-mary-button>
            </div>
        </div>

        <div class="flex items-center justify-center mb-6">
            <div class="md:hidden">
                <x-mary-button icon="o-arrow-path" class="btn-primary" wire:click="refreshData">
                    <span wire:loading.remove wire:target="refreshData">Refresh Data</span>
                    <span wire:loading wire:target="refreshData">Refreshing...</span>
                </x-mary-button>
            </div>
        </div>

        <!-- Modern Statistics Cards -->
        <div id="overview-metrics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6"
            wire:loading.class="opacity-50" wire:target="refreshData">

            <!-- Pending Approvals -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Pending Approvals</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format(data_get($stats, 'pending', 0)) }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Awaiting review</p>
                    </div>
                    <div
                        class="w-14 h-14 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-clock" class="w-7 h-7 text-amber-600 dark:text-amber-500" />
                    </div>
                </div>
            </div>

            <!-- Approved Today -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Approved Today</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format(data_get($stats, 'approvedToday', 0)) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Processed today</p>
                    </div>
                    <div
                        class="w-14 h-14 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-check-circle" class="w-7 h-7 text-emerald-600 dark:text-emerald-500" />
                    </div>
                </div>
            </div>

            <!-- For Revision Today -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">For Revision</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format(data_get($stats, 'for_revisionToday', 0)) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Needs correction</p>
                    </div>
                    <div class="w-14 h-14 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-x-circle" class="w-7 h-7 text-rose-600 dark:text-rose-500" />
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Upcoming Events</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format(data_get($stats, 'upcomingEvents', 0)) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Scheduled approved</p>
                    </div>
                    <div
                        class="w-14 h-14 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-calendar-days" class="w-7 h-7 text-violet-600 dark:text-violet-500" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats with Modern Design -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" wire:loading.class="opacity-50"
            wire:target="refreshData">

            <!-- High Priority Items -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">High Priority</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($pendingApprovalRows->where('priority_key', 'high')->count()) }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Urgent requests</p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6 text-amber-600 dark:text-amber-500" />
                    </div>
                </div>
            </div>

            <!-- Tickets In Queue -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Tickets In Queue</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($ticketsInQueue ?? 0) }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Total in pipeline</p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-ticket" class="w-6 h-6 text-indigo-600 dark:text-indigo-500" />
                    </div>
                </div>
            </div>
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
                            <div class="p-3 bg-base-100 rounded-lg border border-base-200"
                                wire:key="pending-{{ $approval['approval_id'] }}">
                                <!-- Mobile Layout-->
                                <div class="md:hidden space-y-2">
                                    <!-- Request Type -->
                                    <div class="flex items-start mb-2">
                                        <x-mary-badge :value="$approval['request_type']"
                                            class="{{ $approval['request_type_badge_class'] }} badge-sm text-white whitespace-normal h-auto" />
                                    </div>

                                    <!-- Event Title and Ticket # -->
                                    <div class="flex flex-col items-start gap-2">
                                        <x-mary-badge :value="$approval['ticket_number']" class="badge-ghost badge-sm" />
                                        <span class="font-semibold">{{ $approval['event_title'] }}</span>
                                    </div>

                                    <!-- Organization -->
                                    <div class="flex items-center gap-2 text-sm">
                                        <x-mary-icon name="o-building-office" class="w-4 h-4" />
                                        <span>{{ $approval['organization'] }}</span>
                                    </div>

                                    <!-- Event Date -->
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="text-gray-500">{{ $approval['event_date'] }}</span>
                                    </div>
                                </div>

                                <!-- Tablet/Desktop Layout -->
                                <div class="hidden md:block">
                                    <x-mary-list-item :item="$approval" no-separator>
                                        <x-slot:value>
                                            <div class="flex flex-col gap-2">
                                                <x-mary-badge :value="$approval['request_type']"
                                                    class="{{ $approval['request_type_badge_class'] }} badge-sm" />
                                                <div class="flex items-center gap-2">
                                                    <x-mary-badge :value="$approval['ticket_number']" class="badge-ghost badge-sm" />
                                                    <span class="font-semibold">{{ $approval['event_title'] }}</span>
                                                </div>
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
                                    </x-mary-list-item>
                                </div>
                            </div>
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

            <x-mary-card id="recent-activity-section" title="Recent Activity"
                subtitle="Latest actions from your team" class="shadow-md">

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
                                        <p class="font-medium text-sm dark:text-white">
                                            {{ $activity['action'] ?? 'Activity' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $activity['details'] ?? 'Details unavailable.' }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                            {{ $activity['time_ago'] ?? 'Just now' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-mary-icon name="o-document"
                            class="w-8 h-8 mx-auto mb-2 text-gray-400 dark:text-gray-500" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">Activity logs will appear here once actions
                            are taken.</p>
                    </div>
                @endif
            </x-mary-card>
        </div>

        <x-mary-card id="approval-snapshot-section" title="Approval Snapshot" subtitle="Quick view of recent tickets"
            class="shadow-md" wire:loading.class="opacity-50" wire:target="refreshData">

            @if ($approvalSnapshotRows->count() > 0)
                <!-- Mobile Layout (Card-based) -->
                <div class="md:hidden space-y-3">
                    @foreach ($approvalSnapshotRows as $row)
                        <div class="p-4 bg-base-100 rounded-lg border border-base-200"
                            wire:key="snapshot-{{ $row['ticket_number'] }}">
                            <div class="space-y-3">
                                <!-- Ticket Number -->
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Ticket</span>
                                    <div class="mt-1">
                                        <x-mary-badge :value="$row['ticket_number']" class="badge-primary badge-outline" />
                                    </div>
                                </div>

                                <!-- Event Title -->
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Event</span>
                                    <p class="font-medium mt-1 dark:text-white">{{ $row['event_title'] }}</p>
                                </div>

                                <!-- Organization -->
                                <div>
                                    <span
                                        class="text-xs text-gray-500 dark:text-gray-400 uppercase">Organization</span>
                                    <p class="mt-1 dark:text-gray-300">{{ $row['organization'] }}</p>
                                </div>

                                <!-- Event Date -->
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Event Date</span>
                                    <p class="mt-1 dark:text-gray-300">{{ $row['event_date'] }}</p>
                                </div>

                                <!-- Priority -->
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Priority</span>
                                    <div class="mt-1">
                                        @php
                                            $priorityClass = match ($row['priority_key'] ?? 'low') {
                                                'high' => 'badge-error',
                                                'medium' => 'badge-warning',
                                                'low' => 'badge-success',
                                                default => 'badge-ghost',
                                            };
                                        @endphp
                                        <x-mary-badge :value="$row['priority'] ?? 'Low'" :class="$priorityClass . ' text-white'" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Tablet/Desktop Layout (Table) -->
                <div class="hidden md:block">
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
                </div>
                <div class="mt-4 text-center">
                    <x-mary-button label="Go to Ticket Review" link="{{ route('gso.ticket-review') }}"
                        class="btn-primary" icon-right="o-arrow-right" wire:navigate />
                </div>
            @else
                <div class="text-center py-12">
                    <x-mary-icon name="o-inbox" class="w-16 h-16 mx-auto mb-3 text-gray-300 dark:text-gray-600" />
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No approval data to display</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">New tickets will appear here once
                        submitted.</p>
                </div>
            @endif
        </x-mary-card>

        <!-- Modern Quick Actions -->
        <div class="mt-6" wire:loading.class="opacity-50" wire:target="refreshData">
            <div class="mb-4">
                <h2 class="text-xl font-bold font-heading dark:text-white">Quick Actions</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Frequently used features</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Ticket Review -->
                <a href="{{ route('gso.ticket-review') }}" wire:navigate
                    class="sm:p-5 group max-md:pt-1 md:p-6 bg-gradient-to-br from-indigo/5 to-indigo/10 dark:from-indigo/10 dark:to-indigo/20 hover:from-indigo/10 hover:to-indigo/20 rounded-xl border-2 border-transparent hover:border-blue-800 transition-all duration-200 cursor-pointer">
                    <div class="text-center">
                        <div
                            class="w-14 h-14 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-800 group-hover:scale-110 transition-all">
                            <x-mary-icon name="o-clipboard-document-check"
                                class="w-7 h-7 text-indigo-600 dark:text-indigo-500 group-hover:text-white" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Ticket Review</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Process office approvals</p>
                    </div>
                </a>

                <!-- Event Calendar -->
                <a href="{{ route('gso.calendar') }}" wire:navigate
                    class="sm:p-5 group max-md:pt-1 md:p-6 bg-gradient-to-br from-indigo/5 to-indigo/10 dark:from-indigo/10 dark:to-indigo/20 hover:from-indigo/10 hover:to-indigo/20 rounded-xl border-2 border-transparent hover:border-violet-800 transition-all duration-200 cursor-pointer">
                    <div class="text-center">
                        <div
                            class="w-14 h-14 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center mx-auto mb-3 group-hover:bg-violet-800 group-hover:scale-110 transition-all">
                            <x-mary-icon name="o-calendar-days"
                                class="w-7 h-7 text-violet-600 dark:text-violet-500 group-hover:text-white" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Event Calendar</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">View schedule</p>
                    </div>
                </a>

                <!-- Reports -->
                <a href="{{ route('gso.reports') }}" wire:navigate
                    class="sm:p-5 group max-md:pt-1 md:p-6 bg-gradient-to-br from-indigo/5 to-indigo/10 dark:from-indigo/10 dark:to-indigo/20 hover:from-indigo/10 hover:to-indigo/20 rounded-xl border-2 border-transparent hover:border-sky-800 transition-all duration-200 cursor-pointer">
                    <div class="text-center">
                        <div
                            class="w-14 h-14 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center mx-auto mb-3 group-hover:bg-sky-800 group-hover:scale-110 transition-all">
                            <x-mary-icon name="o-document-chart-bar"
                                class="w-7 h-7 text-sky-600 dark:text-sky-500 group-hover:text-white" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Reports</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Download insights</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
