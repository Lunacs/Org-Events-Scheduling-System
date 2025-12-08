<div x-data="{
    closeDrawer() {
        $wire.closeDetailDrawer();
    }
}">
    <div class="p-6 space-y-6">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-heading">Ticket Management</h1>
                <p class="text-sm text-base-content/60 mt-1">View and manage all event tickets</p>
            </div>
            <div class="flex gap-2">
                @if (count($selectedTickets) > 0)
                    <x-mary-button icon="o-check-circle" class="btn-success btn-sm" wire:click="openBulkModal('approve')">
                        Approve ({{ count($selectedTickets) }})
                    </x-mary-button>
                    <x-mary-button icon="o-x-circle" class="btn-error btn-sm" wire:click="openBulkModal('reject')">
                        Reject ({{ count($selectedTickets) }})
                    </x-mary-button>
                @endif
                <x-mary-button icon="o-arrow-path" class="btn-outline btn-sm" wire:click="$refresh">
                    Refresh
                </x-mary-button>
            </div>
        </div>

        {{-- Filters --}}
        <x-mary-card>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <x-mary-input label="Search" wire:model.live.debounce.300ms="search" placeholder="Search tickets..."
                    icon="o-magnifying-glass" />

                <x-mary-select label="Status" wire:model.live="statusFilter" :options="[
                    ['id' => 'all', 'name' => 'All Status'],
                    ['id' => 'received', 'name' => 'Received'],
                    ['id' => 'gso_review', 'name' => 'GSO Review'],
                    ['id' => 'pending_osa_approval', 'name' => 'Pending OSA Approval'],
                    ['id' => 'for_revision', 'name' => 'For Revision'],
                    ['id' => 'approved', 'name' => 'Approved'],
                    ['id' => 'amended', 'name' => 'Amended'],
                ]" option-value="id"
                    option-label="name" />

                <x-mary-select label="Office" wire:model.live="officeFilter" :options="$offices" option-value="office_id"
                    option-label="office_name" placeholder="All Offices" />

                <x-mary-input label="From Date" wire:model.live="dateFrom" type="date" />

                <x-mary-input label="To Date" wire:model.live="dateTo" type="date" />
            </div>
        </x-mary-card>

        {{-- Tickets Table --}}
        <x-mary-card shadow>
            <x-mary-table :headers="$headers" :rows="$ticketsData" :sort-by="$sortBy" with-pagination
                wire:model="selectedTickets" selectable>

                @scope('cell_ticket_number', $ticket)
                    <span class="font-mono text-sm font-semibold">{{ $ticket->ticket_number }}</span>
                @endscope

                @scope('cell_title', $ticket)
                    <div class="font-medium">{{ $ticket->title }}</div>
                    <div class="text-xs text-base-content/60">{{ $ticket->eventType->type_name ?? 'N/A' }}</div>
                @endscope

                @scope('cell_organization', $ticket)
                    <span class="text-sm">{{ $ticket->user->studentOrganization->org_name ?? 'N/A' }}</span>
                @endscope

                @scope('cell_status', $ticket)
                    <x-mary-badge :value="ucfirst($ticket->status)" :class="match ($ticket->status) {
                        'received' => 'badge-info text-white',
                        'gso_review' => 'badge-info text-white',
                        'pending_osa_approval' => 'badge-error text-white',
                        'for_revision' => 'badge-warning text-white',
                        'approved' => 'badge-success text-white',
                        'amended' => 'badge-info text-white',
                        default => 'badge-ghost',
                    }" />
                @endscope

                @scope('cell_created_at', $ticket)
                    <div class="text-sm">
                        <div>{{ $ticket->created_at->format('M d, Y') }}</div>
                        <div class="text-gray-500">{{ $ticket->created_at->format('g:i A') }}</div>
                    </div>
                @endscope

                @scope('cell_actions', $ticket)
                    <div class="flex gap-2">
                        <x-mary-button size="xs" icon="o-eye" class="btn-ghost"
                            wire:click="viewTicketDetails({{ $ticket->ticket_id }})">
                            View
                        </x-mary-button>
                        <x-mary-button size="xs" icon="o-arrow-path-rounded-square" class="btn-ghost"
                            wire:click="openReassignModal({{ $ticket->ticket_id }})">
                            Reassign
                        </x-mary-button>
                    </div>
                @endscope
            </x-mary-table>
        </x-mary-card>
    </div>

    {{-- Detail Drawer --}}
    <div class="drawer drawer-end z-50">
        <input type="checkbox" class="drawer-toggle" x-bind:checked="$wire.showDetailDrawer"
            @change="if (!$event.target.checked) closeDrawer()" />

        <div class="drawer-side">
            <label class="drawer-overlay" @click="closeDrawer()"></label>

            <div class="bg-base-100 min-h-full w-11/12 lg:w-1/2 p-6 flex flex-col">
                @if ($selectedTicket)
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-base-300">
                        <div>
                            <h2 class="text-2xl font-bold text-base-content">Ticket Details</h2>
                            <p class="text-sm text-base-content/60 mt-1">{{ $selectedTicket->ticket_number }}</p>
                        </div>
                        <button @click="closeDrawer()" class="btn btn-sm btn-circle btn-ghost">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 overflow-y-auto space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">{{ $selectedTicket->title }}</h3>
                            <x-mary-badge :value="ucfirst($selectedTicket->status)" :class="match ($selectedTicket->status) {
                                'pending' => 'badge-warning',
                                'approved' => 'badge-success',
                                'for_revision' => 'badge-warning',
                                'cancelled' => 'badge-ghost',
                                default => 'badge-ghost',
                            }" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-base-content/60">Organization</p>
                                <p class="font-medium">
                                    {{ $selectedTicket->user->studentOrganization->org_name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Event Type</p>
                                <p class="font-medium">{{ $selectedTicket->eventType->type_name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Venue</p>
                                <p class="font-medium">{{ $selectedTicket->venue_requested ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Submitted</p>
                                <p class="font-medium">{{ $selectedTicket->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>

                        @if ($selectedTicket->description)
                            <div>
                                <p class="text-sm text-base-content/60 mb-2">Description</p>
                                <p class="text-sm">{{ $selectedTicket->description }}</p>
                            </div>
                        @endif

                        @if ($selectedTicket->event && $selectedTicket->event->eventSchedules->count() > 0)
                            <div>
                                <p class="text-sm text-base-content/60 mb-2">Event Schedules</p>
                                <div class="space-y-2">
                                    @foreach ($selectedTicket->event->eventSchedules as $schedule)
                                        <div class="bg-base-200 p-3 rounded-lg">
                                            <p class="font-medium">{{ $schedule->start_date->format('M d, Y') }}</p>
                                            <p class="text-sm text-base-content/70">
                                                {{ $schedule->start_time }} - {{ $schedule->end_time }}
                                            </p>
                                            <p class="text-sm">{{ $schedule->venue }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-2 pt-4 mt-4 border-t border-base-300">
                        <x-mary-button label="Close" @click="closeDrawer()" />
                        <x-mary-button label="Reassign" icon="o-arrow-path-rounded-square" class="btn-primary"
                            wire:click="openReassignModal({{ $selectedTicket->ticket_id }}); closeDrawer()" />
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Reassign Modal --}}
    <x-mary-modal wire:model="showReassignModal" title="Reassign Ticket">
        <div class="space-y-4">
            <p>Reassign this ticket to a different office:</p>
            <x-mary-select label="New Office" wire:model="newOfficeId" :options="$offices" option-value="office_id"
                option-label="office_name" placeholder="Select office..." required />
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.showReassignModal = false" />
            <x-mary-button label="Reassign" class="btn-primary" wire:click="reassignTicket"
                spinner="reassignTicket" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Bulk Action Modal --}}
    <x-mary-modal wire:model="showBulkModal" title="Bulk Action Confirmation">
        <div class="space-y-4">
            <p>You are about to <strong>{{ $bulkAction }}</strong> <strong>{{ count($selectedTickets) }}</strong>
                tickets.</p>
            <div class="alert alert-warning">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    class="stroke-current shrink-0 w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>This action will affect multiple tickets at once.</span>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.showBulkModal = false" />
            <x-mary-button label="Confirm" class="btn-primary" wire:click="executeBulkAction"
                spinner="executeBulkAction" />
        </x-slot:actions>
    </x-mary-modal>
</div>
