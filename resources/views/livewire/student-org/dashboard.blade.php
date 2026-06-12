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
                <section
                    class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
                    <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
                    <div class="relative p-6 sm:p-8">
                        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex items-start space-x-4">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/20">
                                    <x-mary-icon name="s-building-office" class="w-6 h-6 text-primary" />
                                </span>
                                <div>
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

                                <x-mary-button icon="o-arrow-path"
                                    class="btn-primary btn-sm data-loading:opacity-50 data-loading:pointer-events-none"
                                    wire:click.async="$refresh" spinner>
                                    Refresh
                                </x-mary-button>
                            </div>
                        </div>
                    </div>
                </section>
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

                <x-mary-stat title="Upcoming Events" description="Next 30 days" value="{{ $upcomingEventsCount }}"
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

            {{-- Upcoming Events (deferred child component) --}}
            <livewire:student-org.dashboard.upcoming-events defer.bundle />

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

            {{-- Recent Notifications (deferred child component) --}}
            <livewire:student-org.dashboard.recent-notifications defer.bundle />
        </div>
    </div>

</div>
