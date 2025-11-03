<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Tickets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Header Actions --}}
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">All Event Requests</h3>
                    <p class="text-sm text-gray-600">Track the progress of your submitted tickets</p>
                </div>
                <x-mary-button label="Submit New Ticket" icon="s-document-plus" class="btn-primary"
                    link="/student-org/submit-ticket" wire:navigate />
            </div>

            {{-- Filter and Search --}}
            <x-mary-card>
                <div class="flex flex-wrap gap-4 items-end">
                    <x-mary-input label="Search Tickets" wire:model.live="search"
                        placeholder="Search by title, ID, or description..." icon="s-magnifying-glass"
                        class="flex-1 min-w-64" />

                    <x-mary-select label="Status Filter" wire:model.live="statusFilter" :options="[
                        ['id' => '', 'name' => 'All Status'],
                        ['id' => 'draft', 'name' => 'Draft'],
                        ['id' => 'under_review', 'name' => 'Under Review'],
                        ['id' => 'approved', 'name' => 'Approved'],
                        ['id' => 'rejected', 'name' => 'Rejected'],
                        ['id' => 'needs_revision', 'name' => 'Requires Revision'],
                    ]"
                        placeholder="Filter by status" />

                    <x-mary-select label="Date Range" wire:model.live="dateFilter" :options="[
                        ['id' => '', 'name' => 'All Time'],
                        ['id' => 'last_week', 'name' => 'Last Week'],
                        ['id' => 'last_month', 'name' => 'Last Month'],
                        ['id' => 'last_3_months', 'name' => 'Last 3 Months'],
                        ['id' => 'this_year', 'name' => 'This Year'],
                    ]"
                        placeholder="Filter by date" />

                    <x-mary-button icon="s-funnel" class="btn-ghost" wire:click="clearFilters" />
                </div>
            </x-mary-card>

            {{-- Tickets List --}}
            <x-mary-card>
                <div class="space-y-4">
                    {{-- Ticket Item 1 --}}
                    @foreach ($tickets as $ticket)
                        <x-tickets.ticketinfo :tickets="$ticket" />
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6 flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        Showing {{ $tickets->firstItem() ?? 0 }} to {{ $tickets->lastItem() ?? 0 }}
                        of {{ $tickets->total() }} tickets
                    </div>

                    <div class="flex space-x-2">
                        <x-mary-button icon="s-chevron-left" class="btn-sm btn-ghost" wire:click="previousPage"
                            :disabled="!$tickets->previousPageUrl()" />

                        @foreach ($tickets->getUrlRange(1, $tickets->lastPage()) as $page => $url)
                            <x-mary-button :label="$page"
                                class="btn-sm {{ $page == $tickets->currentPage() ? 'btn-primary' : 'btn-ghost' }}"
                                wire:click="gotoPage({{ $page }})" />
                        @endforeach

                        <x-mary-button icon="s-chevron-right" class="btn-sm btn-ghost" wire:click="nextPage"
                            :disabled="!$tickets->nextPageUrl()" />
                    </div>
                </div>
            </x-mary-card>

            {{-- Quick Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-mary-stat title="Total Submitted" value="{{ $allTickets->count() }}" icon="s-document-text"
                    color="text-primary" />

                <x-mary-stat title="Under Review"
                    value="{{ $allTickets->whereNotIn('status', ['approved', 'rejected', 'needs_revision', 'for_rescheduling'])->count() }}"
                    icon="s-clock" color="text-warning" />

                <x-mary-stat title="Approved" value="{{ $allTickets->where('status', 'approved')->count() }}"
                    icon="s-check-circle" color="text-success" />

                <x-mary-stat title="Need Action"
                    value="{{ $allTickets->whereIn('status', ['needs_revision', 'for_rescheduling'])->count() }}"
                    icon="s-exclamation-triangle" color="text-error" />
            </div>
        </div>
    </div>

    <x-mary-modal wire:model="showDetailsModal" title="Ticket Details" class="backdrop-blur"
        box-class="max-w-5xl max-h-[85vh] overflow-y-auto" @close="$wire.closeDetailsModal()">

        @if ($this->selectedTicket)
            <x-tickets.ticket-preview :ticket="$this->selectedTicket" />
        @else
            <div class="text-center py-8">
                <x-mary-loading class="loading-lg" />
            </div>
        @endif
    </x-mary-modal>

    <x-mary-modal wire:model="showCommentsModal" title="Ticket Comments" class="backdrop-blur"
        box-class="max-w-5xl max-h-[85vh] overflow-y-auto" @close="$wire.closeCommentsModal()">

        @if ($this->selectedTicket)
            @if (in_array(strtolower($this->selectedTicket->status), ['approved', 'for_rescheduling', 'needs_revision', 'rejected']))
                <x-tickets.latest-remark :status="$this->selectedTicket->status" :ticket="$this->selectedTicket" />
            @endif
            @if ($this->selectedTicketComments)
                <div wire:key="comments-list-{{ $this->selectedTicket->ticket_id }}">
                    @foreach ($this->selectedTicketComments as $comment)
                        <div wire:key="comment-{{ $comment->id }}">
                            <x-comment-boxes.normal-comment :comment="$comment" />
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="space-y-3 mt-6" x-data="{ isSubmitting: false }" @comment-added.window="isSubmitting = false">
                <textarea wire:model.defer="comment" class="textarea textarea-bordered w-full h-4" placeholder="Add a comment..."
                    x-on:keydown.ctrl.enter="$wire.addComment(); isSubmitting = true" :disabled="isSubmitting"></textarea>
                <button class="btn btn-primary w-full" wire:click="addComment" x-on:click="isSubmitting = true"
                    :disabled="isSubmitting" wire:loading.attr="disabled">
                    <x-mary-icon name="o-chat-bubble-left-right" class="w-4 h-4" wire:loading.remove
                        wire:target="addComment" />
                    <span wire:loading wire:target="addComment" class="loading loading-spinner loading-sm"></span>
                    <span wire:loading.remove wire:target="addComment">Add Comment</span>
                    <span wire:loading wire:target="addComment">Adding...</span>
                </button>
            </div>
        @else
            <div class="text-center py-8">
                <x-mary-loading class="loading-lg" />
            </div>
        @endif
    </x-mary-modal>

    <x-mary-drawer wire:model="showEditDrawer"
        title="{{ $this->selectedTicket ? 'Edit Ticket - ' . $this->selectedTicket->ticket_number : 'Edit Ticket' }}"
        subtitle="Revise your event request" separator with-close-button close-on-escape right class="w-11/12 lg:w-2/3"
        @close="$wire.closeEditDrawer()">
        @if ($showEditDrawer && $selectedTicketId)
            @livewire('student-org.edit-ticket', ['ticketId' => $selectedTicketId], key('edit-ticket-' . $selectedTicketId))
        @else
            <div class="text-center py-8">
                <x-mary-loading class="loading-lg" />
            </div>
        @endif
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

</div>
