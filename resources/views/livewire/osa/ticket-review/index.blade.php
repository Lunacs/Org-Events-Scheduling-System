@php
    $statusOptions = [
        ['id' => '', 'name' => 'All active tickets'],
        ['id' => 'received', 'name' => \App\Support\TicketStatus::label('received')],
        ['id' => 'amended', 'name' => \App\Support\TicketStatus::label('amended')],
        ['id' => 'gso_review', 'name' => \App\Support\TicketStatus::label('gso_review')],
        ['id' => 'pending_osa_approval', 'name' => \App\Support\TicketStatus::label('pending_osa_approval')],
        ['id' => 'for_revision', 'name' => \App\Support\TicketStatus::label('for_revision')],
        ['id' => 'approved', 'name' => \App\Support\TicketStatus::label('approved')],
        ['id' => 'completed', 'name' => \App\Support\TicketStatus::label('completed')],
    ];
    $filtering = 'search,statusFilter,organizationFilter,clearFilters';
@endphp

<div class="space-y-6">
    <x-ui.page-heading title="Ticket review"
        subtitle="Open a request to approve it, forward it to GSO, or ask the organization for changes.">
        <x-slot:meta>
            <span class="badge badge-soft tabular-nums">
                {{ number_format($tickets->total()) }} {{ Str::plural('ticket', $tickets->total()) }}
            </span>
        </x-slot:meta>
    </x-ui.page-heading>

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
        <x-ui.input label="Search" wire:model.live.debounce.300ms="search" placeholder="Event title or ticket number…"
            icon="o-magnifying-glass" type="search" autocomplete="off" />

        <x-ui.select label="Status" wire:model.live="statusFilter" :options="$statusOptions" />

        <x-ui.select label="Organization" wire:model.live="organizationFilter" placeholder="All organizations"
            :options="$this->organizations->map(fn($org) => [
                'org_id' => $org->org_id,
                'org_name' => $org->org_name . ($org->deleted_at ? ' (Deleted)' : ''),
            ])" option-value="org_id" option-label="org_name" />

        <div class="flex items-end">
            <x-ui.button label="Clear filters" icon="o-x-mark" class="btn-ghost"
                x-cloak x-show="$wire.search || $wire.statusFilter || $wire.organizationFilter"
                wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters" />
        </div>
    </div>

    {{-- Queue --}}
    <section aria-label="Tickets" class="relative overflow-hidden rounded-box border border-base-300 bg-base-100"
        wire:loading.class="opacity-60" wire:target="{{ $filtering }}">
        <div wire:loading.flex wire:target="{{ $filtering }}"
            class="absolute inset-x-0 top-0 z-10 h-0.5 overflow-hidden bg-base-200" aria-hidden="true">
            <div class="h-full w-1/3 animate-pulse bg-accent"></div>
        </div>

        @if ($tickets->isEmpty())
            <x-ui.empty-state icon="o-inbox" title="No tickets match"
                description="Try a different search or clear the filters. New requests from organizations appear here automatically.">
            </x-ui.empty-state>
        @else
            {{-- Desktop table --}}
            <div class="hidden md:block">
                <x-ui.table :headers="[
                    ['key' => 'event', 'label' => 'Event'],
                    ['key' => 'date', 'label' => 'Event date'],
                    ['key' => 'venue', 'label' => 'Venue'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'submitted', 'label' => 'Submitted'],
                    ['key' => 'action', 'label' => '', 'class' => 'w-0'],
                ]" :paginate="false">
                    @foreach ($tickets as $ticket)
                        @php
                            $orgDeleted = $ticket->user?->studentOrganization?->trashed();
                            $organization = $orgDeleted ? 'Deleted organization' : ($ticket->user?->studentOrganization?->org_name ?? 'No organization');
                            $schedule = $ticket->events->first()?->eventSchedules->first();
                            $eventDate = $schedule?->start_date ?? ($ticket->date_from ? \Carbon\Carbon::parse($ticket->date_from) : null);
                            $venue = $schedule?->venue ?? $ticket->venue_display_name;
                            $url = route('osa.ticket-review.show', $ticket->ticket_number);
                        @endphp
                        <tr wire:key="ticket-review-{{ $ticket->ticket_id }}" class="hover:bg-base-200/50">
                            <td class="max-w-md">
                                <a href="{{ $url }}" wire:navigate class="font-medium text-base-content hover:underline">
                                    {{ $ticket->title }}
                                </a>
                                <p class="mt-0.5 flex flex-wrap items-center gap-x-2 text-xs text-base-content/70">
                                    <span class="font-mono">{{ $ticket->ticket_number }}</span>
                                    <span aria-hidden="true">&middot;</span>
                                    <span @class(['truncate', 'italic' => $orgDeleted])>{{ $organization }}</span>
                                    @if ($ticket->attachments->count() > 0)
                                        <span aria-hidden="true">&middot;</span>
                                        <span class="inline-flex items-center gap-1">
                                            <x-ui.icon name="o-paper-clip" class="size-3.5" aria-hidden="true" />
                                            {{ $ticket->attachments->count() }}
                                            <span class="sr-only">{{ Str::plural('attachment', $ticket->attachments->count()) }}</span>
                                        </span>
                                    @endif
                                </p>
                            </td>
                            <td class="whitespace-nowrap">{{ $eventDate?->format('M j, Y') ?? 'Not set' }}</td>
                            <td class="max-w-48 truncate text-base-content/80">{{ $venue ?? 'Not set' }}</td>
                            <td><x-tickets.progress-badge :status="$ticket->status" size="sm" /></td>
                            <td class="whitespace-nowrap text-base-content/70">
                                <time datetime="{{ $ticket->created_at?->toIso8601String() }}"
                                    title="{{ $ticket->created_at?->timezone('Asia/Manila')->format('M j, Y g:i A') }}">
                                    {{ $ticket->created_at?->diffForHumans() ?? '—' }}
                                </time>
                            </td>
                            <td class="text-end">
                                <x-ui.button label="Review" link="{{ $url }}" class="btn-sm"
                                    aria-label="Review {{ $ticket->title }}" />
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>

            {{-- Mobile list --}}
            <ul class="divide-y divide-base-300 md:hidden">
                @foreach ($tickets as $ticket)
                    @php
                        $schedule = $ticket->events->first()?->eventSchedules->first();
                        $eventDate = $schedule?->start_date ?? ($ticket->date_from ? \Carbon\Carbon::parse($ticket->date_from) : null);
                    @endphp
                    <li wire:key="ticket-review-m-{{ $ticket->ticket_id }}">
                        <a href="{{ route('osa.ticket-review.show', $ticket->ticket_number) }}" wire:navigate
                            class="block px-4 py-3 active:bg-base-200">
                            <div class="flex items-start justify-between gap-3">
                                <p class="min-w-0 text-sm font-medium text-base-content">{{ $ticket->title }}</p>
                                <x-tickets.progress-badge :status="$ticket->status" size="sm" class="shrink-0" />
                            </div>
                            <p class="mt-1 text-xs text-base-content/70">
                                {{ $ticket->user?->studentOrganization?->org_name ?? 'No organization' }}
                            </p>
                            <p class="mt-0.5 text-xs text-base-content/70">
                                <span class="font-mono">{{ $ticket->ticket_number }}</span> &middot;
                                {{ $eventDate?->format('M j, Y') ?? 'No date' }}
                            </p>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <x-tickets.ticket-pagination :tickets="$tickets" />
</div>
