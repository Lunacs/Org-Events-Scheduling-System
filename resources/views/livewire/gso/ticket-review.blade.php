@php
    $statusDefinitions = $statusDefinitions ?? [
        'pending' => ['key' => 'pending', 'label' => 'Pending'],
        'approved' => ['key' => 'approved', 'label' => 'Approved'],
        'rejected' => ['key' => 'rejected', 'label' => 'Rejected'],
    ];

    $statusOptions = collect($statusDefinitions)
        ->map(fn ($definition) => ['id' => $definition['key'], 'name' => $definition['label']])
        ->prepend(['id' => '', 'name' => 'All Status'])
        ->values()
        ->all();

    $typeOptions = [
        ['id' => '', 'name' => 'All Types'],
        ['id' => 'venue', 'name' => 'Venue Booking'],
        ['id' => 'equipment', 'name' => 'Equipment'],
        ['id' => 'logistics', 'name' => 'Logistics'],
        ['id' => 'catering', 'name' => 'Catering'],
    ];

    $priorityOptions = [
        ['id' => '', 'name' => 'All Priorities'],
        ['id' => 'high', 'name' => 'High'],
        ['id' => 'medium', 'name' => 'Medium'],
        ['id' => 'low', 'name' => 'Low'],
    ];

    $tableHeaders = [
        ['key' => 'ticket_number', 'label' => 'Ticket ID'],
        ['key' => 'event_name', 'label' => 'Event Name'],
        ['key' => 'organization', 'label' => 'Organization'],
        ['key' => 'request_type', 'label' => 'Request Type'],
        ['key' => 'event_date', 'label' => 'Event Date'],
        ['key' => 'priority', 'label' => 'Priority'],
        ['key' => 'status', 'label' => 'Status'],
        ['key' => 'actions', 'label' => 'Actions'],
    ];

    $tableRows = collect($tickets)
        ->map(fn ($ticket) => is_array($ticket) ? $ticket : (array) $ticket)
        ->values()
        ->all();

    $defaultRequestTypeBadge = 'badge-ghost text-base-content';

    $requestTypeBadgeDefaults = [
        'venue booking' => 'badge-primary text-primary-content',
        'venue' => 'badge-primary text-primary-content',
        'equipment' => 'badge-info text-info-content',
        'logistics' => 'badge-secondary text-secondary-content',
        'catering' => 'badge-accent text-accent-content',
    ];

    $requestTypeBadgePalette = [
        'badge-success text-success-content',
        'badge-warning text-warning-content',
        'badge-error text-error-content',
        'badge-neutral text-neutral-content',
        'badge-info text-info-content',
        'badge-secondary text-secondary-content',
        'badge-accent text-accent-content',
        'badge-primary text-primary-content',
    ];

    $requestTypeBadgeMap = $requestTypeBadgeDefaults;
    $paletteIndex = 0;

    foreach (collect($tableRows)->pluck('request_type')->filter()->unique()->values() as $typeLabel) {
        $lookupKey = \Illuminate\Support\Str::of($typeLabel)->lower()->toString();

        if (! array_key_exists($lookupKey, $requestTypeBadgeMap)) {
            $requestTypeBadgeMap[$lookupKey] = $requestTypeBadgePalette[$paletteIndex % count($requestTypeBadgePalette)];
            $paletteIndex++;
        }
    }

    $tableRows = collect($tableRows)
        ->map(function ($row) use ($requestTypeBadgeMap, $defaultRequestTypeBadge) {
            $typeKey = \Illuminate\Support\Str::of($row['request_type'] ?? '')->lower()->toString();
            $row['request_type_badge_class'] = $requestTypeBadgeMap[$typeKey] ?? $defaultRequestTypeBadge;

            return $row;
        })
        ->values()
        ->all();
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <x-mary-card title="Filter Tickets" subtitle="Refine your assigned requests">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-mary-select wire:model.live="filterType" label="Request Type" :options="$typeOptions"
                    option-value="id" option-label="name" />

                <x-mary-select wire:model.live="filterPriority" label="Priority" :options="$priorityOptions"
                    option-value="id" option-label="name" />

                <x-mary-select wire:model.live="filterStatus" label="Status" :options="$statusOptions"
                    option-value="id" option-label="name" />

                <x-mary-input wire:model.debounce.300ms="search" label="Search"
                    placeholder="Search by event name...">
                    <x-slot:prepend>
                        <x-mary-icon name="s-magnifying-glass" class="w-5 h-5 text-base-content/50" />
                    </x-slot:prepend>
                </x-mary-input>
            </div>
        </x-mary-card>

        <x-mary-card title="Assigned Tickets">
            <x-slot:menu>
                <span class="text-sm text-base-content/60">
                    Showing {{ number_format($tickets->count()) }} of {{ number_format($totalTickets) }} tickets
                </span>
            </x-slot:menu>

            @if ($tickets->count() > 0)
                <x-mary-table :headers="$tableHeaders" :rows="$tableRows">
                    @scope('cell_ticket_number', $row)
                        <span class="text-sm font-semibold text-base-content">{{ $row['ticket_number'] }}</span>
                    @endscope

                    @scope('cell_event_name', $row)
                        <span class="text-sm font-medium text-base-content">{{ $row['event_name'] }}</span>
                    @endscope

                    @scope('cell_organization', $row)
                        <span class="text-sm text-base-content/70">{{ $row['organization'] }}</span>
                    @endscope

                    @scope('cell_request_type', $row)
                        <x-mary-badge :value="$row['request_type'] ?? 'N/A'"
                            class="{{ $row['request_type_badge_class'] ?? 'badge-ghost text-base-content' }} border-none badge-lg h-auto flex-wrap whitespace-normal leading-tight px-3 py-1" />
                    @endscope

                    @scope('cell_event_date', $row)
                        <span class="text-sm text-base-content/70">{{ $row['event_date'] }}</span>
                    @endscope

                    @scope('cell_priority', $row)
                        @php
                            $priorityKey = \Illuminate\Support\Str::of($row['priority'] ?? 'low')->lower()->toString();
                            $priorityClass = match ($priorityKey) {
                                'high' => 'badge-error text-error-content',
                                'medium' => 'badge-warning text-warning-content',
                                'low' => 'badge-success text-success-content',
                                default => 'badge-ghost text-base-content',
                            };
                        @endphp
                        <x-mary-badge :value="\Illuminate\Support\Str::title($priorityKey)"
                            class="{{ $priorityClass }} border-none badge-lg h-auto flex-wrap whitespace-normal leading-tight px-3 py-1" />
                    @endscope

                    @scope('cell_status', $row)
                        @php
                            $statusKey = \Illuminate\Support\Str::of($row['status'] ?? 'pending')->lower()->toString();
                            $statusClass = match ($statusKey) {
                                'pending' => 'badge-warning text-warning-content',
                                'approved' => 'badge-success text-success-content',
                                'rejected' => 'badge-error text-error-content',
                                default => 'badge-ghost text-base-content',
                            };
                            $statusLabel = $row['status_label'] ?? \Illuminate\Support\Str::of($statusKey)->replace('_', ' ')->title()->toString();
                            $statusDetail = $row['remarks'] ?? null;
                        @endphp
                        <div class="flex flex-col gap-1 max-w-xs">
                            <x-mary-badge :value="$statusLabel"
                                class="{{ $statusClass }} border-none badge-lg h-auto flex-wrap whitespace-normal leading-tight px-3 py-1" />
                            @if ($statusDetail)
                                <span class="text-xs text-base-content/60">{{ $statusDetail }}</span>
                            @endif
                        </div>
                    @endscope

                    @scope('cell_actions', $row)
                        <x-mary-button label="View" icon="s-eye" class="btn-outline btn-emerald btn-sm"
                            link="{{ route('gso.ticket-details', ['ticket' => $row['ticket_id'], 'office' => $row['office_id'] ?? null]) }}" wire:navigate />
                    @endscope
                </x-mary-table>
            @else
                <div class="text-center py-12 space-y-2">
                    <x-mary-icon name="s-clipboard-document" class="w-12 h-12 mx-auto text-base-content/40" />
                    <p class="text-sm font-medium text-base-content">No approval requests</p>
                    <p class="text-sm text-base-content/60">No tickets match your current filters.</p>
                </div>
            @endif
        </x-mary-card>
    </div>
</div>