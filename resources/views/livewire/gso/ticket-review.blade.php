@php
    $statusDefinitions = $statusDefinitions ?? [
        'pending' => ['key' => 'pending', 'label' => 'Pending'],
        'approved' => ['key' => 'approved', 'label' => 'Approved'],
        'rejected' => ['key' => 'rejected', 'label' => 'Rejected'],
    ];

    $statusOptions = [
        ['id' => '', 'name' => 'All Status'],
    ];

    foreach ($statusDefinitions as $definition) {
        $statusOptions[] = [
            'id' => $definition['key'],
            'name' => $definition['label'],
        ];
    }

    $statusBadges = [
        'pending' => 'badge-warning',
        'approved' => 'badge-success',
        'rejected' => 'badge-error',
    ];

    $priorityBadges = [
        'high' => 'badge-error',
        'medium' => 'badge-warning',
        'low' => 'badge-success',
    ];

    $typeBadges = [
        'venue booking' => 'badge-success',
        'equipment' => 'badge-info',
        'logistics' => 'badge-warning',
        'catering' => 'badge-primary',
    ];
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Filter Tickets</h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <x-mary-select wire:model.live="filterType" label="Request Type" :options="[
        ['id' => '', 'name' => 'All Types'],
        ['id' => 'venue', 'name' => 'Venue Booking'],
        ['id' => 'equipment', 'name' => 'Equipment'],
        ['id' => 'logistics', 'name' => 'Logistics'],
        ['id' => 'catering', 'name' => 'Catering'],
    ]" option-value="id" option-label="name" class="select-emerald" />

                    <x-mary-select wire:model.live="filterPriority" label="Priority" :options="[
        ['id' => '', 'name' => 'All Priorities'],
        ['id' => 'high', 'name' => 'High'],
        ['id' => 'medium', 'name' => 'Medium'],
        ['id' => 'low', 'name' => 'Low'],
    ]" option-value="id" option-label="name"
                        class="select-emerald" />

                    <x-mary-select wire:model.live="filterStatus" label="Status" :options="$statusOptions"
                        option-value="id" option-label="name" class="select-emerald" />

                    <x-mary-input wire:model.debounce.300ms="search" label="Search"
                        placeholder="Search by event name..." class="input-emerald">
                        <x-slot:prepend>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </x-slot:prepend>
                    </x-mary-input>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Assigned Tickets</h3>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Showing {{ number_format($tickets->count()) }} of {{ number_format($totalTickets) }} tickets
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                <th class="text-emerald-700 dark:text-emerald-300">Ticket ID</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Event Name</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Organization</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Request Type</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Event Date</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Priority</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Status</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tickets as $ticket)
                                @php
                                    $statusKey = $ticket['status'] ?? 'pending';
                                    $statusLabel = $ticket['status_label'] ?? ucfirst(str_replace('_', ' ', $statusKey));
                                    $statusDetail = $ticket['remarks'] ?? null;
                                    $priorityKey = $ticket['priority'] ?? 'low';
                                    $typeKey = \Illuminate\Support\Str::of($ticket['request_type'] ?? '')->lower()->toString();
                                @endphp
                                <tr class="hover:bg-emerald-50 dark:hover:bg-emerald-900/10"
                                    wire:key="ticket-{{ $ticket['approval_id'] }}">
                                    <td class="align-top pt-6">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $ticket['ticket_number'] }}
                                        </div>
                                    </td>
                                    <td class="align-top pt-6">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $ticket['event_name'] }}
                                        </div>
                                    </td>
                                    <td class="align-top pt-6 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $ticket['organization'] }}
                                    </td>
                                    <td class="align-top pt-6">
                                        <span
                                            class="badge {{ $typeBadges[$typeKey] ?? 'badge-ghost' }} h-auto min-h-[1.75rem] whitespace-normal break-words text-left leading-tight px-3 py-1">
                                            {{ $ticket['request_type'] }}
                                        </span>
                                    </td>
                                    <td class="align-top pt-6 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $ticket['event_date'] }}
                                    </td>
                                    <td class="align-top pt-6">
                                        <span
                                            class="badge {{ $priorityBadges[$priorityKey] ?? 'badge-ghost' }} h-auto min-h-[1.75rem] whitespace-normal break-words text-left leading-tight px-4 py-1">
                                            {{ $ticket['priority_label'] ?? ucfirst($priorityKey) }}
                                        </span>
                                    </td>
                                    <td class="align-top pt-6">
                                        <div class="flex flex-col space-y-1">
                                            <span
                                                class="badge {{ $statusBadges[$statusKey] ?? 'badge-ghost' }} h-auto min-h-[1.75rem] whitespace-normal break-words text-left leading-tight px-4 py-1">
                                                {{ $statusLabel }}
                                            </span>
                                            @if ($statusDetail)
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400 max-w-[12rem] break-words">
                                                    {{ $statusDetail }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-top pt-4">
                                        <a href="{{ route('gso.ticket-details', ['ticket' => $ticket['ticket_id']]) }}"
                                            class="btn btn-sm btn-outline btn-emerald" wire:navigate>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-12">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No approval
                                            requests</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No tickets match your
                                            current filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>