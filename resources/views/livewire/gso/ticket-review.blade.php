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
                                        <button class="btn btn-sm btn-outline btn-emerald"
                                            wire:click="showDetails({{ $ticket['approval_id'] }})">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                            View
                                        </button>
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

    @if ($showDetailsModal && $modalTicket)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDetailsModal">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Ticket Details - {{ $modalTicket['ticket_number'] }}
                            </h3>
                            <button class="text-gray-400 hover:text-gray-600" wire:click="closeDetailsModal">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-semibold text-emerald-700 dark:text-emerald-300 mb-3">Event Information</h4>
                                <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                    <div><strong>Event Name:</strong> {{ $modalTicket['event_name'] }}</div>
                                    <div><strong>Organization:</strong> {{ $modalTicket['organization'] }}</div>
                                    <div><strong>Event Date:</strong> {{ $modalTicket['event_date'] }}</div>
                                    <div><strong>Venue:</strong> {{ $modalTicket['venue'] }}</div>
                                    <div><strong>Total Participants:</strong>
                                        {{ number_format($modalTicket['attendees'] ?? 0) }}</div>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-semibold text-emerald-700 dark:text-emerald-300 mb-3">Request Details</h4>
                                <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                    <div><strong>Request Type:</strong> {{ $modalTicket['request_type'] }}</div>
                                    <div><strong>Status:</strong>
                                        {{ $modalTicket['status_label'] ?? str_replace('_', ' ', $modalTicket['status']) }}
                                    </div>
                                    <div><strong>Submitted:</strong> {{ $modalTicket['submitted_date'] }}</div>
                                    <div><strong>Due Date:</strong> {{ $modalTicket['due_date'] }}</div>
                                    <div><strong>Remarks:</strong> {{ $modalTicket['remarks'] ?: '—' }}</div>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <h4 class="font-semibold text-emerald-700 dark:text-emerald-300 mb-3">Description</h4>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $modalTicket['description'] }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <h4 class="font-semibold text-emerald-700 dark:text-emerald-300 mb-3">Requirements</h4>
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($modalTicket['requirements'] as $requirement)
                                        <span class="badge badge-outline badge-sm">{{ $requirement }}</span>
                                    @empty
                                        <span class="text-sm text-gray-500 dark:text-gray-400">No special requirements
                                            provided.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="btn btn-emerald w-full sm:w-auto" wire:click="closeDetailsModal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>