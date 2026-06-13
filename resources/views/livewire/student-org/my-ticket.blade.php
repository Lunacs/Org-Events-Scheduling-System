<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            {{ __('My Tickets') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Header --}}
            <section
                class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
                <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
                <div class="relative p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-heading font-bold text-base-content">All Event Requests</h1>
                            <p class="text-base-content/70 mt-1">Track the progress of your organization's tickets</p>
                        </div>
                        <x-mary-button label="Submit New Ticket" icon="s-document-plus"
                            class="btn-primary w-full sm:w-auto" link="/student-org/submit-ticket" wire:navigate />
                    </div>
                </div>
            </section>

            {{-- Filter and Search --}}
            <x-mary-card>
                <div class="relative grid grid-cols-1 gap-4 sm:gap-5 md:grid-cols-12 md:items-end">

                    <div class="min-w-0 md:col-span-12 lg:col-span-4">
                        <x-mary-input label="Search Tickets" wire:model.live.debounce.300ms="search"
                            wire:loading.class="opacity-70" wire:target="search"
                            placeholder="Search by title, ID, or description..." icon="s-magnifying-glass"
                            class="w-full" />
                    </div>

                    <div class="min-w-0 md:col-span-6 lg:col-span-3">
                        <x-mary-select label="Status" wire:model.live="statusFilter" :options="[
                            ['id' => '', 'name' => 'All Status'],
                            ['id' => 'under_review', 'name' => 'Under Review'],
                            ['id' => 'approved', 'name' => 'Approved'],
                            ['id' => 'for_revision', 'name' => 'For Revision'],
                        ]"
                            wire:loading.class="opacity-70" wire:target="statusFilter" class="w-full" />
                    </div>

                    <div class="min-w-0 md:col-span-6 lg:col-span-3">
                        <x-mary-select label="Date range" wire:model.live="dateFilter" :options="[
                            ['id' => '', 'name' => 'All Time'],
                            ['id' => 'last_week', 'name' => 'Last Week'],
                            ['id' => 'last_month', 'name' => 'Last Month'],
                            ['id' => 'last_3_months', 'name' => 'Last 3 Months'],
                            ['id' => 'this_year', 'name' => 'This Year'],
                        ]"
                            wire:loading.class="opacity-70" wire:target="dateFilter" class="w-full" />
                    </div>

                    @if ($search || $statusFilter || $dateFilter)
                        <div class="min-w-0 md:col-span-12 lg:col-span-2 flex items-end">
                            <x-mary-button label="Clear Filters" icon="s-funnel"
                                class="btn-ghost w-full lg:w-auto whitespace-nowrap shrink-0 gap-2"
                                wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters"
                                tooltip="Clear filters" />
                        </div>
                    @endif
                </div>
            </x-mary-card>

            {{-- Tickets List --}}
            <x-mary-card>
                {{-- Skeleton Loading State --}}
                <div wire:loading.delay wire:target="search,statusFilter,dateFilter,clearFilters"
                    class="space-y-4 w-full">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="rounded-xl border border-base-300 bg-base-100 p-5 sm:p-6 animate-pulse">
                            {{-- Title + badge row --}}
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 space-y-2 mr-4">
                                    <div class="h-5 bg-base-200 rounded w-2/3"></div>
                                    <div class="h-4 bg-base-200 rounded w-1/3"></div>
                                </div>
                                <div class="flex flex-col gap-2 items-end">
                                    <div class="h-8 bg-base-200 rounded w-20"></div>
                                    <div class="h-8 bg-base-200 rounded w-24"></div>
                                </div>
                            </div>
                            {{-- Description --}}
                            <div class="space-y-2 mb-4">
                                <div class="h-3 bg-base-200 rounded w-full"></div>
                                <div class="h-3 bg-base-200 rounded w-5/6"></div>
                                <div class="h-3 bg-base-200 rounded w-2/3"></div>
                            </div>
                            {{-- 3-column details --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 bg-base-200 rounded shrink-0"></div>
                                    <div class="h-4 bg-base-200 rounded w-28"></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 bg-base-200 rounded shrink-0"></div>
                                    <div class="h-4 bg-base-200 rounded w-32"></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 bg-base-200 rounded shrink-0"></div>
                                    <div class="h-4 bg-base-200 rounded w-24"></div>
                                </div>
                            </div>
                            {{-- Progress strip --}}
                            <div class="hidden md:flex items-center gap-2 mb-4">
                                <div class="h-3 bg-base-200 rounded w-full"></div>
                            </div>
                            {{-- Footer timestamp --}}
                            <div class="mt-4 pt-4 border-t border-base-300/70">
                                <div class="h-3 bg-base-200 rounded w-48"></div>
                            </div>
                        </div>
                    @endfor
                </div>

                {{-- Actual Content --}}
                <div wire:loading.remove.delay wire:target="search,statusFilter,dateFilter,clearFilters"
                    class="space-y-4">
                    @forelse ($tickets as $ticket)
                        <x-tickets.ticketinfo :tickets="$ticket" />
                    @empty
                        <x-ui.empty-state title="No tickets found"
                            description="Try adjusting your search and filters or submit a new ticket to get started."
                            icon="o-ticket" tone="primary" iconColor="text-primary" actionLabel="Submit New Ticket"
                            actionLink="/student-org/submit-ticket" />
                    @endforelse

                    {{-- Pagination --}}
                    @if ($tickets->hasPages())
                        <x-tickets.ticket-pagination :tickets="$tickets" />
                    @endif
                </div>
            </x-mary-card>

            {{-- Quick Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-mary-stat title="Total Submitted" value="{{ $ticketStats['total'] }}" icon="s-document-text"
                    color="text-primary" />

                <x-mary-stat title="Under Review" value="{{ $ticketStats['under_review'] }}"
                    icon="s-clock" color="text-warning" />

                <x-mary-stat title="Approved" value="{{ $ticketStats['approved'] }}"
                    icon="s-check-circle" color="text-success" />

                <x-mary-stat title="Need Action" value="{{ $ticketStats['need_action'] }}"
                    icon="s-exclamation-triangle" color="text-error" />
            </div>
        </div>
    </div>

    {{-- Edit Drawer - Keep for editing tickets from list view if needed --}}

    <x-mary-drawer wire:model="showEditDrawer"
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
                    <x-mary-loading class="loading-lg text-primary" />
                    <p class="text-sm text-base-content/70">Loading form...</p>
                </div>
            </div>

            <div x-show="!isLoading" x-cloak x-transition>
                @if ($showEditDrawer && $selectedTicketId)
                    <livewire:student-org.edit-ticket :ticketId="$selectedTicketId" :key="'edit-ticket-' . $selectedTicketId" />
                @endif
            </div>
        </div>
    </x-mary-drawer>


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
