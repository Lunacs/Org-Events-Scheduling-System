<div>
    @php
        $statusTabs = [
            ['value' => '', 'label' => 'All', 'count' => $ticketStats['total']],
            ['value' => 'under_review', 'label' => 'In review', 'count' => $ticketStats['under_review']],
            ['value' => 'for_revision', 'label' => 'Needs revision', 'count' => $ticketStats['need_action']],
            ['value' => 'approved', 'label' => 'Approved', 'count' => $ticketStats['approved']],
        ];
        $filtering = 'search,statusFilter,dateFilter,clearFilters';
    @endphp

    <div class="space-y-6">
        <x-ui.page-heading title="My tickets"
            subtitle="Every event request from your organization, where it stands, and what to do next.">
            @can('tickets.create')
                <x-slot:actions>
                    <x-ui.button label="Submit a ticket" icon="o-plus" class="btn-primary"
                        link="{{ route('student-org.submit-ticket') }}" />
                </x-slot:actions>
            @endcan
        </x-ui.page-heading>

        {{-- Status filter: counts double as the overview (no separate stat cards) --}}
        <div class="space-y-4">
            <div class="flex flex-wrap gap-2" role="group" aria-label="Filter by status">
                @foreach ($statusTabs as $tab)
                    <button type="button" wire:click="$set('statusFilter', '{{ $tab['value'] }}')"
                        aria-pressed="{{ $statusFilter === $tab['value'] ? 'true' : 'false' }}"
                        @class([
                            'btn btn-sm gap-2 font-medium',
                            'btn-primary' => $statusFilter === $tab['value'],
                            'border-base-300 bg-base-100 hover:bg-base-200' => $statusFilter !== $tab['value'],
                        ])>
                        {{ $tab['label'] }}
                        <span @class([
                            'rounded-full px-1.5 text-xs tabular-nums',
                            'bg-primary-content/20' => $statusFilter === $tab['value'],
                            'bg-base-200 text-base-content/70' => $statusFilter !== $tab['value'],
                        ])>{{ $tab['count'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_auto] sm:items-end">
                <x-ui.input label="Search" wire:model.live.debounce.300ms="search" type="search" autocomplete="off"
                    placeholder="Event title, ticket number, or description…" icon="o-magnifying-glass" />

                <x-ui.select label="Submitted" wire:model.live="dateFilter" :options="[
                    ['id' => '', 'name' => 'Any time'],
                    ['id' => 'last_week', 'name' => 'Last 7 days'],
                    ['id' => 'last_month', 'name' => 'Last 30 days'],
                    ['id' => 'last_3_months', 'name' => 'Last 3 months'],
                    ['id' => 'this_year', 'name' => 'This year'],
                ]" />

                @if ($search || $statusFilter || $dateFilter)
                    <x-ui.button label="Clear filters" icon="o-x-mark" class="btn-ghost" wire:click="clearFilters"
                        wire:loading.attr="disabled" wire:target="clearFilters" />
                @endif
            </div>
        </div>

        {{-- Tickets --}}
        <section aria-label="Tickets" class="space-y-4" wire:loading.class="opacity-60"
            wire:target="{{ $filtering }}">
            @forelse ($tickets as $ticket)
                <x-tickets.ticketinfo :tickets="$ticket" wire:key="my-ticket-{{ $ticket->ticket_id }}" />
            @empty
                <div class="rounded-box border border-base-300 bg-base-100">
                    @if ($search || $statusFilter || $dateFilter)
                        <x-ui.empty-state icon="o-funnel" title="No tickets match these filters"
                            description="Try another status or search term, or clear the filters to see every request." />
                    @else
                        <x-ui.empty-state icon="o-document-text" title="No tickets yet"
                            description="Event requests you submit appear here with their status and next step."
                            :actionLabel="auth()->user()?->can('tickets.create') ? 'Submit a ticket' : null"
                            :actionLink="auth()->user()?->can('tickets.create') ? route('student-org.submit-ticket') : null"
                            primary />
                    @endif
                </div>
            @endforelse
        </section>

        @if ($tickets->hasPages())
            <x-tickets.ticket-pagination :tickets="$tickets" />
        @endif
    </div>

    {{-- Edit Drawer - Keep for editing tickets from list view if needed --}}

    <x-ui.drawer wire:model="showEditDrawer"
        title="{{ $this->selectedTicket ? 'Edit Ticket - ' . $this->selectedTicket->ticket_number : 'Edit Ticket' }}"
        subtitle="Revise your event request" separator with-close-button close-on-escape right
        class="w-11/12 lg:w-2/3 overflow-hidden" @close="$wire.closeEditDrawer()">

        <div x-data="{ isLoading: true }" x-init="$watch('$wire.showEditDrawer', value => {
            if (value) {
                isLoading = true;
                setTimeout(() => {
                    const checkInterval = setInterval(() => {
                        const form = document.querySelector('[wire\\:submit=\'updateTicket\']');
                        if (form) {
                            clearInterval(checkInterval);
                            isLoading = false;
                        }
                    }, 50);
        
                    setTimeout(() => {
                        clearInterval(checkInterval);
                        isLoading = false;
                    }, 2000);
                }, 100);
            } else {
                isLoading = true;
            }
        });">
            <div x-show="isLoading" class="flex items-center justify-center py-16">
                <div class="flex flex-col items-center gap-3">
                    <x-ui.loading class="loading-lg text-primary" />
                    <p class="text-sm text-base-content/70">Loading form…</p>
                </div>
            </div>

            <div x-show="!isLoading" x-cloak x-transition>
                @if ($showEditDrawer && $selectedTicketId)
                    <livewire:student-org.edit-ticket :ticketId="$selectedTicketId" :key="'edit-ticket-' . $selectedTicketId" />
                @endif
            </div>
        </div>
    </x-ui.drawer>


    {{-- Add JavaScript for handling attachment preview and download --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-attachment-preview', ({
                url
            }) => {
                if (url) {
                    window.open(url, '_blank');
                }
            });

            Livewire.on('download-attachment', ({
                url,
                filename
            }) => {
                if (url) {
                    // Create a temporary anchor element to trigger download
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = filename || 'download';
                    link.target = '_blank';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });
        });
    </script>
    @script
        <script>
            // Handle modal opened event
            Livewire.on('modal-opened', ({
                ticketId
            }) => {
                // Wait for modal animation to complete
                setTimeout(() => {
                    // Load ticket data
                    $wire.call('loadTicketData', ticketId).then(() => {
                        // Wait for Livewire to render
                        setTimeout(() => {
                            // Check if ticket preview exists
                            const checkInterval = setInterval(() => {
                                const preview = document.querySelector(
                                    '[data-ticket-loaded]');
                                if (preview) {
                                    clearInterval(checkInterval);
                                    // Find the modal's Alpine component
                                    const modalContent = preview.closest('[x-data]');
                                    if (modalContent) {
                                        Alpine.evaluate(modalContent,
                                            'isLoading = false');
                                    }
                                }
                            }, 50);

                            // Fallback - force hide loading after 2 seconds
                            setTimeout(() => {
                                clearInterval(checkInterval);
                                const modal = document.querySelector(
                                    '[x-data*="isLoading"]');
                                if (modal) {
                                    Alpine.evaluate(modal, 'isLoading = false');
                                }
                            }, 2000);
                        }, 100);
                    });
                }, 100);
            });

            // Reset modal state when navigating away
            document.addEventListener('livewire:navigating', () => {
                if ($wire.showDetailsModal) {
                    $wire.closeDetailsModal();
                }
            });
        </script>
    @endscript
</div>
