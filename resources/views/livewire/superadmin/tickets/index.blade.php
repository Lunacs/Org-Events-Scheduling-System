<div x-data="{
    closeDetailDrawer() {
        $wire.closeDetailDrawer();
    }
}">
    <div class="p-6 space-y-6">
        {{-- Header --}}
        <section
            class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
            <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-heading font-bold text-base-content">Ticket Management</h1>
                        <p class="text-sm text-base-content/70 mt-1">View and manage all event tickets</p>
                    </div>
                    <div class="flex items-center gap-2 relative z-10 w-full sm:w-auto">
                        @if (count($selectedTickets) > 0)
                            <x-ui.button icon="o-check-circle" class="btn-success btn-sm"
                                wire:click="openBulkModal('approve')">
                                Approve ({{ count($selectedTickets) }})
                            </x-ui.button>
                            <x-ui.button icon="o-x-circle" class="btn-error btn-sm"
                                wire:click="openBulkModal('reject')">
                                Reject ({{ count($selectedTickets) }})
                            </x-ui.button>
                        @endif
                        <a href="{{ route('superadmin.ticket.create') }}" wire:navigate
                            class="btn btn-accent btn-sm gap-2 w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Create Ticket
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Filters --}}
        <div class="bg-base-100 rounded-xl border border-base-200 p-4">
            <div class="flex flex-col sm:flex-row gap-4">
                {{-- Search --}}
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-base-content/70 mb-1">Search</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-base-content/40" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input id="search" type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Search by title, ticket #, or organization..."
                            class="input input-bordered w-full pl-10 input-sm" />
                    </div>
                </div>

                {{-- Status --}}
                <div class="w-full sm:w-48">
                    <label for="status" class="block text-sm font-medium text-base-content/70 mb-1">Status</label>
                    <select id="status" wire:model.live="statusFilter"
                        class="select select-bordered w-full select-sm">
                        <option value="all">All Status</option>
                        <option value="received">Received</option>
                        <option value="gso_review">GSO Review</option>
                        <option value="pending_osa_approval">Pending OSA Approval</option>
                        <option value="for_revision">For Revision</option>
                        <option value="approved">Approved</option>
                        <option value="amended">Amended</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Tickets Table --}}
        <x-ui.card shadow>
            {{-- Selectable, sortable table (inline DaisyUI: x-ui.table has no selection column). --}}
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox" class="checkbox checkbox-sm" aria-label="Select all tickets"
                                    @change="$wire.set('selectedTickets', $event.target.checked ? @js($ticketsData->pluck('ticket_id')->values()->all()) : [])"
                                    :checked="$wire.selectedTickets.length === {{ $ticketsData->count() }} &&
                                        {{ $ticketsData->count() }} > 0" />
                            </th>
                            @foreach ($headers as $header)
                                @php
                                    $isActive = ($sortBy['column'] ?? null) === ($header['key'] ?? null);
                                    $dir = $sortBy['direction'] ?? 'asc';
                                    $next = $isActive && $dir === 'asc' ? 'desc' : 'asc';
                                    $sortIcon = $isActive
                                        ? ($dir === 'asc'
                                            ? 'o-chevron-up'
                                            : 'o-chevron-down')
                                        : 'o-chevron-up-down';
                                @endphp
                                <th
                                    @if ($isActive) aria-sort="{{ $dir === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                    @if (($header['sortable'] ?? false) && !empty($header['key']))
                                        <button type="button"
                                            class="inline-flex items-center gap-1 font-semibold hover:text-primary transition-colors"
                                            wire:click="$set('sortBy', { column: '{{ $header['key'] }}', direction: '{{ $next }}' })">
                                            <span>{{ $header['label'] }}</span>
                                            <x-ui.icon :name="$sortIcon" class="w-4 h-4 opacity-70" />
                                        </button>
                                    @else
                                        {{ $header['label'] }}
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ticketsData as $ticket)
                            <tr wire:key="ticket-{{ $ticket->ticket_id }}">
                                <td>
                                    <input type="checkbox" class="checkbox checkbox-sm"
                                        value="{{ $ticket->ticket_id }}" wire:model.live="selectedTickets"
                                        aria-label="Select ticket {{ $ticket->ticket_number }}" />
                                </td>
                                <td>
                                    <span class="font-mono text-sm font-semibold">{{ $ticket->ticket_number }}</span>
                                </td>
                                <td>
                                    <div class="font-medium">{{ $ticket->title }}</div>
                                    <div class="text-xs text-base-content/60">
                                        {{ $ticket->eventType->type_name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    @php $orgDeleted = $ticket->user?->studentOrganization?->trashed(); @endphp
                                    <span class="text-sm {{ $orgDeleted ? 'italic text-base-content/50' : '' }}">
                                        {{ $orgDeleted ? 'Deleted Organization' : $ticket->user?->studentOrganization?->org_name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <x-ui.badge :value="ucfirst(str_replace('_', ' ', $ticket->status))" :class="match ($ticket->status) {
                                        'received' => 'badge-info text-white',
                                        'gso_review' => 'badge-info text-white',
                                        'pending_osa_approval' => 'badge-error text-white',
                                        'for_revision' => 'badge-warning text-white',
                                        'approved' => 'badge-success text-white',
                                        'amended' => 'badge-info text-white',
                                        'completed' => 'badge-success text-white',
                                        default => 'badge-ghost',
                                    }" />
                                </td>
                                <td>
                                    <div class="text-sm">
                                        <div>{{ $ticket->created_at->format('M d, Y') }}</div>
                                        <div class="text-gray-500">{{ $ticket->created_at->format('g:i A') }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex gap-1">
                                        <x-ui.button size="xs" icon="o-eye" class="btn-ghost"
                                            wire:click="viewTicketDetails({{ $ticket->ticket_id }})" tooltip="View" />
                                        <a href="{{ route('superadmin.ticket.edit', $ticket->ticket_id) }}"
                                            wire:navigate class="btn btn-ghost btn-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </a>
                                        <x-ui.button size="xs" icon="o-arrow-path-rounded-square" class="btn-ghost"
                                            wire:click="openReassignModal({{ $ticket->ticket_id }})"
                                            tooltip="Reassign" />
                                        <x-ui.button size="xs" icon="o-trash" class="btn-ghost text-red-600"
                                            wire:click="openDeleteModal({{ $ticket->ticket_id }}, '{{ addslashes($ticket->title) }}')"
                                            tooltip="Delete" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($headers) + 1 }}">
                                    <div class="flex flex-col items-center justify-center py-12 text-center">
                                        <x-ui.icon name="o-ticket" class="w-16 h-16 text-base-content/20 mb-4" />
                                        <h3 class="text-xl font-bold text-base-content/70">No tickets found</h3>
                                        <p class="text-base-content/50 max-w-sm mx-auto mt-2">
                                            No tickets match your current filters. Try adjusting your search criteria.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($ticketsData->hasPages())
                <x-tickets.ticket-pagination :items="$ticketsData" label="tickets" />
            @endif
        </x-ui.card>
    </div>

    {{-- ── Detail Drawer ──────────────────────────────────────────── --}}
    <div class="drawer drawer-end z-50">
        <input type="checkbox" class="drawer-toggle" x-bind:checked="$wire.showDetailDrawer"
            @change="if (!$event.target.checked) closeDetailDrawer()" />

        <div class="drawer-side">
            <label class="drawer-overlay" @click="closeDetailDrawer()"></label>

            <div class="bg-base-100 min-h-full w-11/12 lg:w-1/2 p-6 flex flex-col">
                @if ($selectedTicket)
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-base-300">
                        <div>
                            <h2 class="text-2xl font-bold text-base-content">Ticket Details</h2>
                            <p class="text-sm text-base-content/60 mt-1">{{ $selectedTicket->ticket_number }}</p>
                        </div>
                        <button @click="closeDetailDrawer()" class="btn btn-sm btn-circle btn-ghost">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 overflow-y-auto space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">{{ $selectedTicket->title }}</h3>
                            <x-ui.badge :value="ucfirst(str_replace('_', ' ', $selectedTicket->status))" :class="match ($selectedTicket->status) {
                                'received' => 'badge-info text-white',
                                'gso_review' => 'badge-info text-white',
                                'pending_osa_approval' => 'badge-error text-white',
                                'for_revision' => 'badge-warning text-white',
                                'approved' => 'badge-success text-white',
                                'amended' => 'badge-info text-white',
                                'completed' => 'badge-success text-white',
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
                                <p class="text-sm text-base-content/60">Preferred Venue</p>
                                <p class="font-medium">
                                    {{ $selectedTicket->venue?->venue_name ?? ($selectedTicket->venue_other ?? 'N/A') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Alternate Venue</p>
                                <p class="font-medium">
                                    {{ $selectedTicket->alternateVenue?->venue_name ?? ($selectedTicket->alternate_venue_other ?? 'N/A') }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-base-content/60">Start Date</p>
                                <p class="font-medium">
                                    {{ $selectedTicket->date_from ? \Carbon\Carbon::parse($selectedTicket->date_from)->format('M d, Y') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">End Date</p>
                                <p class="font-medium">
                                    {{ $selectedTicket->date_to ? \Carbon\Carbon::parse($selectedTicket->date_to)->format('M d, Y') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Start Time</p>
                                <p class="font-medium">{{ $selectedTicket->time_from ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">End Time</p>
                                <p class="font-medium">{{ $selectedTicket->time_to ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-base-content/60">PLV Participants</p>
                                <p class="font-medium">{{ $selectedTicket->plv_participants ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">External Participants</p>
                                <p class="font-medium">{{ $selectedTicket->external_participants ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Total</p>
                                <p class="font-medium">{{ $selectedTicket->total_participants ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-base-content/60">Estimated Budget</p>
                                <p class="font-medium">
                                    {{ $selectedTicket->estimated_budget ? 'PHP ' . number_format($selectedTicket->estimated_budget, 2) : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-base-content/60">Fund Source</p>
                                <p class="font-medium">{{ $selectedTicket->fundSource?->source_name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        @if ($selectedTicket->budget_breakdown)
                            <div>
                                <p class="text-sm text-base-content/60 mb-1">
                                    {{ $selectedTicket->budget_breakdown_label }}</p>
                                <p class="text-sm bg-base-200 p-3 rounded-lg">{{ $selectedTicket->budget_breakdown }}
                                </p>
                            </div>
                        @endif

                        @if ($selectedTicket->description)
                            <div>
                                <p class="text-sm text-base-content/60 mb-1">Description</p>
                                <p class="text-sm bg-base-200 p-3 rounded-lg">{{ $selectedTicket->description }}</p>
                            </div>
                        @endif

                        @if ($selectedTicket->special_requirements)
                            <div>
                                <p class="text-sm text-base-content/60 mb-1">Special Requirements</p>
                                <p class="text-sm bg-base-200 p-3 rounded-lg">
                                    {{ $selectedTicket->special_requirements }}</p>
                            </div>
                        @endif

                        @if ($selectedTicket->additional_notes)
                            <div>
                                <p class="text-sm text-base-content/60 mb-1">Additional Notes</p>
                                <p class="text-sm bg-base-200 p-3 rounded-lg">{{ $selectedTicket->additional_notes }}
                                </p>
                            </div>
                        @endif

                        <div>
                            <p class="text-sm text-base-content/60">Submitted</p>
                            <p class="font-medium">{{ $selectedTicket->created_at->format('M d, Y g:i A') }}</p>
                        </div>

                        @if ($selectedTicket->attachments && $selectedTicket->attachments->count() > 0)
                            <div>
                                <p class="text-sm text-base-content/60 mb-2">Attachments
                                    ({{ $selectedTicket->attachments->count() }})</p>
                                <div class="space-y-1">
                                    @foreach ($selectedTicket->attachments as $attachment)
                                        <div class="flex items-center gap-2 bg-base-200 p-2 rounded-lg text-sm">
                                            <x-ui.icon name="o-paper-clip" class="w-4 h-4 text-base-content/60" />
                                            <span>{{ $attachment->file_name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($selectedTicket->events && $selectedTicket->events->flatMap(fn($e) => $e->eventSchedules ?? collect())->count() > 0)
                            <div>
                                <p class="text-sm text-base-content/60 mb-2">Event Schedules</p>
                                <div class="space-y-2">
                                    @foreach ($selectedTicket->events as $event)
                                        @foreach ($event->eventSchedules ?? [] as $schedule)
                                            <div class="bg-base-200 p-3 rounded-lg">
                                                <p class="font-medium">
                                                    {{ $schedule->start_date ? \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') : 'N/A' }}
                                                </p>
                                                <p class="text-sm text-base-content/70">
                                                    {{ $schedule->start_time ?? '' }} -
                                                    {{ $schedule->end_time ?? '' }}
                                                </p>
                                                @if ($schedule->venue)
                                                    <p class="text-sm">{{ $schedule->venue }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-2 pt-4 mt-4 border-t border-base-300">
                        <x-ui.button label="Close" @click="closeDetailDrawer()" />
                        <a href="{{ route('superadmin.ticket.edit', $selectedTicket->ticket_id) }}" wire:navigate
                            class="btn btn-accent btn-sm gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Edit
                        </a>
                        <x-ui.button label="Reassign" icon="o-arrow-path-rounded-square" class="btn-primary btn-sm"
                            wire:click="openReassignModal({{ $selectedTicket->ticket_id }})"
                            @click="closeDetailDrawer()" />
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Reassign Modal ─────────────────────────────────────────── --}}
    <x-ui.modal-dialog wire:model="showReassignModal" title="Reassign Ticket">
        <div class="space-y-4">
            <p>Reassign this ticket to a different office:</p>
            <x-ui.select label="New Office" wire:model="newOfficeId" :options="$offices" option-value="office_id"
                option-label="office_name" placeholder="Select office..." required />
        </div>

        <x-slot:actions>
            <x-ui.button label="Cancel" @click="$wire.showReassignModal = false" />
            <x-ui.button label="Reassign" class="btn-primary" wire:click="reassignTicket"
                spinner="reassignTicket" />
        </x-slot:actions>
    </x-ui.modal-dialog>

    {{-- ── Bulk Action Modal ──────────────────────────────────────── --}}
    <x-ui.modal-dialog wire:model="showBulkModal" title="Bulk Action Confirmation">
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
            <x-ui.button label="Cancel" @click="$wire.showBulkModal = false" />
            <x-ui.button label="Confirm" class="btn-primary" wire:click="executeBulkAction"
                spinner="executeBulkAction" />
        </x-slot:actions>
    </x-ui.modal-dialog>

    {{-- ── Delete Confirmation Modal ──────────────────────────────── --}}
    @if ($deletingTicketTitle)
        <x-ui.modal-dialog wire:model="showDeleteModal" title="Delete Ticket Confirmation"
            subtitle="This action will soft-delete the ticket">
            <div class="space-y-4">
                <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                        <p class="text-red-800 dark:text-red-300 font-medium">Warning: This will remove the ticket from
                            active view</p>
                    </div>
                </div>

                <p class="text-base-content/80">
                    Are you sure you want to delete the ticket
                    <strong class="text-base-content">{{ $deletingTicketTitle }}</strong>?
                </p>

                <ul class="list-disc list-inside text-sm text-base-content/60 ml-4">
                    <li>The ticket will be soft-deleted (recoverable via Archive)</li>
                    <li>It will no longer appear in the active ticket list</li>
                    <li>Associated events and approvals remain intact</li>
                </ul>
            </div>

            <x-slot:actions>
                <x-ui.button label="Cancel" wire:click="closeDeleteModal()" />
                <x-ui.button label="Delete Ticket" wire:click="confirmDelete" class="btn-error"
                    spinner="confirmDelete" />
            </x-slot:actions>
        </x-ui.modal-dialog>
    @endif
</div>
