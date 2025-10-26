<div>
    <div class="p-6">
        <!-- Header -->
        @persist('dashboard-header')
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold font-heading text-primary">OSA Dashboard</h1>
                    <p class="text-sm text-gray-600 mt-1">Office of Student Affairs - Event Management System</p>
                </div>
                <x-mary-button icon="o-arrow-path" class="btn-primary" wire:click="refreshData">
                    <span wire:loading.remove wire:target="refreshData">Refresh Data</span>
                    <span wire:loading wire:target="refreshData">Refreshing...</span>
                </x-mary-button>
            </div>
        @endpersist

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" wire:loading.class="opacity-50"
            wire:target="refreshData">
            <x-mary-stat title="Pending Requests" :value="number_format($this->stats['pending'])" icon="o-clock"
                class="bg-linear-to-br from-warning/10 to-warning/5 border border-warning/20"
                tooltip="Requests awaiting OSA review" />
            <x-mary-stat title="Forwarded to Offices" :value="number_format($this->stats['forwarded'])" icon="o-paper-airplane"
                class="bg-linear-to-br from-info/10 to-info/5 border border-info/20"
                tooltip="Requests sent to GSO/other offices" />
            <x-mary-stat title="Approved Events" :value="number_format($this->stats['approved'])" icon="o-check-circle"
                class="bg-linear-to-br from-success/10 to-success/5 border border-success/20"
                tooltip="Successfully approved events" />
            <x-mary-stat title="This Month" :value="number_format($this->stats['thisMonthTickets'])" icon="o-calendar-days"
                class="bg-linear-to-br from-primary/10 to-primary/5 border border-primary/20"
                tooltip="Requests submitted this month" />
        </div>

        <!-- Additional Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" wire:loading.class="opacity-50"
            wire:target="refreshData">
            <x-mary-card class="border-l-4 border-l-error shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Rejected Requests</p>
                        <p class="text-3xl font-bold text-error">{{ number_format($this->stats['rejected']) }}</p>
                    </div>
                    <x-mary-icon name="o-x-circle" class="w-12 h-12 text-error opacity-20" />
                </div>
            </x-mary-card>

            <x-mary-card class="border-l-4 border-l-secondary shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Active Organizations</p>
                        <p class="text-3xl font-bold text-secondary">
                            {{ number_format($this->stats['totalOrganizations']) }}
                        </p>
                    </div>
                    <x-mary-icon name="o-user-group" class="w-12 h-12 text-secondary opacity-20" />
                </div>
            </x-mary-card>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Pending Approvals - Takes 2 columns -->
            <x-mary-card title="Pending Approvals" subtitle="Requests requiring your attention"
                class="col-span-1 lg:col-span-2 shadow-md">
                <x-slot:menu>
                    <x-mary-button icon="o-eye" link="/admin/tickets" class="btn-sm btn-ghost" label="View All" />
                </x-slot:menu>

                @if (count($this->pendingApprovals) > 0)
                    <div class="space-y-3">
                        @foreach ($this->pendingApprovals as $approval)
                            <x-mary-list-item :item="$approval" no-separator wire:key="pending-{{ $approval['id'] }}">
                                <x-slot:value>
                                    <div class="flex items-center gap-2">
                                        <x-mary-badge value="{{ $approval['ticket_number'] }}"
                                            class="badge-ghost badge-sm" />
                                        <span class="font-semibold">{{ $approval['title'] }}</span>
                                    </div>
                                </x-slot:value>
                                <x-slot:sub-value>
                                    <div class="flex items-center gap-2 text-sm">
                                        <x-mary-icon name="o-building-office" class="w-4 h-4" />
                                        <span>{{ $approval['organization'] }}</span>
                                        <span class="text-gray-400">•</span>
                                        <span class="text-gray-500">{{ $approval['submitted'] }}</span>
                                    </div>
                                </x-slot:sub-value>
                                <x-slot:actions>
                                    <x-mary-button icon="o-eye"
                                        link="/admin/ticket-review?ticket={{ $approval['id'] }}"
                                        class="btn-sm btn-primary" />
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

            <!-- Upcoming Events -->
            <x-mary-card title="Upcoming Events" subtitle="Approved & scheduled" shadow class="shadow-md">
                <x-slot:menu>
                    <x-mary-button icon="o-calendar" link="/admin/calendar" class="btn-sm btn-ghost" />
                </x-slot:menu>

                @if (count($this->upcomingEvents) > 0)
                    <div class="space-y-3">
                        @foreach ($this->upcomingEvents as $index => $event)
                            <div class="p-3 bg-base-200 rounded-lg border border-base-300"
                                wire:key="upcoming-{{ $index }}">
                                <h4 class="font-semibold text-sm mb-1">{{ $event['title'] }}</h4>
                                <div class="flex items-center gap-2 text-xs text-gray-600">
                                    <x-mary-icon name="o-user-group" class="w-3 h-3" />
                                    <span>{{ $event['organization'] }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-600 mt-1">
                                    <x-mary-icon name="o-calendar" class="w-3 h-3" />
                                    <span>{{ $event['date'] }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-600 mt-1">
                                    <x-mary-icon name="o-map-pin" class="w-3 h-3" />
                                    <span>{{ $event['venue'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-mary-icon name="o-calendar-days" class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                        <p class="text-sm text-gray-500">No upcoming events</p>
                    </div>
                @endif
            </x-mary-card>
        </div>

        <!-- Recent Tickets Table -->
        <x-mary-card title="Recent Event Requests" subtitle="Latest submissions from student organizations"
            class="shadow-md">
            <x-slot:menu>
                <div class="flex gap-2">
                    <x-mary-button icon="o-funnel" class="btn-sm btn-ghost" label="Filter" />
                    <x-mary-button icon="o-arrow-down-tray" class="btn-sm btn-ghost" label="Export" />
                </div>
            </x-slot:menu>

            @if (count($this->recentTickets) > 0)
                <x-mary-table :headers="$this->headers" :rows="$this->recentTickets" striped>
                    @scope('cell_ticket_number', $row)
                        <x-mary-badge :value="$row['ticket_number']" class="badge-primary badge-outline" />
                    @endscope

                    @scope('cell_title', $row)
                        <div class="font-medium">{{ $row['title'] }}</div>
                    @endscope

                    @scope('cell_status', $row)
                        <x-mary-badge :value="$row['status']" :class="match ($row['status']) {
                            'Pending' => 'badge-warning',
                            'Approved' => 'badge-success',
                            'Rejected' => 'badge-error',
                            'Forwarded' => 'badge-info',
                            default => 'badge-ghost',
                        }" />
                    @endscope
                </x-mary-table>

                <div class="mt-4 text-center">
                    <x-mary-button label="View All Tickets" link="/admin/tickets" class="btn-primary"
                        icon-right="o-arrow-right" />
                </div>
            @else
                <div class="text-center py-12">
                    <x-mary-icon name="o-inbox" class="w-16 h-16 mx-auto mb-3 text-gray-300" />
                    <p class="text-gray-500 font-medium">No recent tickets</p>
                    <p class="text-sm text-gray-400 mt-1">Event requests will appear here</p>
                </div>
            @endif
        </x-mary-card>

        <!-- Quick Actions -->
        @persist('quick-actions')
            <div class="mt-6">
                <h2 class="text-xl font-bold font-heading mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-mary-card
                        class="hover:shadow-lg transition-shadow cursor-pointer border-2 border-transparent hover:border-primary"
                        link="/admin/tickets">
                        <div class="text-center py-4">
                            <x-mary-icon name="o-ticket" class="w-12 h-12 mx-auto mb-3 text-primary" />
                            <h3 class="font-semibold">Review Tickets</h3>
                            <p class="text-xs text-gray-500 mt-1">Manage event requests</p>
                        </div>
                    </x-mary-card>

                    <x-mary-card
                        class="hover:shadow-lg transition-shadow cursor-pointer border-2 border-transparent hover:border-secondary"
                        link="/admin/approvals">
                        <div class="text-center py-4">
                            <x-mary-icon name="o-clipboard-document-check"
                                class="w-12 h-12 mx-auto mb-3 text-secondary" />
                            <h3 class="font-semibold">Approvals</h3>
                            <p class="text-xs text-gray-500 mt-1">Process approvals</p>
                        </div>
                    </x-mary-card>

                    <x-mary-card
                        class="hover:shadow-lg transition-shadow cursor-pointer border-2 border-transparent hover:border-accent"
                        link="/admin/calendar">
                        <div class="text-center py-4">
                            <x-mary-icon name="o-calendar-days" class="w-12 h-12 mx-auto mb-3 text-accent" />
                            <h3 class="font-semibold">Event Calendar</h3>
                            <p class="text-xs text-gray-500 mt-1">View scheduled events</p>
                        </div>
                    </x-mary-card>

                    <x-mary-card
                        class="hover:shadow-lg transition-shadow cursor-pointer border-2 border-transparent hover:border-info"
                        link="/admin/reports">
                        <div class="text-center py-4">
                            <x-mary-icon name="o-document-chart-bar" class="w-12 h-12 mx-auto mb-3 text-info" />
                            <h3 class="font-semibold">Reports</h3>
                            <p class="text-xs text-gray-500 mt-1">Generate reports</p>
                        </div>
                    </x-mary-card>
                </div>
            </div>
        @endpersist
    </div>
</div>
