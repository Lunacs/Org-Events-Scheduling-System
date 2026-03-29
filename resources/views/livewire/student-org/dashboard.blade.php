<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            {{ __('Student Organization Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Welcome Card --}}
            @persist('student-dashboard-header')
                <x-mary-card class="shadow-md overflow-hidden border border-base-300/60">
                    <div class="relative bg-linear-to-br from-primary/15 via-base-100 to-base-100 p-6">
                        <div
                            class="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full bg-primary/15 blur-2xl">
                        </div>

                        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex items-start space-x-4">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/20">
                                    <x-mary-icon name="s-building-office" class="w-6 h-6 text-primary" />
                                </span>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.18em] text-base-content/60">Student Organization
                                    </p>
                                    <h1 class="text-2xl md:text-3xl font-bold font-heading text-base-content">
                                        Welcome back, {{ auth()->user()->name ?? 'Student Organization' }}
                                    </h1>
                                    <p class="text-sm text-base-content/70 mt-1">
                                        Overview of your requests, approvals, and upcoming activities.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="text-xs text-base-content/60 hidden md:block">
                                    Last updated: <span class="font-medium">{{ now()->format('h:i A') }}</span>
                                </div>

                                <x-mary-button icon="o-arrow-path" class="btn-primary btn-sm" wire:click="$refresh"
                                    wire:loading.attr="disabled" wire:target="$refresh">
                                    <span wire:loading.remove wire:target="$refresh">Refresh</span>
                                    <span wire:loading wire:target="$refresh" class="inline-flex items-center gap-2">
                                        <span class="loading loading-spinner loading-xs"></span>
                                        Refreshing...
                                    </span>
                                </x-mary-button>
                            </div>
                        </div>
                    </div>
                </x-mary-card>
            @endpersist

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <x-mary-stat title="Total Tickets" description="Submitted requests" value="{{ $tickets->count() }}"
                    icon="s-ticket" color="text-primary" />

                <x-mary-stat title="Pending" description="Awaiting approval"
                    value="{{ $tickets->whereNotIn('status', ['approved'])->count() }}" icon="s-clock"
                    color="text-warning" />

                <x-mary-stat title="Approved" description="Ready to proceed"
                    value="{{ $tickets->where('status', 'approved')->count() }}" icon="s-check-circle"
                    color="text-success" />

                <x-mary-stat title="Upcoming Events" description="Next 30 days" value="{{ count($upcomingEvents) }}"
                    icon="s-calendar-days" color="text-info" />
            </div>

            {{-- Recent Tickets --}}
            <x-mary-card title="Recent Ticket Submissions"
                subtitle="The latest event requests from your organization: {{ auth()->user()->studentOrganization->org_name }}">
                <x-slot:menu>
                    <x-mary-button label="View All" link="/student-org/my-tickets" icon="s-eye"
                        class="btn-sm btn-ghost" wire:navigate />
                </x-slot:menu>

                {{-- Desktop --}}
                <div class="hidden md:block">
                    <x-mary-table :headers="[
                        ['key' => 'id', 'label' => '#'],
                        ['key' => 'title', 'label' => 'Event Title'],
                        ['key' => 'date', 'label' => 'Requested Date'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'submitted', 'label' => 'Submitted'],
                    ]" :rows="$recentTickets">
                        @scope('cell_id', $recentTicket)
                            {{ $recentTicket->ticket_number }}
                        @endscope

                        @scope('cell_title', $recentTicket)
                            {{ $recentTicket->event_name ?? $recentTicket->title }}
                        @endscope

                        @scope('cell_date', $recentTicket)
                            {{ \Carbon\Carbon::parse($recentTicket->date_from)->format('M j, Y') ?? 'N/A' }}
                        @endscope

                        @scope('cell_status', $recentTicket)
                            <x-tickets.progress-badge :status="$recentTicket->status" />
                        @endscope

                        @scope('cell_submitted', $recentTicket)
                            {{ $recentTicket->created_at->format('Y-m-d') }}
                        @endscope
                    </x-mary-table>
                </div>

                {{-- Mobile --}}
                <div class="md:hidden space-y-3">
                    @forelse($recentTickets as $recentTicket)
                        <div class="border border-base-300 rounded-lg p-4 space-y-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="mb-2">
                                        <x-tickets.progress-badge :status="$recentTicket->status" />
                                    </div>
                                    <p class="text-xs text-base-content/60">#{{ $recentTicket->ticket_number }}</p>
                                    <h4 class="font-semibold text-base-content">{{ $recentTicket->event_name ?? $recentTicket->title }}</h4>
                                </div>
                            </div>
                            <div class="text-sm text-base-content/70">
                                <p>
                                    Requested:
                                    {{ \Carbon\Carbon::parse($recentTicket->date_from)->format('M j, Y') ?? 'N/A' }}
                                </p>
                                <p>Submitted: {{ $recentTicket->created_at->format('Y-m-d') }}</p>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state title="No recent tickets yet"
                            description="Your newest submissions will appear here once you create an event request."
                            icon="o-document-text" tone="primary" iconColor="text-primary" actionLabel="Submit a Ticket"
                            actionLink="/student-org/submit-ticket" />
                    @endforelse
                </div>
            </x-mary-card>

            {{-- Upcoming Events --}}
            <x-mary-card title="Upcoming Approved Events" subtitle="Events scheduled for the next 30 days">
                <x-slot:menu>
                    <x-mary-button label="View Calendar" link="/student-org/calendar" icon="s-calendar"
                        class="btn-sm btn-ghost" wire:navigate />
                </x-slot:menu>

                <div class="space-y-4">
                    @forelse($upcomingEvents as $event)
                        <x-tickets.upc-events-card :ticket="$event" />
                    @empty
                        <x-ui.empty-state title="No upcoming approved events"
                            description="Approved events within the next 30 days will be listed here."
                            icon="o-calendar-days" tone="info" iconColor="text-info" actionLabel="Open Calendar"
                            actionLink="/student-org/calendar" />
                    @endforelse
                </div>
            </x-mary-card>

            {{-- Quick Actions --}}
            <x-mary-card title="Quick Actions" subtitle="Frequently used actions">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-mary-button label="Submit New Ticket" icon="s-document-plus" class="btn-primary btn-lg w-full"
                        link="/student-org/submit-ticket" wire:navigate />

                    <x-mary-button label="Check My Tickets" icon="s-ticket" class="btn-secondary btn-lg w-full"
                        link="/student-org/my-tickets" wire:navigate />

                    <x-mary-button label="Request Reschedule" icon="s-arrow-path" class="btn-accent btn-lg w-full"
                        link="/student-org/reschedule" wire:navigate />
                </div>
            </x-mary-card>

            {{-- Recent Notifications --}}
            <x-mary-card title="Recent Notifications" subtitle="Latest updates from OSA and GSO">
                <x-slot:menu>
                    <x-mary-button label="View All" link="/student-org/notifications" icon="s-bell"
                        class="btn-sm btn-ghost" wire:navigate />
                </x-slot:menu>

                <div class="space-y-3">
                    @forelse($recentNotifications as $notification)
                        @php
                            $data = $notification->data;
                            $createdAt = \Illuminate\Support\Carbon::parse($notification->created_at);
                            $timeAgo = $createdAt->diffForHumans();

                            $color = $data['color'] ?? 'info';

                            $bgMap = [
                                'primary' => 'bg-primary/10 border-primary',
                                'success' => 'bg-success/10 border-success',
                                'error' => 'bg-error/10 border-error',
                                'warning' => 'bg-warning/10 border-warning',
                                'info' => 'bg-info/10 border-info',
                                'secondary' => 'bg-secondary/10 border-secondary',
                            ];
                            $iconColorMap = [
                                'primary' => 'text-primary',
                                'success' => 'text-success',
                                'error' => 'text-error',
                                'warning' => 'text-warning',
                                'info' => 'text-info',
                                'secondary' => 'text-secondary',
                            ];
                            $iconMap = [
                                'success' => 's-check-circle',
                                'warning' => 's-exclamation-triangle',
                                'error' => 's-x-circle',
                                'info' => 's-information-circle',
                            ];

                            $containerClass = $bgMap[$color] ?? 'bg-info/10 border-info';
                            $iconColorClass = $iconColorMap[$color] ?? 'text-info';
                            $icon = $iconMap[$color] ?? 's-bell';
                        @endphp

                        <div class="flex items-start gap-3 p-3 {{ $containerClass }} rounded-lg border-l-4">
                            <x-mary-icon :name="$icon" class="w-5 h-5 {{ $iconColorClass }} mt-0.5" />
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-base-content">{{ $data['title'] ?? 'Notification' }}</p>
                                <p class="text-sm text-base-content/70">{{ $data['message'] ?? 'No message' }}</p>
                                <p class="text-xs text-base-content/50 mt-1">{{ $timeAgo }}</p>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state title="No recent notifications"
                            description="System updates and ticket feedback will appear here." icon="o-bell-slash"
                            tone="secondary" iconColor="text-secondary" actionLabel="View Notification Center"
                            actionLink="/student-org/notifications" />
                    @endforelse
                </div>
            </x-mary-card>
        </div>
    </div>

</div>
