<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            {{ __('Event History') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Header --}}
            <section
                class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
                <div class="absolute -top-24 -right-24 h-56 w-56 rounded-full bg-primary/10 blur-2xl"></div>
                <div class="relative p-6 sm:p-8">
                    <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs tracking-[0.2em] uppercase text-base-content/50">Student Organization</p>
                            <h1 class="mt-1 text-3xl sm:text-4xl font-heading font-extrabold text-base-content">Event
                                History</h1>
                            <p class="mt-2 max-w-2xl text-sm sm:text-base text-base-content/70">
                                Track approved, revised, completed, and cancelled event requests in one place.
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-mary-button label="Submit New Event" icon="s-document-plus"
                                class="btn-primary btn-sm text-white" link="/student-org/submit-ticket" wire:navigate />
                        </div>
                    </div>
                </div>
            </section>

            {{-- Analytics Summary --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <x-mary-stat title="Total Events" description="Approved & Needs Revision" :value="$this->stats['total']"
                    icon="s-calendar-days" color="text-primary" />

                <x-mary-stat title="Approved" :description="'Success rate: ' . $this->stats['approved']['percentage'] . '%'" :value="$this->stats['approved']['count']" icon="s-check-circle"
                    color="text-success" />

                <x-mary-stat title="Needs Revision" :description="$this->stats['for_revision']['percentage'] . '% of submissions'" :value="$this->stats['for_revision']['count']" icon="s-x-circle"
                    color="text-error" />

                <x-mary-stat title="Avg. Processing" description="Days to approval" :value="$this->stats['avgProcessingDays']" icon="s-clock"
                    color="text-info" />
            </div>

            {{-- Performance Analytics --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-mary-card title="Event Type Mix" subtitle="Most frequent approved categories">
                    @if (count($this->eventTypeDistribution) > 0)
                        <div class="space-y-3">
                            @foreach ($this->eventTypeDistribution as $type)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-3 w-3 {{ $type['color'] }} rounded-full"></div>
                                        <span class="text-sm font-medium">{{ $type['name'] }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="hidden w-32 h-2 rounded-full bg-base-300 md:block">
                                            <div class="{{ $type['color'] }} h-2 rounded-full"
                                                style="width: {{ $type['percentage'] }}%"></div>
                                        </div>
                                        <span class="text-sm text-base-content/70">{{ $type['percentage'] }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-base-content/60">
                            <x-mary-icon name="o-chart-bar" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">No approved events yet</p>
                        </div>
                    @endif
                </x-mary-card>

                <x-mary-card title="Monthly Activity" subtitle="Submissions in the last 6 months">
                    @if (count($this->monthlyActivity) > 0)
                        <div class="space-y-3">
                            @foreach ($this->monthlyActivity as $month)
                                <div class="grid grid-cols-[1fr_1fr_auto] items-center gap-3">
                                    <span class="text-sm font-medium truncate">{{ $month['name'] }}</span>
                                    <div class="w-full h-2 rounded-full bg-base-300">
                                        <div class="bg-primary h-2 rounded-full"
                                            style="width: {{ $month['percentage'] }}%"></div>
                                    </div>
                                    <span class="text-sm text-base-content/70 text-right">{{ $month['count'] }}
                                        {{ Str::plural('event', $month['count']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-base-content/60">
                            <x-mary-icon name="o-calendar" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">No events submitted in the last 6 months</p>
                        </div>
                    @endif
                </x-mary-card>
            </div>

            {{-- Filter and Search --}}
            <x-mary-card>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-12 md:items-end">
                    <div class="md:col-span-5">
                        <x-mary-input label="Search Events" wire:model.live.debounce.500ms="search"
                            x-data="{ placeholder: window.innerWidth < 768 ? 'Title, Description or Venue' : 'Search by title, description, or venue...' }" x-init="window.addEventListener('resize', () => { placeholder = window.innerWidth < 768 ? 'Search...' : 'Search by title, description, or venue...' })" ::placeholder="placeholder"
                            icon="s-magnifying-glass" />
                    </div>

                    <div wire:loading.class="opacity-50" class="md:col-span-2">
                        <x-mary-select label="Status" wire:model.live="statusFilter" :options="[
                            ['id' => '', 'name' => 'All Status'],
                            ['id' => 'approved', 'name' => 'Approved'],
                            ['id' => 'for_revision', 'name' => 'Needs Revision'],
                            ['id' => 'cancelled', 'name' => 'Cancelled'],
                        ]" />
                    </div>

                    <div wire:loading.class="opacity-50" class="md:col-span-2">
                        <x-mary-select label="Event Type" wire:model.live="typeFilter" :options="$this->eventTypes" />
                    </div>

                    <div wire:loading.class="opacity-50" class="md:col-span-2">
                        <x-mary-select label="Year" wire:model.live="yearFilter" :options="$this->years" />
                    </div>

                    <div class="md:col-span-1 flex md:justify-end">
                        <x-mary-button icon="s-arrow-path" class="btn-ghost w-full md:w-auto" wire:click="resetFilters"
                            tooltip="Reset Filters" />
                    </div>
                </div>
            </x-mary-card>

            {{-- Event History List --}}
            <x-mary-card title="Event History" subtitle="Complete record of your organization's events">
                <div wire:loading.delay class="flex justify-center items-center py-8">
                    <span class="loading loading-spinner loading-md"></span>
                    <span class="ml-2 text-base-content/60">Loading events...</span>
                </div>

                <div class="space-y-6" wire:loading.remove.delay>
                    @forelse($this->tickets as $ticket)
                        @php
                            $isApproved = $ticket->status === 'approved';
                            $isForRevision = $ticket->status === 'for_revision';
                            $isCancelled = $ticket->status === 'cancelled';

                            // Check if event is completed based on event_schedule end_date or ticket date_to
                            $eventEndDate = $ticket->end_date ?? $ticket->date_to;
                            $isComplete = $isApproved && $eventEndDate && now()->isAfter($eventEndDate);

                            $decisionLabel = $isForRevision
                                ? 'Revision Requested'
                                : ($isCancelled
                                    ? 'Cancelled'
                                    : 'Approved');
                        @endphp

                        <article
                            class="rounded-xl border border-base-300 bg-base-100 p-5 sm:p-6 transition-all hover:shadow-md {{ $isCancelled ? 'opacity-75' : '' }}">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        @if ($isApproved)
                                            <x-mary-badge value="Approved" class="badge-success text-white" />
                                            @if ($isComplete)
                                                <x-mary-badge value="Completed" class="badge-info text-white" />
                                            @endif
                                        @elseif($isForRevision)
                                            <x-mary-badge value="For Revision" class="badge-warning text-white" />
                                        @elseif($isCancelled)
                                            <x-mary-badge value="Cancelled" class="badge-error text-white" />
                                        @endif
                                        <span class="text-xs text-base-content/50">
                                            Ticket #{{ $ticket->ticket_number ?? $ticket->ticket_id }}
                                        </span>
                                    </div>

                                    <h3 class="text-lg sm:text-xl font-bold text-base-content wrap-break-word">
                                        {{ $ticket->title }}</h3>
                                    <p class="mt-2 text-sm text-base-content/70 wrap-break-word">
                                        {{ $ticket->description }}
                                    </p>

                                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                        <div>
                                            <p class="text-xs text-base-content/50 uppercase tracking-wide">
                                                {{ $isForRevision ? 'Proposed Date' : 'Event Date' }}
                                            </p>
                                            <p class="text-sm font-semibold text-base-content">
                                                @if ($ticket->start_date)
                                                    {{ \Carbon\Carbon::parse($ticket->start_date)->format('M d, Y') }}
                                                    @if ($ticket->end_date && $ticket->start_date !== $ticket->end_date)
                                                        -
                                                        {{ \Carbon\Carbon::parse($ticket->end_date)->format('M d, Y') }}
                                                    @endif
                                                @else
                                                    {{ \Carbon\Carbon::parse($ticket->date_from)->format('M d, Y') }}
                                                    @if ($ticket->date_to && $ticket->date_from !== $ticket->date_to)
                                                        -
                                                        {{ \Carbon\Carbon::parse($ticket->date_to)->format('M d, Y') }}
                                                    @endif
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-base-content/50 uppercase tracking-wide">Venue</p>
                                            <p class="text-sm font-semibold text-base-content wrap-break-word">
                                                {{ $ticket->schedule_venue ?? $ticket->venue_display_name }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-base-content/50 uppercase tracking-wide">Expected
                                            </p>
                                            <p class="text-sm font-semibold text-base-content">
                                                {{ $ticket->total_participants ?? $ticket->plv_participants }}
                                                attendees</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-base-content/50 uppercase tracking-wide">Type</p>
                                            <p class="text-sm font-semibold text-base-content">
                                                {{ $ticket->eventType->type_name }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                                        <div class="rounded-lg bg-base-200 px-3 py-2">
                                            <p class="text-xs uppercase tracking-wide text-base-content/50">Submitted
                                            </p>
                                            <p class="font-semibold text-base-content">
                                                {{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y') }}</p>
                                        </div>
                                        <div class="rounded-lg bg-base-200 px-3 py-2">
                                            <p class="text-xs uppercase tracking-wide text-base-content/50">
                                                {{ $decisionLabel }}</p>
                                            <p class="font-semibold text-base-content">
                                                {{ \Carbon\Carbon::parse($ticket->updated_at)->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-row lg:flex-col gap-2 lg:ml-4">
                                    <x-mary-button icon="s-eye" class="btn-sm btn-ghost flex-1 lg:flex-none"
                                        tooltip="View Details"
                                        wire:click="openDetailsModal({{ $ticket->ticket_id }})" />
                                    @if ($isForRevision)
                                        <x-mary-button icon="s-arrow-path"
                                            class="btn-sm btn-ghost flex-1 lg:flex-none" tooltip="Resubmit Modified"
                                            wire:click="resubmitTicket({{ $ticket->ticket_id }})" />
                                    @endif
                                </div>
                            </div>

                            {{-- Status-specific feedback sections --}}
                            @php
                                // Only show revision remarks if ticket is needing revision AND has remarks
                                $for_revisionRemarks =
                                    $isForRevision && $ticket->osa_decision === 'for_revision'
                                        ? $ticket->osa_remarks
                                        : null;

                                $completionRemarks = $isComplete ? $ticket->schedule_remarks : null;
                                $cancellationRemarks = $isCancelled
                                    ? $ticket->schedule_remarks ?? $ticket->content
                                    : null;
                            @endphp

                            @if ($isForRevision && $for_revisionRemarks)
                                <div class="bg-orange-50 p-4 rounded-lg border-l-4 border-orange-400">
                                    <div class="flex items-start space-x-3">
                                        <x-mary-icon name="s-exclamation-triangle"
                                            class="w-5 h-5 text-orange-600 mt-0.5" />
                                        <div class="flex-1">
                                            <h5 class="font-medium text-orange-900">Revision Reasons</h5>
                                            <p class="text-sm text-orange-700 mt-1">{{ $for_revisionRemarks }}</p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($isComplete && $completionRemarks)
                                <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-400">
                                    <div class="flex items-start space-x-3">
                                        <x-mary-icon name="s-chart-bar" class="w-5 h-5 text-green-600 mt-0.5" />
                                        <div class="flex-1">
                                            <h5 class="font-medium text-green-900">Event Results & Feedback</h5>
                                            <p class="text-sm text-green-700 mt-1">{{ $completionRemarks }}</p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($isCancelled && $cancellationRemarks)
                                <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-400">
                                    <div class="flex items-start space-x-3">
                                        <x-mary-icon name="s-exclamation-triangle"
                                            class="w-5 h-5 text-yellow-600 mt-0.5" />
                                        <div class="flex-1">
                                            <h5 class="font-medium text-yellow-900">Event Cancelled</h5>
                                            <p class="text-sm text-yellow-700 mt-1">{{ $cancellationRemarks }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </article>
                    @empty
                        <x-ui.empty-state title="No event history yet"
                            description="Approved, revised, and cancelled event requests will appear here once submissions are processed."
                            icon="o-calendar-days" tone="info" iconColor="text-info"
                            actionLabel="Submit New Event" actionLink="/student-org/submit-ticket" />
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if ($this->tickets->hasPages())
                    <div class="mt-6">
                        {{ $this->tickets->links() }}
                    </div>
                @endif
            </x-mary-card>
        </div>
    </div>

    {{-- Ticket Details Modal --}}
    <x-mary-modal wire:model="showDetailsModal" title="Event Details" class="backdrop-blur"
        box-class="max-w-7xl max-h-[85vh] overflow-y-auto">
        @if ($loadingDetails)
            <div class="flex justify-center items-center py-12">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
        @elseif($selectedTicket)
            <div class="grid grid-cols-1 gap-6">
                <x-tickets.ticket-preview :ticket="$selectedTicket" />
            </div>
        @endif
    </x-mary-modal>
</div>
