@php
    $statusDefinitions = $statusDefinitions ?? [
        'pending' => ['key' => 'pending', 'label' => 'Pending'],
        'approved' => ['key' => 'approved', 'label' => 'Approved'],
        'rejected' => ['key' => 'rejected', 'label' => 'Rejected'],
    ];

    $statusOptions = collect($statusDefinitions)
        ->map(fn ($definition) => ['id' => $definition['key'], 'name' => $definition['label']])
        ->prepend(['id' => '', 'name' => 'All Statuses'])
        ->values()
        ->all();

    $typeOptions = [
        ['id' => '', 'name' => 'All Request Types'],
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

    $ticketsCollection = collect($tickets)
        ->map(fn ($ticket) => is_array($ticket) ? $ticket : (array) $ticket)
        ->values();
@endphp

<div>
    <x-mary-toast />

    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-base-content">Ticket Management</h1>
                    <p class="text-base-content/70 mt-1">Review and manage ticket requests assigned to the General Services Office.</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-mary-badge value="{{ number_format($totalTickets) }} Total Tickets" class="badge-primary" />
                </div>
            </div>
        </div>
    </div>

    <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-mary-input wire:model.live.debounce.300ms="search" placeholder="Search tickets..."
                icon="o-magnifying-glass" clearable />

            <x-mary-select wire:model.live="filterStatus" placeholder="Filter by Status" :options="$statusOptions"
                option-value="id" option-label="name" />

            <x-mary-select wire:model.live="filterType" placeholder="Filter by Request Type" :options="$typeOptions"
                option-value="id" option-label="name" />

            <x-mary-select wire:model.live="filterPriority" placeholder="Filter by Priority" :options="$priorityOptions"
                option-value="id" option-label="name" />
        </div>
    </div>

    <div class="bg-base-100 rounded-box shadow-lg overflow-hidden">
        <div class="overflow-x-auto" wire:loading.class="opacity-50"
            wire:target="search,filterStatus,filterType,filterPriority">
            <table class="table table-zebra w-full">
                <thead class="bg-base-200">
                    <tr>
                        <th>Ticket #</th>
                        <th>Event Title</th>
                        <th>Organization</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ticketsCollection as $ticket)
                        @php
                            $ticketNumber = $ticket['ticket_number'] ?? 'N/A';
                            $eventTitle = $ticket['event_name'] ?? 'Untitled Event';
                            $eventDescription = $ticket['description'] ?? null;
                            $organization = $ticket['organization'] ?? 'N/A';
                            $requestType = $ticket['request_type'] ?? 'N/A';
                            $priorityKey = \Illuminate\Support\Str::of($ticket['priority'] ?? 'low')->lower()->toString();
                            $statusKey = \Illuminate\Support\Str::of($ticket['status'] ?? 'pending')->lower()->toString();
                            $statusLabel = $ticket['status_label']
                                ?? \Illuminate\Support\Str::of($statusKey)->replace('_', ' ')->title()->toString();
                            $statusClasses = [
                                'pending' => 'badge-warning',
                                'approved' => 'badge-success',
                                'rejected' => 'badge-error',
                            ];
                            $priorityClasses = [
                                'high' => 'badge-error',
                                'medium' => 'badge-warning',
                                'low' => 'badge-success',
                            ];
                            $statusBadgeClass = $statusClasses[$statusKey] ?? 'badge-neutral';
                            $priorityBadgeClass = $priorityClasses[$priorityKey] ?? 'badge-neutral';
                            $priorityLabel = \Illuminate\Support\Str::title($priorityKey);
                            $submittedDate = $ticket['submitted_date'] ?? 'N/A';
                            $eventDate = $ticket['event_date'] ?? null;
                            $detailUrl = $ticket['ticket_id']
                                ? route('gso.ticket-details', [
                                    'ticket' => $ticket['ticket_id'],
                                    'office' => $ticket['office_id'] ?? null,
                                    'approval' => $ticket['approval_id'] ?? null,
                                ])
                                : null;
                            $orgCode = \Illuminate\Support\Str::of($organization)
                                ->replaceMatches('/[^A-Za-z0-9]/', '')
                                ->upper()
                                ->substr(0, 3)
                                ->toString();
                            $orgCode = $orgCode !== '' ? $orgCode : 'ORG';
                        @endphp
                        <tr class="{{ $detailUrl ? 'hover:cursor-pointer hover:bg-base-200 transition-colors duration-200' : '' }}"
                            wire:key="ticket-{{ $ticket['ticket_id'] ?? $loop->index }}"
                            @if($detailUrl)
                                onclick="window.location='{{ $detailUrl }}'"
                                title="Ticket: {{ $eventTitle }} | Organization: {{ $organization }} | Status: {{ $statusLabel }}"
                            @endif
                        >
                            <td>
                                <span class="font-mono text-sm">#{{ $ticketNumber }}</span>
                            </td>
                            <td>
                                <div class="font-semibold text-base-content">{{ $eventTitle }}</div>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <x-mary-badge value="{{ $requestType }}" class="badge-neutral badge-sm" />
                                    <x-mary-badge value="{{ $priorityLabel }}" class="{{ $priorityBadgeClass }} badge-sm text-white" />
                                </div>
                                @if ($eventDescription)
                                    <div class="text-sm text-base-content/70 mt-1">{{ \Illuminate\Support\Str::limit($eventDescription, 60) }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder flex justify-center items-center">
                                        <div class="bg-primary text-primary-content rounded-full w-8 h-8 flex items-center justify-center">
                                            <span class="text-xs font-semibold">{{ $orgCode }}</span>
                                        </div>
                                    </div>
                                    <div class="font-medium text-base-content">{{ $organization }}</div>
                                </div>
                            </td>
                            <td>
                                <x-mary-badge value="{{ $statusLabel }}" class="{{ $statusBadgeClass }} text-white badge-md w-40 justify-center truncate" />
                            </td>
                            <td>
                                <div>{{ $submittedDate }}</div>
                                @if ($eventDate)
                                    <div class="text-sm text-base-content/70">Event: {{ $eventDate }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8">
                                <div class="flex flex-col items-center gap-2">
                                    <x-mary-icon name="o-document-text" class="w-12 h-12 text-base-content/30" />
                                    <span class="text-base-content/70">No tickets found</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>