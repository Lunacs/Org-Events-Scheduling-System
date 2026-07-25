{{-- Single root element wrapper for Livewire --}}
<div>
    @php
        // Cache computed properties to prevent duplicate cache queries
        $stats = $this->stats;
        $recentTickets = $this->recentTickets;
        $pendingApprovals = $this->pendingApprovals;
    @endphp

    <div class="p-6 space-y-6">
        <!-- Header -->
        @persist('dashboard-header')
            <section
                class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
                <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
                <div class="relative p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-start space-x-4">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/20">
                                <x-ui.icon name="s-squares-2x2" class="w-6 h-6 text-primary" />
                            </span>
                            <div>
                                <h1 class="text-2xl md:text-3xl font-bold font-heading text-base-content">Dashboard Overview</h1>
                                <p class="text-sm text-base-content/70 mt-1">
                                    Welcome back! Here's what's happening with event requests.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endpersist

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.metric-card label="Pending Requests" value="{{ number_format($stats['pending']) }}"
                meta="Awaiting your review" icon="o-clock" color="warning" />

            <x-ui.metric-card label="Forwarded" value="{{ number_format($stats['forwarded']) }}"
                meta="Sent to other offices" icon="o-paper-airplane" color="info" />

            <x-ui.metric-card label="Approved" value="{{ number_format($stats['approved']) }}"
                meta="All time" icon="o-check-circle" color="success" />

            <x-ui.metric-card label="This Month" value="{{ number_format($stats['thisMonthTickets']) }}"
                meta="{{ now()->format('F Y') }}" icon="o-calendar-days" color="accent" />
        </div>

        <!-- Secondary stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-ui.metric-card label="For Revision" value="{{ number_format($stats['for_revision']) }}"
                meta="Needs correction" icon="o-x-circle" color="error"
                :link="$stats['for_revision'] > 0 ? route('osa.ticket-review.index', ['statusFilter' => 'for_revision']) : null" />

            <x-ui.metric-card label="Organizations" value="{{ number_format($stats['totalOrganizations']) }}"
                meta="Active accounts" icon="o-user-group" color="secondary" />

            <x-ui.metric-card label="Total Tickets"
                value="{{ number_format($stats['approved'] + $stats['pending'] + $stats['for_revision']) }}"
                meta="All time" icon="o-ticket" color="primary" />
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Action Required -->
            <div class="col-span-1 lg:col-span-2">
                <x-ui.card title="Action Required" subtitle="Review and process these requests" shadow>
                    <x-slot:menu>
                        <x-ui.button label="View All" icon-right="o-arrow-right"
                            link="{{ route('osa.ticket-review.index') }}" class="btn-sm btn-ghost" wire:navigate />
                    </x-slot:menu>

                    @if (count($pendingApprovals) > 0)
                        <div class="space-y-3">
                            @foreach ($pendingApprovals as $approval)
                                <a href="/admin/ticket-review/{{ $approval['ticket_number'] }}" wire:navigate
                                    wire:key="pending-{{ $approval['id'] }}" class="block">
                                    <x-ui.approval-queue-item title="{{ $approval['title'] }}"
                                        label="{{ $approval['status_label'] }}"
                                        :meta="[
                                            ['icon' => 'o-building-office', 'text' => $approval['organization']],
                                            ['icon' => 'o-hashtag', 'text' => $approval['ticket_number']],
                                        ]" />
                                </a>
                            @endforeach
                        </div>
                    @else
                        <x-ui.empty-state title="All Caught Up!"
                            description="No pending approvals at the moment." icon="o-check-circle"
                            tone="success" iconColor="text-success" />
                    @endif
                </x-ui.card>

                <div class="mt-6">
                    <livewire:osa.dashboard.recent-activity defer.bundle />
                </div>
            </div>

            <!-- Right Column: Upcoming Events + Today's Summary (deferred) -->
            <livewire:osa.dashboard.sidebar defer.bundle />
        </div>

        <!-- Quick Actions -->
        @persist('quick-actions')
            <x-ui.card title="Quick Actions" subtitle="Frequently used features" shadow>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <x-ui.quick-action label="Review Tickets" description="Manage event requests" icon="o-ticket"
                        color="primary" link="{{ route('osa.ticket-review.index') }}"
                        :badge="$stats['pending'] > 0 ? $stats['pending'] . ' pending' : null" />

                    <x-ui.quick-action label="Event Calendar" description="View schedule" icon="o-calendar-days"
                        color="accent" link="/admin/calendar" />

                    <x-ui.quick-action label="Reports" description="Generate insights" icon="o-document-chart-bar"
                        color="info" link="/admin/reports" />
                </div>
            </x-ui.card>
        @endpersist
    </div>
</div>
