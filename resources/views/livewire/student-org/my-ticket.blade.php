<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Tickets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Header --}}
            <div class="mb-8">
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-heading font-bold text-base-content">All Event Requests</h1>
                            <p class="text-base-content/70 mt-1">Track the progress of your organization's tickets</p>
                        </div>
                        <div class="hidden md:block">
                            <x-mary-button label="Submit New Ticket" icon="s-document-plus" class="btn-primary"
                                           link="/student-org/submit-ticket" wire:navigate/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-center items-center">
                <x-mary-button label="Submit New Ticket" icon="s-document-plus" class="btn-primary md:hidden"
                               link="/student-org/submit-ticket" wire:navigate/>
            </div>

            {{-- Filter and Search --}}
            <x-mary-card>
                <div class="grid grid-cols-1 md:flex md:flex-wrap gap-4 md:items-end md:justify-start">
                    <x-mary-input label="Search Tickets" wire:model.live="search"
                                  x-data="{ placeholder: window.innerWidth < 768 ? 'Title, ID, or Description' : 'Search by title, ID, or description...' }"
                                  x-init="window.addEventListener('resize', () => { placeholder = window.innerWidth < 768 ? 'Search...' : 'Search by title, ID, or description...' })"
                                  ::placeholder="placeholder"
                                  icon="s-magnifying-glass"
                                  class="flex-1 md:min-w-64 min-w-32"/>


                    <x-mary-select label="Status Filter" wire:model.live="statusFilter" :options="[
                        ['id' => '', 'name' => 'All Status'],
                        ['id' => 'under_review', 'name' => 'Under Review'],
                        ['id' => 'approved', 'name' => 'Approved'],
                        ['id' => 'for_revision', 'name' => 'For Revision'],
                        ['id' => 'rescheduled', 'name' => 'Rescheduled'],
                    ]"/>

                    <x-mary-select label="Date Range" wire:model.live="dateFilter" :options="[
                        ['id' => '', 'name' => 'All Time'],
                        ['id' => 'last_week', 'name' => 'Last Week'],
                        ['id' => 'last_month', 'name' => 'Last Month'],
                        ['id' => 'last_3_months', 'name' => 'Last 3 Months'],
                        ['id' => 'this_year', 'name' => 'This Year'],
                    ]"/>

                    <x-mary-button icon="s-funnel" class="btn-ghost hidden md:block" wire:click="clearFilters"/>
                    <x-mary-button label="Clear Filters" class="mt-6 md:hidden" wire:click="clearFilters"/>
                </div>
            </x-mary-card>

            {{-- Tickets List --}}
            <x-mary-card>
                <div class="space-y-4">
                    {{-- Ticket Item 1 --}}
                    @forelse ($tickets as $ticket)
                        <x-tickets.ticketinfo :tickets="$ticket"/>
                    @empty
                        <div class="flex flex-col items-center gap-2">
                            <span class="text-base-content/70">No tickets found</span>
                            <span class="text-sm text-base-content/50">Try adjusting your filters</span>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if ($tickets->hasPages())
                    <x-tickets.ticket-pagination :tickets="$tickets"/>
                @endif
            </x-mary-card>

            {{-- Quick Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-mary-stat title="Total Submitted" value="{{ $allTickets->count() }}" icon="s-document-text"
                             color="text-primary"/>

                <x-mary-stat title="Under Review"
                             value="{{ $allTickets->whereNotIn('status', ['approved', 'for_revision'])->count() }}"
                             icon="s-clock" color="text-warning"/>

                <x-mary-stat title="Approved" value="{{ $allTickets->where('status', 'approved')->count() }}"
                             icon="s-check-circle" color="text-success"/>

                <x-mary-stat title="Need Action"
                             value="{{ $allTickets->whereIn('status', ['for_revision'])->count() }}"
                             icon="s-exclamation-triangle" color="text-error"/>
            </div>
        </div>
    </div>

    {{-- Details modal --}}
    <x-mary-modal wire:model="showDetailsModal" title="Ticket Details" class="backdrop-blur"
                  box-class="max-w-5xl max-h-[85vh] overflow-y-auto" @close="$wire.closeDetailsModal()">

        <div x-data="{ isLoading: true }"
             x-init="
            $watch('$wire.showDetailsModal', value => {
                if (value) {
                    // Reset loading state when modal opens
                    isLoading = true;
                } else {
                    // Reset when modal closes
                    isLoading = true;
                }
            });
         ">
            <div x-show="isLoading" class="flex items-center justify-center py-16">
                <div class="flex flex-col items-center gap-3">
                    <x-mary-loading class="loading-lg text-primary"/>
                    <p class="text-sm text-base-content/70">Loading ticket details...</p>
                </div>
            </div>

            <div x-show="!isLoading" x-cloak x-transition>
                @if ($this->selectedTicket)
                    <x-tickets.ticket-preview :ticket="$this->selectedTicket"/>
                @endif
            </div>
        </div>
    </x-mary-modal>

    {{-- Comments modal --}}
    <x-mary-modal wire:model="showCommentsModal" title="Ticket Comments" class="backdrop-blur"
                  box-class="max-w-5xl max-h-[85vh] overflow-y-auto" @close="$wire.closeCommentsModal()">

        @if ($this->selectedTicket)
            @if (in_array(strtolower($this->selectedTicket->status), ['approved', 'for_rescheduling', 'needs_revision', 'for_revision']))
                <x-tickets.latest-remark :status="$this->selectedTicket->status" :ticket="$this->selectedTicket"/>
            @endif

            <div class="mt-4">
                <livewire:components.ticket-comments :ticket="$this->selectedTicket"
                                                     :key="'ticket-comments-' . $this->selectedTicket->ticket_id"/>
            </div>
        @else
            <div class="text-center py-8">
                <x-mary-loading class="loading-lg"/>
            </div>
        @endif
    </x-mary-modal>

    <x-mary-drawer wire:model="showEditDrawer"
                   title="{{ $this->selectedTicket ? 'Edit Ticket - ' . $this->selectedTicket->ticket_number : 'Edit Ticket' }}"
                   subtitle="Revise your event request"
                   separator
                   with-close-button
                   close-on-escape
                   right
                   class="w-11/12 lg:w-2/3 overflow-hidden"
                   @close="$wire.closeEditDrawer()">

        <div x-data="{ isLoading: true }"
             x-init="
        $watch('$wire.showEditDrawer', value => {
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
        });
     ">
            <div x-show="isLoading" class="flex items-center justify-center py-16">
                <div class="flex flex-col items-center gap-3">
                    <x-mary-loading class="loading-lg text-primary"/>
                    <p class="text-sm text-base-content/70">Loading form...</p>
                </div>
            </div>

            <div x-show="!isLoading" x-cloak x-transition>
                @if ($showEditDrawer && $selectedTicketId)
                    @livewire('student-org.edit-ticket', ['ticketId' => $selectedTicketId], key('edit-ticket-' . $selectedTicketId))
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
        Livewire.on('modal-opened', ({ticketId}) => {
            // Wait for modal animation to complete
            setTimeout(() => {
                // Load ticket data
                $wire.call('loadTicketData', ticketId).then(() => {
                    // Wait for Livewire to render
                    setTimeout(() => {
                        // Check if ticket preview exists
                        const checkInterval = setInterval(() => {
                            const preview = document.querySelector('[data-ticket-loaded]');
                            if (preview) {
                                clearInterval(checkInterval);
                                // Find the modal's Alpine component
                                const modalContent = preview.closest('[x-data]');
                                if (modalContent) {
                                    Alpine.evaluate(modalContent, 'isLoading = false');
                                }
                            }
                        }, 50);

                        // Fallback - force hide loading after 2 seconds
                        setTimeout(() => {
                            clearInterval(checkInterval);
                            const modal = document.querySelector('[x-data*="isLoading"]');
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
