@php
    $statusDefinitions = $statusDefinitions ?? [
        'pending' => ['key' => 'pending', 'label' => 'Pending'],
        'approved' => ['key' => 'approved', 'label' => 'Approved'],
        'rejected' => ['key' => 'rejected', 'label' => 'Rejected'],
    ];

    $statusOptions = collect($statusDefinitions)
        ->map(fn($definition) => ['id' => $definition['key'], 'name' => $definition['label']])
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

    $tableRows = collect($tickets)->map(fn($ticket) => is_array($ticket) ? $ticket : (array) $ticket)->values()->all();

    $defaultRequestTypeBadge = 'badge-ghost text-base-content';

    $requestTypeBadgeDefaults = [
        'venue booking' => 'badge-primary text-white',
        'venue' => 'badge-primary text-white',
        'equipment' => 'badge-info text-white',
        'logistics' => 'badge-secondary text-white',
        'catering' => 'badge-accent text-white',
    ];

    $requestTypeBadgePalette = [
        'badge-success text-white',
        'badge-warning text-white',
        'badge-error text-white',
        'badge-neutral text-white',
        'badge-info text-white',
        'badge-secondary text-white',
        'badge-accent text-white',
        'badge-primary text-white',
    ];

    $requestTypeBadgeMap = $requestTypeBadgeDefaults;
    $paletteIndex = 0;

    foreach (collect($tableRows)->pluck('request_type')->filter()->unique()->values() as $typeLabel) {
        $lookupKey = \Illuminate\Support\Str::of($typeLabel)->lower()->toString();

        if (!array_key_exists($lookupKey, $requestTypeBadgeMap)) {
            $requestTypeBadgeMap[$lookupKey] =
                $requestTypeBadgePalette[$paletteIndex % count($requestTypeBadgePalette)];
            $paletteIndex++;
        }
    }

    $tableRows = collect($tableRows)
        ->map(function ($row) use ($requestTypeBadgeMap, $defaultRequestTypeBadge) {
            $typeKey = \Illuminate\Support\Str::of($row['request_type'] ?? '')
                ->lower()
                ->toString();
            $row['request_type_badge_class'] = $requestTypeBadgeMap[$typeKey] ?? $defaultRequestTypeBadge;

            return $row;
        })
        ->values()
        ->all();
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <x-mary-card title="Filter Tickets" subtitle="Refine your assigned requests">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-64">
                    <x-mary-input wire:model.defer="search" label="Search"
                        placeholder="Search by event name, ticket number, organization..." class="input-emerald">
                        <x-slot:prepend>
                            <svg class="w-5 h-5 text-base-content/40" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </x-slot:prepend>
                    </x-mary-input>
                </div>

                <div>
                    <x-mary-select wire:model.live="filterStatus" label="Status" :options="$statusOptions" option-value="id"
                        option-label="name" class="select-emerald" />
                </div>

                <div>
                    <x-mary-select wire:model.live="filterType" label="Type" :options="$typeOptions" option-value="id"
                        option-label="name" class="select-emerald" />
                </div>

                <div>
                    <x-mary-select wire:model.live="filterPriority" label="Priority" :options="$priorityOptions" option-value="id"
                        option-label="name" class="select-emerald" />
                </div>
            </div>
        </x-mary-card>

        <x-mary-card title="Assigned Tickets">
            @if (count($tickets) > 0)
                <x-mary-table :headers="$tableHeaders" :rows="$tableRows">
                    @scope('cell_ticket_number', $row)
                        <span class="text-sm font-medium text-base-content/80">{{ $row['ticket_number'] }}</span>
                    @endscope

                    @scope('cell_event_name', $row)
                        <div class="font-medium text-sm text-base-content/80">{{ $row['event_name'] }}</div>
                    @endscope

                    @scope('cell_organization', $row)
                        <span class="text-sm font-medium text-base-content/80">{{ $row['organization'] }}</span>
                    @endscope

                    @scope('cell_request_type', $row)
                        <x-mary-badge :value="$row['request_type'] ?? 'N/A'"
                            class="{{ $row['request_type_badge_class'] ?? 'badge-ghost text-base-content' }} border-none badge-lg h-auto flex-wrap whitespace-normal leading-tight px-3 py-1" />
                    @endscope

                    @scope('cell_event_date', $row)
                        <span class="text-sm font-medium text-base-content/80">{{ $row['event_date'] }}</span>
                    @endscope

                    @scope('cell_priority', $row)
                        @php
                            $priorityKey = \Illuminate\Support\Str::of($row['priority'] ?? 'low')
                                ->lower()
                                ->toString();
                            $priorityClass = match ($priorityKey) {
                                'high' => 'badge-error text-white',
                                'medium' => 'badge-warning text-white',
                                'low' => 'badge-success text-white',
                                default => 'badge-ghost text-base-content',
                            };
                        @endphp
                        <x-mary-badge :value="\Illuminate\Support\Str::title($priorityKey)"
                            class="{{ $priorityClass }} border-none badge-lg h-auto flex-wrap whitespace-normal leading-tight px-3 py-1" />
                    @endscope

                    @scope('cell_status', $row)
                        @php
                            $statusKey = \Illuminate\Support\Str::of($row['status'] ?? 'pending')
                                ->lower()
                                ->toString();
                            $statusClass = match ($statusKey) {
                                'pending' => 'badge-warning text-white',
                                'approved' => 'badge-success text-white',
                                'rejected' => 'badge-error text-white',
                                default => 'badge-ghost text-base-content',
                            };
                            $statusLabel =
                                $row['status_label'] ??
                                \Illuminate\Support\Str::of($statusKey)->replace('_', ' ')->title()->toString();
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
                            link="{{ route('gso.ticket-details', ['ticket' => $row['ticket_id'], 'office' => $row['office_id'] ?? null]) }}"
                            wire:navigate />
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
