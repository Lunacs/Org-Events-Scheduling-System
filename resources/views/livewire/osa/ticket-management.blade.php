<div x-data="{
    firstLoad: true,
    show: false,
    processingTickets: new Set(),
    optimisticUpdate(ticketId, action) {
        this.processingTickets.add(ticketId);
        // Show instant feedback
        const row = document.querySelector(`[wire\\:key='ticket-${ticketId}']`);
        if (row) {
            row.style.opacity = '0.5';
            row.style.pointerEvents = 'none';
        }
    },
    resetOptimistic(ticketId) {
        this.processingTickets.delete(ticketId);
        const row = document.querySelector(`[wire\\:key='ticket-${ticketId}']`);
        if (row) {
            row.style.opacity = '1';
            row.style.pointerEvents = 'auto';
        }
    }
}" x-init="$nextTick(() => { firstLoad = false;
    show = true; })">

    {{-- Skeleton Loading State (First Load Only) --}}
    <div x-show="firstLoad" x-cloak>
        @include('livewire.osa.placeholders.ticket-management')
    </div>

    {{-- Actual Content --}}
    <div x-show="!firstLoad" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
        <x-mary-toast />

        {{-- Header --}}
        @persist('ticket-management-header')
            <div class="mb-8">
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-base-content">Ticket Management</h1>
                            <p class="text-base-content/70 mt-1">View and manage all submitted tickets from Student
                                Organizations
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
                <div x-data="{ searching: false }">
                    <x-mary-input wire:model.live.debounce.300ms="search" placeholder="Search tickets..."
                        icon="o-magnifying-glass" clearable @input="searching = true"
                        x-on:livewire:updated="searching = false" />
                    <div x-show="searching" x-cloak class="text-xs text-gray-500 mt-1">
                        <span class="loading loading-spinner loading-xs"></span>
                        Searching...
                    </div>
                </div>

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

                <x-mary-select wire:model.live="organizationFilter" placeholder="Filter by Organization"
                    :options="$organizations" option-value="org_id" option-label="org_name" />

                <div class="flex gap-2">
                    <x-mary-input wire:model.live="dateFilter" type="date" placeholder="Filter by Date" />
                    <x-mary-button wire:click="clearFilters" class="btn-ghost" icon="o-x-mark" tooltip="Clear Filters">
                        <span wire:loading.remove wire:target="clearFilters">Clear</span>
                        <span wire:loading wire:target="clearFilters">
                            <span class="loading loading-spinner loading-xs"></span>
                        </span>
                    </x-mary-button>
                </div>
            </div>
        </div>

        {{-- Tickets Table --}}
        <div class="bg-base-100 rounded-box shadow-lg overflow-hidden">
            <!-- Skeleton Loading State -->
            <div wire:loading.delay wire:target="search,statusFilter,organizationFilter,dateFilter,clearFilters">
                <div class="overflow-x-auto">
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
                        <tbody class="animate-pulse">
                            @for ($i = 0; $i < 5; $i++)
                                <tr>
                                    <td>
                                        <div class="h-4 bg-gray-200 rounded w-20"></div>
                                    </td>
                                    <td>
                                        <div class="space-y-2">
                                            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                            <div class="h-3 bg-gray-200 rounded w-full"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-gray-200 rounded-full"></div>
                                            <div class="h-4 bg-gray-200 rounded w-32"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="h-6 bg-gray-200 rounded w-24"></div>
                                    </td>
                                    <td>
                                        <div class="space-y-1">
                                            <div class="h-4 bg-gray-200 rounded w-20"></div>
                                            <div class="h-3 bg-gray-200 rounded w-16"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Actual Content -->
            <div wire:loading.remove.delay wire:target="search,statusFilter,organizationFilter,dateFilter,clearFilters">
                <div class="overflow-x-auto">
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
                            @forelse($tickets as $ticket)
                                <tr class="hover:cursor-pointer hover:bg-base-200 transition-colors duration-200"
                                    wire:key="ticket-{{ $ticket->ticket_id }}"
                                    title="Ticket: {{ $ticket->title }} | Organization: {{ $ticket->user->studentOrganization->org_name ?? 'No Organization' }} | Status: {{ ucfirst(str_replace('_', ' ', $ticket->status)) }} | Click to view details"
                                    onclick="window.location='{{ route('osa.ticket-review.show', $ticket->ticket_number) }}'">
                                    <td>
                                        <span class="font-mono text-sm">#{{ $ticket->ticket_number }}</span>
                                    </td>
                                    <td>
                                        <div class="font-semibold">{{ $ticket->title }}</div>
                                        <div class="text-sm text-base-content/70">
                                            {{ Str::limit($ticket->description, 60) }}
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
                                                'gso_review' => 'badge-info',
                                                'for_rescheduling' => 'badge-warning',
                                                'rescheduled' => 'badge-success',
                                                'needs_revision' => 'badge-warning',
                                                'amended' => 'badge-info',
                                                'approved' => 'badge-success',
                                                'rejected' => 'badge-error',
                                            ];
                                        @endphp
                                        <x-mary-badge value="{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}"
                                            class="{{ $statusClasses[$ticket->status] ?? 'badge-neutral' }} text-white badge-md w-[10rem] justify-center truncate" />
                                    </td>
                                    <td>
                                        <div>{{ $ticket->created_at?->format('M d, Y') ?? 'N/A' }}</div>
                                        <div class="text-sm text-base-content/70">
                                            {{ $ticket->created_at?->format('h:i A') ?? '' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8">
                                        <div class="flex flex-col items-center gap-2">
                                            <x-mary-icon name="o-document-text"
                                                class="w-12 h-12 text-base-content/30" />
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
    </div>
</div>
