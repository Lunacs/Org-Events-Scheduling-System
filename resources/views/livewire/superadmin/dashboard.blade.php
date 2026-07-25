<div>
    <div class="p-6 space-y-6">
        {{-- Header --}}
        <section
            class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
            <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-heading font-bold text-base-content">SuperAdmin Dashboard</h1>
                        <p class="text-sm text-base-content/70 mt-1">
                            Overview of system activity and items requiring attention
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Attention Required Alert --}}
        @if ($attentionRequired['total_attention'] > 0)
            <div class="alert alert-warning shadow-lg">
                <x-ui.icon name="o-exclamation-triangle" class="w-6 h-6" />
                <div>
                    <h3 class="font-bold">Attention Required</h3>
                    <div class="text-sm">{{ $attentionRequired['total_attention'] }} item(s) need your attention</div>
                </div>
                <a href="{{ route('superadmin.tickets') }}" class="btn btn-sm btn-ghost">View All</a>
            </div>
        @endif

        {{-- Quick Stats Overview --}}
        <div>
            <h2 class="text-lg font-semibold text-base-content mb-3 flex items-center gap-2">
                <x-ui.icon name="o-chart-bar" class="w-5 h-5 text-primary" />
                System Overview
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-ui.metric-card label="Total Users" value="{{ number_format($stats['totalUsers']) }}"
                    icon="o-users" color="primary" />

                <x-ui.metric-card label="Total Tickets" value="{{ number_format($stats['totalTickets']) }}"
                    icon="o-ticket" color="secondary" />

                <x-ui.metric-card label="Total Events" value="{{ number_format($stats['totalEvents']) }}"
                    icon="o-calendar-days" color="accent" />

                <x-ui.metric-card label="Pending Review" value="{{ number_format($stats['pendingTickets']) }}"
                    icon="o-clock" color="warning" />
            </div>
        </div>

        {{-- Quick Actions --}}
        <div>
            <h2 class="text-lg font-semibold text-base-content mb-3 flex items-center gap-2">
                <x-ui.icon name="o-bolt" class="w-5 h-5 text-accent" />
                Quick Actions
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-ui.quick-action label="Review Tickets" description="{{ $stats['pendingTickets'] }} pending"
                    icon="o-ticket" color="primary" link="{{ route('superadmin.tickets') }}" />

                <x-ui.quick-action label="View Calendar" description="{{ $stats['eventsThisWeek'] }} this week"
                    icon="o-calendar" color="secondary" link="{{ route('superadmin.calendar') }}" />

                <x-ui.quick-action label="Manage Users" description="{{ $stats['totalUsers'] }} users"
                    icon="o-user-group" color="accent" link="{{ route('superadmin.users') }}" />

                <x-ui.quick-action label="View Reports" description="Analytics & exports" icon="o-chart-pie"
                    color="info" link="{{ route('superadmin.reports') }}" />
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Pending Approvals (deferred child component) --}}
            <livewire:superadmin.dashboard.pending-approvals defer.bundle class="lg:col-span-2" />

            {{-- Upcoming Events --}}
            <x-ui.card title="Upcoming Events" subtitle="Next 7 days" shadow>
                <x-slot:menu>
                    <a href="{{ route('superadmin.calendar') }}" class="btn btn-ghost btn-sm">Calendar</a>
                </x-slot:menu>
                @if (count($upcomingEvents) > 0)
                    <div class="space-y-3">
                        @foreach ($upcomingEvents as $event)
                            <div
                                class="flex items-start gap-3 p-3 rounded-lg {{ $event['is_today'] ? 'bg-primary/10 border border-primary/20' : ($event['is_tomorrow'] ? 'bg-warning/10 border border-warning/20' : 'bg-base-200/50') }}">
                                <div
                                    class="flex-shrink-0 w-12 h-12 rounded-lg {{ $event['is_today'] ? 'bg-primary text-primary-content' : ($event['is_tomorrow'] ? 'bg-warning text-warning-content' : 'bg-base-300 text-base-content') }} flex flex-col items-center justify-center text-xs font-bold">
                                    <span>{{ \Carbon\Carbon::parse($event['date'])->format('d') }}</span>
                                    <span
                                        class="text-[10px] opacity-80">{{ \Carbon\Carbon::parse($event['date'])->format('M') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm truncate">{{ $event['event_name'] }}</div>
                                    <div class="text-xs text-base-content/60 flex items-center gap-2">
                                        <span
                                            class="font-medium {{ $event['is_today'] ? 'text-primary' : ($event['is_tomorrow'] ? 'text-warning' : '') }}">
                                            {{ $event['day_label'] }}
                                        </span>
                                        <span>•</span>
                                        <span>{{ $event['time'] }}</span>
                                    </div>
                                    <div class="text-xs text-base-content/50 flex items-center gap-1 mt-1">
                                        <x-ui.icon name="o-map-pin" class="w-3 h-3" />
                                        {{ $event['venue'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state title="No Upcoming Events"
                        description="No events scheduled for the next 7 days." icon="o-calendar" />
                @endif
            </x-ui.card>
        </div>

        {{-- Bottom Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Activity Feed (deferred child component) --}}
            <livewire:superadmin.dashboard.recent-activity defer.bundle />

            {{-- Items Needing Attention --}}
            <x-ui.card title="Items Needing Attention" subtitle="Tickets requiring follow-up" shadow>
                @if ($attentionRequired['total_attention'] > 0)
                    <div class="space-y-4">
                        {{-- Pending OSA Review --}}
                        @if (count($attentionRequired['pending_osa_review']) > 0)
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="badge badge-warning badge-sm">Pending Review</span>
                                    <span
                                        class="text-xs text-base-content/60">{{ count($attentionRequired['pending_osa_review']) }}
                                        tickets</span>
                                </div>
                                <div class="space-y-2">
                                    @foreach (array_slice($attentionRequired['pending_osa_review'], 0, 2) as $item)
                                        <x-ui.approval-queue-item title="{{ $item['title'] }}"
                                            :days-waiting="$item['days_waiting']" tone="warning" />
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Stuck in GSO Review --}}
                        @if (count($attentionRequired['stuck_gso_review']) > 0)
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="badge badge-info badge-sm">Stuck in GSO</span>
                                    <span
                                        class="text-xs text-base-content/60">{{ count($attentionRequired['stuck_gso_review']) }}
                                        tickets</span>
                                </div>
                                <div class="space-y-2">
                                    @foreach (array_slice($attentionRequired['stuck_gso_review'], 0, 2) as $item)
                                        <x-ui.approval-queue-item title="{{ $item['title'] }}"
                                            :days-waiting="$item['days_waiting']" tone="info" />
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Revision Follow-up --}}
                        @if (count($attentionRequired['revision_followup']) > 0)
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="badge badge-error badge-sm">Revision Overdue</span>
                                    <span
                                        class="text-xs text-base-content/60">{{ count($attentionRequired['revision_followup']) }}
                                        tickets</span>
                                </div>
                                <div class="space-y-2">
                                    @foreach (array_slice($attentionRequired['revision_followup'], 0, 2) as $item)
                                        <x-ui.approval-queue-item title="{{ $item['title'] }}"
                                            :days-waiting="$item['days_waiting']" tone="error" />
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 pt-4 border-t border-base-200">
                        <a href="{{ route('superadmin.tickets') }}" class="btn btn-sm btn-primary btn-block">
                            View All Tickets
                        </a>
                    </div>
                @else
                    <x-ui.empty-state title="All Clear!" description="No items require immediate attention."
                        icon="o-check-badge" tone="success" iconColor="text-success" />
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
