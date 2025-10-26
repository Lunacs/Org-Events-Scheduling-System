<div>
    <x-mary-toast />

    <style>
        [data-tip] {
            position: relative;
        }

        [data-tip]:hover::after {
            content: attr(data-tip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.5rem;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            white-space: nowrap;
            z-index: 1000;
            margin-bottom: 0.25rem;
        }
    </style>

    {{-- Header --}}
    @persist('ticket-management-header')
        <div class="mb-8">
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-base-content">Ticket Management</h1>
                        <p class="text-base-content/70 mt-1">View and manage all submitted tickets from Student Organizations
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-mary-badge value="{{ $tickets->total() }} Total Tickets" class="badge-primary" />
                    </div>
                </div>
            </div>
        </div>
    @endpersist

    {{-- Filters --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-mary-input wire:model.live.debounce.300ms="search" placeholder="Search tickets..."
                icon="o-magnifying-glass" clearable />

            <x-mary-select wire:model.live="statusFilter" placeholder="Filter by Status" :options="[
                ['id' => '', 'name' => 'All Statuses'],
                ['id' => 'received', 'name' => 'Received'],
                ['id' => 'gso_review', 'name' => 'GSO Review'],
                ['id' => 'for_rescheduling', 'name' => 'For Rescheduling'],
                ['id' => 'rescheduled', 'name' => 'Rescheduled'],
                ['id' => 'needs_revision', 'name' => 'Needs Revision'],
                ['id' => 'amended', 'name' => 'Amended'],
                ['id' => 'approved', 'name' => 'Approved'],
                ['id' => 'rejected', 'name' => 'Rejected'],
            ]"
                option-value="id" option-label="name" />

            <x-mary-select wire:model.live="organizationFilter" placeholder="Filter by Organization" :options="App\Models\Student_Organization::select('org_id', 'org_name')->get()"
                option-value="org_id" option-label="org_name" />

            <div class="flex gap-2">
                <x-mary-input wire:model.live="dateFilter" type="date" placeholder="Filter by Date" />
                <x-mary-button wire:click="clearFilters" class="btn-ghost" icon="o-x-mark" tooltip="Clear Filters">
                    <span wire:loading.remove wire:target="clearFilters">Clear</span>
                    <span wire:loading wire:target="clearFilters">Clearing...</span>
                </x-mary-button>
            </div>
        </div>
    </div>

    {{-- Tickets Table --}}
    <div class="bg-base-100 rounded-box shadow-lg overflow-hidden">
        <div class="overflow-x-auto" wire:loading.class="opacity-50"
            wire:target="search,statusFilter,organizationFilter,dateFilter">
            <table class="table table-zebra w-full">
                <thead class="bg-base-200">
                    <tr>
                        <th>Ticket #</th>
                        <th>Event Title</th>
                        <th>Organization</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr class="hover:cursor-pointer hover:border-l-2 hover:border-l-primary"
                            wire:key="ticket-{{ $ticket->ticket_id }}"
                            onclick="window.location='{{ route('osa.ticket-review.show', $ticket->ticket_number) }}'">
                            <td>
                                <span class="font-mono text-sm">#{{ $ticket->ticket_number }}</span>
                            </td>
                            <td>
                                <div class="font-semibold">{{ $ticket->title }}</div>
                                <div class="text-sm text-base-content/70">{{ Str::limit($ticket->description, 60) }}
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder flex justify-center items-center">
                                        <div class="bg-primary text-primary-content rounded-full w-8">
                                            <span
                                                class="text-xs">{{ $ticket->user->studentOrganization->org_code }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">
                                            {{ $ticket->user->studentOrganization->org_name ?? 'No Organization' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'received' => 'badge-info',
                                        'gso_review' => 'badge-secondary',
                                        'for_rescheduling' => 'badge-warning',
                                        'rescheduled' => 'badge-success',
                                        'needs_revision' => 'badge-warning',
                                        'amended' => 'badge-info',
                                        'approved' => 'badge-success',
                                        'rejected' => 'badge-error',
                                    ];
                                @endphp
                                <x-mary-badge value="{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}"
                                    class="{{ $statusClasses[$ticket->status] ?? 'badge-neutral' }}" />
                            </td>
                            <td>
                                <div>{{ $ticket->created_at?->format('M d, Y') ?? 'N/A' }}</div>
                                <div class="text-sm text-base-content/70">
                                    {{ $ticket->created_at?->format('h:i A') ?? '' }}
                                </div>
                            </td>
                            <td onclick="event.stopPropagation()">
                                <div class="flex gap-1 justify-center">
                                    <button wire:click="approveTicket({{ $ticket->ticket_id }})"
                                        class="btn btn-sm btn-success btn-circle" data-tip="Approve"
                                        onclick="event.stopPropagation()">
                                        <x-mary-icon name="o-check" class="w-4 h-4" />
                                    </button>

                                    <button wire:click="rejectTicket({{ $ticket->ticket_id }})"
                                        class="btn btn-sm btn-error btn-circle" data-tip="Reject"
                                        onclick="event.stopPropagation()">
                                        <x-mary-icon name="o-x-mark" class="w-4 h-4" />
                                    </button>

                                    <button wire:click="rescheduleTicket({{ $ticket->ticket_id }})"
                                        class="btn btn-sm btn-warning btn-circle" data-tip="Reschedule"
                                        onclick="event.stopPropagation()">
                                        <x-mary-icon name="o-calendar" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8">
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

        {{-- Pagination --}}
        @if ($tickets->hasPages())
            <div class="p-4 border-t border-base-300">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
