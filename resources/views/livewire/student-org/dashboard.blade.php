@php
    // One status vocabulary (App\Support\TicketStatus): active = with an office, attention = org must act.
    $activeStatuses = ['received', 'pending', 'amended', 'gso_review', 'pending_osa_approval'];
    $needsAction = $tickets->whereIn('status', ['for_revision', 'needs_revision']);
    $activeCount = $tickets->whereIn('status', $activeStatuses)->count();
    $approvedCount = $tickets->where('status', 'approved')->count();
    $canCreate = auth()->user()?->can('tickets.create');
    $organization = auth()->user()->studentOrganization?->org_name;
@endphp

<div class="space-y-6">
    <x-ui.page-heading>
        <x-slot:heading>Welcome back, {{ auth()->user()->name ?? 'Student Organization' }}</x-slot:heading>
        <x-slot:subtitle>
            {{ $organization ? $organization . ' · ' : '' }}Track your event requests and what needs your attention.
        </x-slot:subtitle>
        @if ($canCreate)
            <x-slot:actions>
                <x-ui.button label="Submit a ticket" icon="o-plus" class="btn-primary"
                    link="{{ route('student-org.submit-ticket') }}" />
            </x-slot:actions>
        @endif
    </x-ui.page-heading>

    {{-- Needs your action: unfinished work first (Zeigarnik effect) --}}
    @if ($needsAction->isNotEmpty())
        <section aria-labelledby="needs-action-heading"
            class="rounded-box border border-warning/40 bg-warning/[0.06] p-4 sm:p-5">
            <div class="flex items-start gap-3">
                <x-ui.icon name="o-pencil-square" class="mt-0.5 size-5 shrink-0 text-warning" aria-hidden="true" />
                <div class="min-w-0 flex-1">
                    <h2 id="needs-action-heading" class="text-base font-semibold text-base-content">
                        {{ $needsAction->count() === 1 ? '1 request needs your revision' : $needsAction->count() . ' requests need your revision' }}
                    </h2>
                    <p class="mt-0.5 text-sm text-base-content/70">
                        OSA asked for changes. Open the request, read the remarks, then update and resubmit it.
                    </p>
                    <ul class="mt-3 divide-y divide-warning/20">
                        @foreach ($needsAction->take(5) as $ticket)
                            <li class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 py-2.5">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-base-content">
                                        {{ $ticket->event_name ?? $ticket->title }}</p>
                                    <p class="font-mono text-xs text-base-content/70">{{ $ticket->ticket_number }}</p>
                                </div>
                                <x-ui.button label="Review changes" icon-right="o-arrow-right" class="btn-sm"
                                    link="{{ route('student-org.ticket-details', ['ticketNumber' => $ticket->ticket_number]) }}" />
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
    @endif

    {{-- Status strip --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-ui.metric-card label="In review" value="{{ $activeCount }}" meta="With OSA or GSO" icon="o-clock"
            color="info" link="{{ route('student-org.my-tickets', ['status' => 'under_review']) }}"
            link-label="View requests in review" />
        <x-ui.metric-card label="Needs revision" value="{{ $needsAction->count() }}" meta="Waiting on you"
            icon="o-pencil-square" color="warning"
            link="{{ route('student-org.my-tickets', ['status' => 'for_revision']) }}"
            link-label="View requests that need revision" />
        <x-ui.metric-card label="Approved" value="{{ $approvedCount }}" meta="All time" icon="o-check-circle"
            color="success" link="{{ route('student-org.my-tickets', ['status' => 'approved']) }}"
            link-label="View approved requests" />
        <x-ui.metric-card label="Upcoming events" value="{{ $upcomingEventsCount }}" meta="Next 30 days"
            icon="o-calendar-days" link="{{ route('student-org.calendar') }}" link-label="Open the event calendar" />
    </div>

    <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-3">
        {{-- Recent tickets --}}
        <x-ui.card title="Recent requests" subtitle="Your latest event requests and where each one stands."
            class="xl:col-span-2">
            <x-slot:menu>
                <x-ui.button label="View all" icon-right="o-arrow-right" link="{{ route('student-org.my-tickets') }}"
                    class="btn-ghost btn-sm" />
            </x-slot:menu>

            @if ($recentTickets->isEmpty())
                <x-ui.empty-state icon="o-document-text" title="No requests yet"
                    description="Event requests you submit appear here with their current status and next step."
                    :actionLabel="$canCreate ? 'Submit a ticket' : null"
                    :actionLink="$canCreate ? route('student-org.submit-ticket') : null" primary />
            @else
                {{-- Desktop table --}}
                <div class="-mx-4 hidden sm:-mx-5 md:block">
                    <x-ui.table :headers="[
                        ['key' => 'title', 'label' => 'Event'],
                        ['key' => 'date', 'label' => 'Event date'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'submitted', 'label' => 'Submitted'],
                    ]">
                        @foreach ($recentTickets as $recentTicket)
                            <tr wire:key="recent-{{ $recentTicket->ticket_id }}" class="hover:bg-base-200/50">
                                <x-ui.table-column>
                                    <a href="{{ route('student-org.ticket-details', ['ticketNumber' => $recentTicket->ticket_number]) }}"
                                        wire:navigate class="font-medium text-base-content hover:underline">
                                        {{ $recentTicket->event_name ?? $recentTicket->title }}
                                    </a>
                                    <p class="font-mono text-xs text-base-content/70">{{ $recentTicket->ticket_number }}</p>
                                </x-ui.table-column>
                                <x-ui.table-column class="whitespace-nowrap">
                                    {{ $recentTicket->date_from ? \Carbon\Carbon::parse($recentTicket->date_from)->format('M j, Y') : '—' }}
                                </x-ui.table-column>
                                <x-ui.table-column>
                                    <x-tickets.progress-badge :status="$recentTicket->status" size="sm" />
                                </x-ui.table-column>
                                <x-ui.table-column class="whitespace-nowrap text-base-content/70">
                                    {{ $recentTicket->created_at->format('M j, Y') }}
                                </x-ui.table-column>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </div>

                {{-- Mobile list --}}
                <ul class="-mx-4 divide-y divide-base-300 border-t border-base-300 md:hidden">
                    @foreach ($recentTickets as $recentTicket)
                        <li wire:key="recent-m-{{ $recentTicket->ticket_id }}">
                            <a href="{{ route('student-org.ticket-details', ['ticketNumber' => $recentTicket->ticket_number]) }}"
                                wire:navigate class="flex items-start justify-between gap-3 px-4 py-3 active:bg-base-200">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-base-content">
                                        {{ $recentTicket->event_name ?? $recentTicket->title }}</p>
                                    <p class="mt-0.5 text-xs text-base-content/70">
                                        <span class="font-mono">{{ $recentTicket->ticket_number }}</span>
                                        &middot;
                                        {{ $recentTicket->date_from ? \Carbon\Carbon::parse($recentTicket->date_from)->format('M j, Y') : 'No date' }}
                                    </p>
                                </div>
                                <x-tickets.progress-badge :status="$recentTicket->status" size="sm" class="shrink-0" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        {{-- Side column: schedule + updates (deferred) --}}
        <div class="space-y-6">
            <livewire:student-org.dashboard.upcoming-events defer.bundle />
            <livewire:student-org.dashboard.recent-notifications defer.bundle />
        </div>
    </div>
</div>
