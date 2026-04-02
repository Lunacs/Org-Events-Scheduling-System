{{-- Single root element wrapper for Livewire --}}
<div>
    @php
        // Cache computed properties to prevent duplicate cache queries
        $stats = $this->stats;
        $recentTickets = $this->recentTickets;
        $pendingApprovals = $this->pendingApprovals;
        
    @endphp

    <div class="p-6 space-y-6">
        <!-- Enhanced Header with Breadcrumb and Actions -->
        @persist('dashboard-header')
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold font-heading text-primary dark:text-primary flex items-center gap-2">
                        <x-mary-icon name="o-squares-2x2" class="w-8 h-8" />
                        Dashboard Overview
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Welcome back! Here's what's happening with event requests.
                    </p>
                </div>


            </div>
        @endpersist

        <!-- Modern Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Pending Requests -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Pending Requests</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['pending']) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Awaiting your review</p>
                    </div>
                    <div
                        class="w-14 h-14 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-clock" class="w-7 h-7 text-amber-600 dark:text-amber-500" />
                    </div>
                </div>
                {{-- @if ($stats['pending'] > 0)
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <a href="{{ route('osa.ticket-review.index') }}" wire:navigate
                            class="text-sm font-medium text-amber-600 hover:text-amber-700 flex items-center gap-1">
                            Review Now
                            <x-mary-icon name="o-arrow-right" class="w-4 h-4" />
                        </a>
                    </div>
                @endif --}}
            </div>

            <!-- Forwarded to Offices -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Forwarded</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['forwarded']) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Sent to other offices</p>
                    </div>
                    <div class="w-14 h-14 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-paper-airplane" class="w-7 h-7 text-sky-600 dark:text-sky-500" />
                    </div>
                </div>
            </div>

            <!-- Approved Events -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Approved</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['approved']) }}</p>
                        <div class="flex items-center gap-1 mt-1">
                            <x-mary-icon name="o-arrow-trending-up" class="w-3 h-3 text-emerald-500" />
                            <p class="text-xs text-emerald-500 dark:text-emerald-400">All time</p>
                        </div>
                    </div>
                    <div
                        class="w-14 h-14 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-check-circle" class="w-7 h-7 text-emerald-600 dark:text-emerald-500" />
                    </div>
                </div>
            </div>

            <!-- This Month -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">This Month</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['thisMonthTickets']) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ now()->format('F Y') }}</p>
                    </div>
                    <div
                        class="w-14 h-14 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-calendar-days" class="w-7 h-7 text-violet-600 dark:text-violet-500" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats with Modern Design -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- For Revision -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">For Revision</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['for_revision']) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Needs correction</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-x-circle" class="w-6 h-6 text-rose-600 dark:text-rose-500" />
                    </div>
                </div>

                @if ($stats['for_revision'] > 0)
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <a href="{{ route('osa.ticket-review.index', ['statusFilter' => 'for_revision']) }}"
                            wire:navigate
                            class="text-sm font-medium text-amber-600 hover:text-amber-700 flex items-center gap-1">
                            View
                            <x-mary-icon name="o-arrow-right" class="w-4 h-4" />
                        </a>
                    </div>
                @endif
            </div>

            <!-- Active Organizations -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Organizations</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['totalOrganizations']) }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Active accounts</p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-user-group" class="w-6 h-6 text-indigo-600" />
                    </div>
                </div>
            </div>

            <!-- Total Tickets -->
            <div
                class="bg-white dark:bg-base-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Tickets</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['approved'] + $stats['pending'] + $stats['for_revision']) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">All time</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
                        <x-mary-icon name="o-ticket" class="w-6 h-6 text-teal-600 dark:text-teal-500" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Action Required -->
            <div class="col-span-1 lg:col-span-2">
                <div class="bg-white dark:bg-base-200 rounded-2xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Action Required</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Review and process these requests</p>
                        </div>
                        <a href="{{ route('osa.ticket-review.index') }}" wire:navigate
                            class="text-sm font-medium text-primary hover:text-primary/80 flex items-center gap-1">
                            View All
                            <x-mary-icon name="o-arrow-right" class="w-4 h-4" />
                        </a>
                    </div>

                    @if (count($pendingApprovals) > 0)
                        <div class="space-y-3">
                            @foreach ($pendingApprovals as $approval)
                                <a href="/admin/ticket-review/{{ $approval['ticket_number'] }}" wire:navigate
                                    wire:key="pending-{{ $approval['id'] }}"
                                    class="block p-4 bg-gray-50 dark:bg-base-300 hover:bg-gray-100 dark:hover:bg-base-100 rounded-xl border border-gray-100 dark:border-gray-700 transition-all duration-200 hover:shadow-sm">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="badge {{ $approval['status_class'] }} badge-sm">
                                                    {{ $approval['status_label'] }}
                                                </span>
                                                <span
                                                    class="text-xs text-gray-400 dark:text-gray-500 font-mono">{{ $approval['ticket_number'] }}</span>
                                            </div>
                                            <h4 class="font-semibold text-gray-900 dark:text-white truncate mb-1">
                                                {{ $approval['title'] }}
                                            </h4>
                                            <div
                                                class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
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
                                        <x-mary-icon name="o-chevron-right" class="w-5 h-5 text-gray-400" />
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div
                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 mb-4">
                                <x-mary-icon name="o-check-circle"
                                    class="w-8 h-8 text-emerald-600 dark:text-emerald-500" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">All Caught Up!</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No pending approvals at the moment.</p>
                        </div>
                    @endif
                </div>

                <livewire:osa.dashboard.recent-activity defer.bundle />
            </div>

            <!-- Right Column: Upcoming Events + Today's Summary (deferred) -->
            <livewire:osa.dashboard.sidebar defer.bundle />
        </div>

        <!-- Enhanced Quick Actions with Categories -->
        @persist('quick-actions')
            <x-mary-card class="shadow-md">
                <div class="mb-4">
                    <h2 class="text-xl font-bold font-heading">Quick Actions</h2>
                    <p class="text-sm text-gray-500">Frequently used features</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <!-- Review Tickets -->
                    <a href="{{ route('osa.ticket-review.index') }}" wire:navigate
                        class="group max-md:pt-1 md:p-6 bg-gradient-to-br from-primary/5 to-primary/10 dark:from-primary/10 dark:to-primary/20 hover:from-primary/10 hover:to-primary/20 rounded-xl border-2 border-transparent hover:border-primary transition-all duration-200 cursor-pointer">
                        <div class="text-center">
                            <div
                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary/10 group-hover:bg-primary group-hover:scale-110 transition-all mb-3">
                                <x-mary-icon name="o-ticket" class="w-8 h-8 text-primary group-hover:text-white" />
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Review Tickets</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Manage event requests</p>
                            @if ($stats['pending'] > 0)
                                <div class="badge badge-warning badge-sm mt-2">{{ $stats['pending'] }}
                                    pending
                                </div>
                            @endif
                        </div>
                    </a>

                    <!-- Calendar -->
                    <a href="/admin/calendar" wire:navigate
                        class="group max-md:pt-1 md:p-6 bg-gradient-to-br from-accent/5 to-accent/10 dark:from-accent/10 dark:to-accent/20 hover:from-accent/10 hover:to-accent/20 rounded-xl border-2 border-transparent hover:border-accent transition-all duration-200 cursor-pointer">
                        <div class="text-center">
                            <div
                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-accent/10 group-hover:bg-accent group-hover:scale-110 transition-all mb-3">
                                <x-mary-icon name="o-calendar-days" class="w-8 h-8 text-accent group-hover:text-white" />
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Event Calendar</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">View schedule</p>
                        </div>
                    </a>

                    <!-- Reports -->
                    <a href="/admin/reports" wire:navigate
                        class="group max-md:pt-1 md:p-6 bg-gradient-to-br from-info/5 to-info/10 dark:from-info/10 dark:to-info/20 hover:from-info/10 hover:to-info/20 rounded-xl border-2 border-transparent hover:border-info transition-all duration-200 cursor-pointer">
                        <div class="text-center">
                            <div
                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-info/10 group-hover:bg-info group-hover:scale-110 transition-all mb-3">
                                <x-mary-icon name="o-document-chart-bar"
                                    class="w-8 h-8 text-info group-hover:text-white" />
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Reports</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Generate insights</p>
                        </div>
                    </a>
                </div>
            </x-mary-card>
        @endpersist
    </div>
</div>
