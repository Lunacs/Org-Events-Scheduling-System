<div>
    <div class="space-y-6">
        {{-- Header --}}
        <x-ui.page-heading title="SuperAdmin Dashboard" subtitle="System health at a glance: tickets that need follow-up, upcoming events, and recent activity." />

        {{-- Attention Required Alert --}}
        @if ($attentionRequired['total_attention'] > 0)
            <div role="status" class="flex flex-wrap items-center gap-3 rounded-box border border-warning/30 bg-warning/[0.06] px-4 py-3">
                <x-ui.icon name="o-exclamation-triangle" class="size-5 shrink-0 text-warning" aria-hidden="true" />
                <p class="min-w-0 flex-1 text-sm text-base-content">
                    <span class="font-semibold">{{ $attentionRequired['total_attention'] }} {{ Str::plural('ticket', $attentionRequired['total_attention']) }} need follow-up</span>
                    <span class="text-base-content/70">— tickets waiting longer than expected.</span>
                </p>
                <a href="{{ route('superadmin.tickets') }}" wire:navigate class="btn btn-sm">Review tickets</a>
            </div>
        @endif

        {{-- Quick Stats Overview --}}
        <div>
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
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
                                    <div class="text-xs text-base-content/60 flex items-center gap-1 mt-1">
                                        <x-ui.icon name="o-map-pin" class="w-3 h-3" />
                                        {{ $event['venue'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state title="No events this week"
                        description="Approved events in the next 7 days appear here." icon="o-calendar" />
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
                                    <span class="badge badge-soft badge-info badge-sm">Waiting on OSA</span>
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
                                    <span class="badge badge-soft badge-info badge-sm">Waiting on GSO</span>
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
                        <a href="{{ route('superadmin.tickets') }}" wire:navigate class="btn btn-sm btn-block">
                            View all tickets
                        </a>
                    </div>
                @else
                    <x-ui.empty-state title="Nothing needs follow-up" icon="o-check-circle"
                        description="Tickets that wait too long with OSA or GSO appear here." />
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
