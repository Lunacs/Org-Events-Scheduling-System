<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Event History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Header --}}
            <div class="mb-8">
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-heading font-bold text-base-content">Event History & Analytics</h1>
                            <p class="text-base-content/70 mt-1">View your organization's complete event history and
                                performance
                                metrics</p>
                        </div>
                        <div class="hidden md:flex md:flex-col items-center space-y-2 sm:space-x-3">
                            {{--                            <x-mary-button label="Export Report" icon="s-document-arrow-down" --}}
                            {{--                                           class="btn-secondary btn-sm text-white" --}}
                            {{--                                           wire:click="exportReport" disabled/> --}}
                            <x-mary-button label="Submit New Event" icon="s-document-plus"
                                class="btn-primary btn-sm text-white" link="/student-org/submit-ticket" wire:navigate />
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:hidden flex flex-row items-center space-y-0 space-x-3">
                {{--                <x-mary-button label="Export Report" icon="s-document-arrow-down" --}}
                {{--                               class="btn-secondary btn-sm text-white" --}}
                {{--                               wire:click="exportReport" disabled/> --}}
                <x-mary-button label="Submit New Event" icon="s-document-plus" class="btn-primary btn-sm text-white"
                    link="/student-org/submit-ticket" wire:navigate />
            </div>

            {{-- Analytics Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-mary-card title="Event Types Distribution" subtitle="Your most common event categories">
                    @if (count($this->eventTypeDistribution) > 0)
                        <div class="space-y-3">
                            @foreach ($this->eventTypeDistribution as $type)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-4 h-4 {{ $type['color'] }} rounded"></div>
                                        <span class="text-sm font-medium">{{ $type['name'] }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-32 bg-gray-200 rounded-full h-2 hidden md:block">
                                            <div class="{{ $type['color'] }} h-2 rounded-full"
                                                style="width: {{ $type['percentage'] }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ $type['percentage'] }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <x-mary-icon name="o-chart-bar" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">No approved events yet</p>
                        </div>
                    @endif
                </x-mary-card>


                <x-mary-card title="Monthly Activity" subtitle="Events submitted per month (Last 6 months)">
                    @if (count($this->monthlyActivity) > 0)
                        <div class="space-y-3">
                            @foreach ($this->monthlyActivity as $month)
                                <div class="grid grid-cols-[120px_1fr_80px] items-center gap-3">
                                    <span class="text-sm font-medium">{{ $month['name'] }}</span>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-primary h-2 rounded-full"
                                            style="width: {{ $month['percentage'] }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-600 text-right">{{ $month['count'] }}
                                        {{ Str::plural('event', $month['count']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <x-mary-icon name="o-calendar" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">No events submitted in the last 6 months</p>
                        </div>
                    @endif
                </x-mary-card>
            </div>

            {{-- Filter and Search --}}
            <x-mary-card>
                <div class="grid grid-cols-1 md:flex md:flex-wrap gap-4 md:items-end">
                    <x-mary-input label="Search Events" wire:model.live.debounce.500ms="search" x-data="{ placeholder: window.innerWidth < 768 ? 'Title, Description or Venue' : 'Search by title, description, or venue...' }"
                        x-init="window.addEventListener('resize', () => { placeholder = window.innerWidth < 768 ? 'Search...' : 'Search by title, description, or venue...' })" ::placeholder="placeholder" icon="s-magnifying-glass"
                        class="flex-1 md:min-w-64 min-w-32" />

                    <div wire:loading.class="opacity-50">
                        <x-mary-select label="Status" wire:model.live="statusFilter" :options="[
                            ['id' => '', 'name' => 'All Status'],
                            ['id' => 'approved', 'name' => 'Approved'],
                            ['id' => 'for_revision', 'name' => 'Needs Revision'],
                            ['id' => 'cancelled', 'name' => 'Cancelled'],
                        ]" class="w-32" />
                    </div>

                    <div wire:loading.class="opacity-50">
                        <x-mary-select label="Event Type" wire:model.live="typeFilter" :options="$this->eventTypes"
                            class="w-32" />
                    </div>

                    <div wire:loading.class="opacity-50">
                        <x-mary-select label="Year" wire:model.live="yearFilter" :options="$this->years" class="w-24" />
                    </div>

                    <div class="hidden md:block">
                        <x-mary-button icon="s-arrow-path" class="btn-ghost" wire:click="resetFilters"
                            tooltip="Reset Filters" />
                    </div>

                    <x-mary-button label="Reset Filters" class="mt-6 md:hidden" wire:click="resetFilters" />
                </div>
            </x-mary-card>

            {{-- Event History List --}}
            <x-mary-card title="Event History" subtitle="Complete record of your organization's events">
                <div wire:loading.delay class="flex justify-center items-center py-8">
                    <span class="loading loading-spinner loading-md"></span>
                    <span class="ml-2 text-gray-500">Loading events...</span>
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

                            $processingDays =
                                $ticket->created_at && $ticket->updated_at
                                    ? \Carbon\Carbon::parse($ticket->created_at)->diffInDays(
                                        \Carbon\Carbon::parse($ticket->updated_at),
                                    )
                                    : 0;
                        @endphp


                        <div
                            class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow {{ $isCancelled ? 'opacity-75' : '' }}">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <div
                                            class="flex md:hidden flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                                            @if ($isApproved)
                                                <x-mary-badge value="Approved"
                                                    class="badge-success text-white whitespace-normal h-auto" />
                                                @if ($isComplete)
                                                    <x-mary-badge value="Completed"
                                                        class="badge-info text-white whitespace-normal h-auto" />
                                                @endif
                                            @elseif($isForRevision)
                                                <x-mary-badge value="For Revision"
                                                    class="badge-warning text-white whitespace-normal h-auto" />
                                            @elseif($isCancelled)
                                                <x-mary-badge value="Cancelled"
                                                    class="badge-error text-white whitespace-normal h-auto" />
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h4 class="text-lg font-semibold">{{ $ticket->title }}</h4>
                                        <div
                                            class="hidden md:flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                                            @if ($isApproved)
                                                <x-mary-badge value="Approved"
                                                    class="badge-success text-white whitespace-normal h-auto" />
                                                @if ($isComplete)
                                                    <x-mary-badge value="Completed"
                                                        class="badge-info text-white whitespace-normal h-auto" />
                                                @endif
                                            @elseif($isForRevision)
                                                <x-mary-badge value="For Revision"
                                                    class="badge-warning text-white whitespace-normal h-auto" />
                                            @elseif($isCancelled)
                                                <x-mary-badge value="Cancelled"
                                                    class="badge-error text-white whitespace-normal h-auto" />
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-gray-600 mb-3 break-all hyphens-auto">{{ $ticket->description }}
                                    </p>

                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">
                                                {{ $isForRevision ? 'Proposed Date' : 'Event Date' }}
                                            </p>
                                            <p class="text-sm font-medium">
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
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">Venue</p>
                                            <p class="text-sm font-medium">
                                                {{ $ticket->schedule_venue ?? $ticket->venue_display_name }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">Expected</p>
                                            <p class="text-sm font-medium">
                                                {{ $ticket->total_participants ?? $ticket->plv_participants }}
                                                attendees</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">Type</p>
                                            <p class="text-sm font-medium">{{ $ticket->eventType->type_name }}</p>
                                        </div>
                                    </div>


                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-500">Submitted:</p>
                                            <p class="font-medium">
                                                {{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-500">
                                                {{ $isForRevision ? 'Revision Requested' : ($isCancelled ? 'Cancelled' : 'Approved') }}
                                                :</p>
                                            <p class="font-medium">
                                                {{ \Carbon\Carbon::parse($ticket->updated_at)->format('M d, Y') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-500">Processing Time:</p>
                                            <p class="font-medium">{{ $processingDays }}
                                                {{ Str::plural('day', $processingDays) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col space-y-2 ml-6">
                                    <x-mary-button icon="s-eye" class="btn-sm btn-ghost" tooltip="View Details"
                                        wire:click="openDetailsModal({{ $ticket->ticket_id }})" />
                                    @if ($isForRevision)
                                        {{--                                        <x-mary-button icon="s-document-text" class="btn-sm btn-ghost" --}}
                                        {{--                                                       tooltip="View Feedback"/> --}}
                                        <x-mary-button icon="s-arrow-path" class="btn-sm btn-ghost"
                                            tooltip="Resubmit Modified"
                                            wire:click="resubmitTicket({{ $ticket->ticket_id }})" />
                                    @elseif($isCancelled)
                                        {{--                                        <x-mary-button icon="s-document-text" class="btn-sm btn-ghost" --}}
                                        {{--                                                       tooltip="Cancellation Report"/> --}}
                                    @else
                                        {{--                                        <x-mary-button icon="s-document-arrow-down" class="btn-sm btn-ghost" --}}
                                        {{--                                                       tooltip="Download Report"/> --}}
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
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-500">
                            <x-mary-icon name="o-inbox" class="w-16 h-16 mx-auto mb-3 opacity-50" />
                            <p class="text-lg font-medium mb-1">No event history yet</p>
                            <p class="text-sm">Your approved, needing revision, and cancelled events will appear
                                here</p>
                        </div>
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
