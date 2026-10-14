{{-- Single root element wrapper for Livewire --}}
<div>
    @php
        // Cache computed properties to prevent duplicate cache queries
        $stats = $this->stats;
        $pendingApprovals = $this->pendingApprovals;
        $reviewUrl = route('osa.ticket-review.index');
    @endphp

    <div class="space-y-6">
        <x-ui.page-heading title="Dashboard">
            <x-slot:subtitle>
                {{ now()->timezone('Asia/Manila')->format('l, F j') }} &middot;
                @if ($stats['pending'] > 0)
                    {{ $stats['pending'] }} {{ Str::plural('request', $stats['pending']) }} waiting for your review.
                @else
                    Your review queue is clear.
                @endif
            </x-slot:subtitle>
            @can('tickets.view')
                <x-slot:actions>
                    <x-ui.button label="Open review queue" icon-right="o-arrow-right" class="btn-primary"
                        link="{{ $reviewUrl }}" />
                </x-slot:actions>
            @endcan
        </x-ui.page-heading>

        {{-- Status strip: where every active ticket is right now --}}
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <x-ui.metric-card label="Needs your review" value="{{ number_format($stats['pending']) }}"
                meta="New, resubmitted, or back from GSO" icon="o-inbox-arrow-down" color="info"
                link="{{ $reviewUrl }}" link-label="Open the review queue" />
            <x-ui.metric-card label="With GSO" value="{{ number_format($stats['forwarded']) }}"
                meta="Waiting on venue approval" icon="o-building-office-2"
                link="{{ route('osa.ticket-review.index', ['statusFilter' => 'gso_review']) }}"
                link-label="View tickets with GSO" />
            <x-ui.metric-card label="Needs revision" value="{{ number_format($stats['for_revision']) }}"
                meta="Waiting on organizations" icon="o-pencil-square" color="warning"
                link="{{ route('osa.ticket-review.index', ['statusFilter' => 'for_revision']) }}"
                link-label="View tickets that need revision" />
            <x-ui.metric-card label="Approved" value="{{ number_format($stats['approved']) }}"
                meta="{{ number_format($stats['thisMonthTickets']) }} new requests this month" icon="o-check-circle"
                color="success" link="{{ route('osa.ticket-review.index', ['statusFilter' => 'approved']) }}"
                link-label="View approved tickets" />
        </div>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                {{-- The queue is the dashboard (Pareto: this is most of OSA's work) --}}
                <x-ui.card title="Needs your review" subtitle="Open a request to approve it, forward it to GSO, or ask for changes.">
                    <x-slot:menu>
                        <x-ui.button label="View all" icon-right="o-arrow-right" link="{{ $reviewUrl }}"
                            class="btn-ghost btn-sm" />
                    </x-slot:menu>

                    @if (count($pendingApprovals) > 0)
                        <ul class="-mx-4 -mb-4 divide-y divide-base-300 border-t border-base-300 sm:-mx-5 sm:-mb-5">
                            @foreach ($pendingApprovals as $approval)
                                <li wire:key="pending-{{ $approval['id'] }}">
                                    <a href="{{ route('osa.ticket-review.show', $approval['ticket_number']) }}" wire:navigate
                                        class="block px-4 transition-colors hover:bg-base-200/60 sm:px-5">
                                        <x-ui.approval-queue-item :title="$approval['title']" :status="$approval['status']"
                                            :meta="[
                                                ['icon' => 'o-building-office', 'text' => $approval['organization']],
                                                ['icon' => 'o-clock', 'text' => 'Submitted ' . $approval['submitted']],
                                            ]" :reference="$approval['ticket_number']" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <x-ui.empty-state icon="o-check-circle" title="Your review queue is clear"
                            description="New and resubmitted requests, and tickets GSO sends back, will appear here." />
                    @endif
                </x-ui.card>

                <livewire:osa.dashboard.recent-activity defer.bundle />
            </div>

            {{-- Upcoming events + today's summary (deferred) --}}
            <livewire:osa.dashboard.sidebar defer.bundle />
        </div>
    </div>
</div>
