{{-- Single root element wrapper for Livewire --}}
<div>
    @php
    // Cache computed properties to prevent duplicate cache queries
    $stats = $this->stats;
    $recentTickets = $this->recentTickets;
    $pendingApprovals = $this->pendingApprovals;
    $upcomingEvents = $this->upcomingEvents;
    @endphp

    <div class="p-6 space-y-6">
        <!-- Enhanced Header with Breadcrumb and Actions -->
        @persist('dashboard-header')
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold font-heading text-primary flex items-center gap-2">
                    <x-mary-icon name="o-squares-2x2" class="w-8 h-8" />
                    Dashboard Overview
                </h1>
                <p class="text-sm text-gray-600 mt-1">
                    Welcome back! Here's what's happening with event requests.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Last Updated Indicator -->
                <div class="text-xs text-gray-500 hidden md:block">
                    Last updated: <span class="font-medium">{{ now()->format('h:i A') }}</span>
                </div>

                <x-mary-button icon="o-arrow-path" class="btn-primary btn-sm" wire:click="refreshData">
                    <span wire:loading.remove wire:target="refreshData">Refresh</span>
                    <span wire:loading wire:target="refreshData">
                        <span class="loading loading-spinner loading-xs"></span>
                        Refreshing...
                    </span>
                </x-mary-button>
            </div>
        </div>
        @endpersist

        <!-- Statistics Cards with Progress Indicators -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
            wire:loading.class="opacity-50 pointer-events-none" wire:target="refreshData">

            <!-- Pending Requests with Badge -->
            <x-mary-card class="hover:shadow-lg transition-all duration-200 border-l-4 border-l-warning">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <x-mary-icon name="o-clock" class="w-5 h-5 text-warning" />
                            <p class="text-sm font-medium text-gray-600">Pending Requests</p>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['pending']) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Awaiting your review</p>
                    </div>
                    <div class="avatar placeholder">
                        <div class="bg-warning/10 text-warning rounded-full w-12 h-12 flex items-center justify-center">
                            <span class="text-xl">{{ $stats['pending'] }}</span>
                        </div>
                    </div>
                </div>
                @if ($stats['pending'] > 0)
                <div class="mt-3 pt-3 border-t">
                    <x-mary-button label="Review Now" icon-right="o-arrow-right"
                        class="btn-warning btn-sm btn-block" link="/admin/tickets?status=pending"
                        wire:navigate />
                </div>
                @endif
            </x-mary-card>

            <!-- Forwarded to Offices -->
            <x-mary-card class="hover:shadow-lg transition-all duration-200 border-l-4 border-l-info">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <x-mary-icon name="o-paper-airplane" class="w-5 h-5 text-info" />
                            <p class="text-sm font-medium text-gray-600">Forwarded</p>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">
                            {{ number_format($stats['forwarded']) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Sent to other offices</p>
                    </div>
                    <div class="radial-progress text-info bg-info/10"
                        style="--value:{{ min(100, ($stats['forwarded'] / max(1, $stats['pending'] + $stats['forwarded'])) * 100) }}; --size:3rem; --thickness: 4px;">
                        <span
                            class="text-xs">{{ round(($stats['forwarded'] / max(1, $stats['pending'] + $stats['forwarded'])) * 100) }}%</span>
                    </div>
                </div>
            </x-mary-card>

            <!-- Approved Events with Trend -->
            <x-mary-card class="hover:shadow-lg transition-all duration-200 border-l-4 border-l-success">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <x-mary-icon name="o-check-circle" class="w-5 h-5 text-success" />
                            <p class="text-sm font-medium text-gray-600">Approved</p>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">
                            {{ number_format($stats['approved']) }}
                        </p>
                        <div class="flex items-center gap-1 mt-1">
                            <x-mary-icon name="o-arrow-trending-up" class="w-3 h-3 text-success" />
                            <p class="text-xs text-success">All time</p>
                        </div>
                    </div>
                    <div class="avatar placeholder">
                        <div class="bg-success/10 text-success rounded-full w-12 h-12">
                            <x-mary-icon name="o-check-badge" class="w-6 h-6" />
                        </div>
                    </div>
                </div>
            </x-mary-card>

            <!-- This Month with Calendar -->
            <x-mary-card class="hover:shadow-lg transition-all duration-200 border-l-4 border-l-primary">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <x-mary-icon name="o-calendar-days" class="w-5 h-5 text-primary" />
                            <p class="text-sm font-medium text-gray-600">This Month</p>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">
                            {{ number_format($stats['thisMonthTickets']) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ now()->format('F Y') }}</p>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-primary/20">{{ now()->format('d') }}</div>
                        <div class="text-xs text-gray-500">{{ now()->format('M') }}</div>
                    </div>
                </div>
            </x-mary-card>
        </div>

        <!-- Secondary Stats with Better Visualization -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-mary-card class="hover:shadow-lg transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">For Revision Requests</p>
                        <p class="text-2xl font-bold text-error">{{ number_format($stats['for_revision']) }}
                        </p>
                        <progress class="progress progress-error w-full h-1 mt-2"
                            value="{{ $stats['for_revision'] }}"
                            max="{{ max(1, $stats['for_revision'] + $stats['approved']) }}"></progress>
                    </div>
                    <div class="avatar placeholder">
                        <div class="bg-error/10 text-error rounded-lg w-12 h-12 flex items-center justify-center">
                            <x-mary-icon name="o-x-circle" class="w-6 h-6" />
                        </div>
                    </div>
                </div>
            </x-mary-card>

            <x-mary-card class="hover:shadow-lg transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Active Organizations</p>
                        <p class="text-2xl font-bold text-secondary">
                            {{ number_format($stats['totalOrganizations']) }}
                        </p>
                        <div class="flex items-center gap-1 mt-2">
                            <div class="badge badge-secondary badge-sm">Active</div>
                        </div>
                    </div>
                    <div class="avatar placeholder">
                        <div class="bg-secondary/10 text-secondary rounded-lg w-12 h-12 flex items-center justify-center">
                            <x-mary-icon name="o-user-group" class="w-6 h-6 text-secondary" />
                        </div>
                    </div>
                </div>
            </x-mary-card>

            <!-- Average Processing Time -->
            <x-mary-card class="hover:shadow-lg transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Avg. Processing Time</p>
                        <p class="text-2xl font-bold text-accent">2.3 days</p>
                        <div class="flex items-center gap-1 mt-2">
                            <x-mary-icon name="o-arrow-trending-down" class="w-3 h-3 text-success" />
                            <span class="text-xs text-success">15% faster</span>
                        </div>
                    </div>
                    <div class="avatar placeholder">
                        <div class="bg-accent/10 text-accent rounded-lg w-12 h-12">
                            <x-mary-icon name="o-bolt" class="w-6 h-6" />
                        </div>
                    </div>
                </div>
            </x-mary-card>
        </div>

        <!-- Tabbed Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Pending Approvals with Tabs -->
            <div class="col-span-1 lg:col-span-2">
                <x-mary-card class="shadow-md" title="Action Required"
                    subtitle="Review and process these requests">
                    <x-slot:menu>
                        <div class="flex gap-2">
                            <div role="tablist" class="tabs tabs-boxed tabs-sm">
                                <a role="tab" class="tab tab-active">Pending</a>
                                <a role="tab" class="tab">Urgent</a>
                            </div>
                            <x-mary-button icon="o-arrow-right" link="/admin/tickets"
                                class="btn-sm btn-ghost" label="View All" wire:navigate />
                        </div>
                    </x-slot:menu>

                    @if (count($pendingApprovals) > 0)
                    <div class="space-y-2">
                        @foreach ($pendingApprovals as $index => $approval)
                        <div class="p-4 bg-base-100 hover:bg-base-200 rounded-lg border border-base-300 transition-colors cursor-pointer"
                            wire:key="pending-{{ $approval['id'] }}"
                            onclick="window.location.href='/admin/ticket-review?ticket={{ $approval['id'] }}'">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <!-- Priority Indicator -->
                                    @if ($index < 3)
                                        <div class="badge badge-error badge-xs mb-2">High Priority
                                </div>
                                @endif

                                <div class="flex items-center gap-2 mb-2">
                                    <x-mary-badge value="{{ $approval['ticket_number'] }}"
                                        class="badge-primary badge-sm" />
                                    <h4 class="font-semibold text-sm truncate">
                                        {{ $approval['title'] }}
                                    </h4>
                                </div>

                                <div
                                    class="flex flex-wrap items-center gap-3 text-xs text-gray-600">
                                    <div class="flex items-center gap-1">
                                        <x-mary-icon name="o-building-office" class="w-3 h-3" />
                                        <span>{{ $approval['organization'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <x-mary-icon name="o-clock" class="w-3 h-3" />
                                        <span>{{ $approval['submitted'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <x-mary-button icon="o-eye" class="btn-sm btn-primary" />
                            </div>
                        </div>
                    </div>
                    @endforeach
            </div>

            <!-- Pagination Indicator -->
            @if (count($pendingApprovals) > 5)
            <div class="text-center mt-4 pt-4 border-t">
                <p class="text-sm text-gray-500">
                    Showing 5 of {{ count($pendingApprovals) }} pending requests
                </p>
            </div>
            @endif
            @else
            <div class="text-center py-16">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-success/10 mb-4">
                    <x-mary-icon name="o-check-circle" class="w-10 h-10 text-success" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">All Caught Up!</h3>
                <p class="text-sm text-gray-500">No pending approvals at the moment.</p>
            </div>
            @endif
            </x-mary-card>

            <!-- Recent Activity Timeline -->
            <x-mary-card class="shadow-md mt-6" title="Recent Activity"
                subtitle="Latest updates and changes">
                <div class="space-y-4">
                    <!-- Timeline items would go here -->
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-success flex items-center justify-center">
                                <x-mary-icon name="o-check" class="w-4 h-4 text-white" />
                            </div>
                            <div class="w-px h-full bg-base-300 mt-2"></div>
                        </div>
                        <div class="flex-1 pb-4">
                            <p class="text-sm font-medium">Event Approved</p>
                            <p class="text-xs text-gray-500">Annual Sports Fest 2024 was approved</p>
                            <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
                        </div>
                    </div>
                </div>
            </x-mary-card>
        </div>

        <!-- Right Column: Upcoming Events -->
        <div class="col-span-1">
            <x-mary-card class="shadow-md sticky top-6" title="Upcoming Events" subtitle="Next 30 days">
                <x-slot:menu>
                    <x-mary-button icon="o-calendar" link="/admin/calendar" class="btn-sm btn-ghost"
                        wire:navigate />
                </x-slot:menu>

                @if (count($upcomingEvents) > 0)
                <div class="space-y-3">
                    @foreach ($upcomingEvents as $index => $event)
                    <div class="group p-3 bg-gradient-to-br from-base-200 to-base-100 rounded-lg border border-base-300 hover:shadow-md transition-all"
                        wire:key="upcoming-{{ $index }}">
                        <!-- Date Badge -->
                        <div class="flex items-start gap-3">
                            <div
                                class="text-center bg-primary text-primary-content rounded-lg p-2 min-w-[48px]">
                                <div class="text-xs font-medium">
                                    {{ \Carbon\Carbon::parse($event['date'])->format('M') }}
                                </div>
                                <div class="text-xl font-bold">
                                    {{ \Carbon\Carbon::parse($event['date'])->format('d') }}
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-sm mb-1 truncate">
                                    {{ $event['title'] }}
                                </h4>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1 text-xs text-gray-600">
                                        <x-mary-icon name="o-user-group" class="w-3 h-3" />
                                        <span class="truncate">{{ $event['organization'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-xs text-gray-600">
                                        <x-mary-icon name="o-map-pin" class="w-3 h-3" />
                                        <span class="truncate">{{ $event['venue'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t">
                    <x-mary-button label="View Full Calendar" icon-right="o-arrow-right"
                        class="btn-sm btn-block btn-outline" link="/admin/calendar" wire:navigate />
                </div>
                @else
                <div class="text-center py-12">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-base-200 mb-3">
                        <x-mary-icon name="o-calendar-days" class="w-8 h-8 text-gray-400" />
                    </div>
                    <p class="text-sm text-gray-500 font-medium">No upcoming events</p>
                    <p class="text-xs text-gray-400 mt-1">Approved events will appear here</p>
                </div>
                @endif
            </x-mary-card>

            <!-- Quick Stats Mini Card -->
            <x-mary-card class="shadow-md mt-4" title="Today's Summary">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">New Requests</span>
                        <span class="font-bold">5</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Processed</span>
                        <span class="font-bold text-success">12</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Pending Review</span>
                        <span class="font-bold text-warning">{{ $stats['pending'] }}</span>
                    </div>
                </div>
            </x-mary-card>
        </div>
    </div>

    <!-- Enhanced Quick Actions with Categories -->
    @persist('quick-actions')
    <x-mary-card class="shadow-md">
        <div class="mb-4">
            <h2 class="text-xl font-bold font-heading">Quick Actions</h2>
            <p class="text-sm text-gray-500">Frequently used features</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Review Tickets -->
            <a href="/admin/tickets" wire:navigate
                class="group p-6 bg-gradient-to-br from-primary/5 to-primary/10 hover:from-primary/10 hover:to-primary/20 rounded-xl border-2 border-transparent hover:border-primary transition-all duration-200 cursor-pointer">
                <div class="text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary/10 group-hover:bg-primary group-hover:scale-110 transition-all mb-3">
                        <x-mary-icon name="o-ticket"
                            class="w-8 h-8 text-primary group-hover:text-white" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Review Tickets</h3>
                    <p class="text-xs text-gray-500">Manage event requests</p>
                    @if ($stats['pending'] > 0)
                    <div class="badge badge-warning badge-sm mt-2">{{ $stats['pending'] }}
                        pending
                    </div>
                    @endif
                </div>
            </a>

            <!-- Approvals -->
            <a href="/admin/approvals" wire:navigate
                class="group p-6 bg-gradient-to-br from-secondary/5 to-secondary/10 hover:from-secondary/10 hover:to-secondary/20 rounded-xl border-2 border-transparent hover:border-secondary transition-all duration-200 cursor-pointer">
                <div class="text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-secondary/10 group-hover:bg-secondary group-hover:scale-110 transition-all mb-3">
                        <x-mary-icon name="o-clipboard-document-check"
                            class="w-8 h-8 text-secondary group-hover:text-white" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Approvals</h3>
                    <p class="text-xs text-gray-500">Process workflows</p>
                </div>
            </a>

            <!-- Calendar -->
            <a href="/admin/calendar" wire:navigate
                class="group p-6 bg-gradient-to-br from-accent/5 to-accent/10 hover:from-accent/10 hover:to-accent/20 rounded-xl border-2 border-transparent hover:border-accent transition-all duration-200 cursor-pointer">
                <div class="text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-accent/10 group-hover:bg-accent group-hover:scale-110 transition-all mb-3">
                        <x-mary-icon name="o-calendar-days"
                            class="w-8 h-8 text-accent group-hover:text-white" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Event Calendar</h3>
                    <p class="text-xs text-gray-500">View schedule</p>
                </div>
            </a>

            <!-- Reports -->
            <a href="/admin/reports" wire:navigate
                class="group p-6 bg-gradient-to-br from-info/5 to-info/10 hover:from-info/10 hover:to-info/20 rounded-xl border-2 border-transparent hover:border-info transition-all duration-200 cursor-pointer">
                <div class="text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-info/10 group-hover:bg-info group-hover:scale-110 transition-all mb-3">
                        <x-mary-icon name="o-document-chart-bar"
                            class="w-8 h-8 text-info group-hover:text-white" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Reports</h3>
                    <p class="text-xs text-gray-500">Generate insights</p>
                </div>
            </a>
        </div>
    </x-mary-card>
    @endpersist
</div>
</div>