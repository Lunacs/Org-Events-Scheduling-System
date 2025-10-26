<div>
    {{-- Header --}}
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-base-content">Approvals</h1>
                    <p class="text-base-content/70 mt-1">Final approval authority for event requests</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-mary-badge value="{{ $tickets->total() }} Pending" class="badge-warning" />
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-mary-input wire:model.live.debounce.300ms="search" placeholder="Search tickets..."
                icon="o-magnifying-glass" clearable />

            <x-mary-select wire:model.live="statusFilter" placeholder="Filter by Status" :options="[
                ['id' => 'pending_osa_approval', 'name' => 'Pending OSA Approval'],
                ['id' => 'approved', 'name' => 'Approved'],
                ['id' => 'rejected', 'name' => 'Rejected'],
            ]"
                option-value="id" option-label="name" />

            <x-mary-button wire:click="clearFilters" class="btn-ghost" icon="o-x-mark">
                <span wire:loading.remove wire:target="clearFilters">Clear Filters</span>
                <span wire:loading wire:target="clearFilters">Clearing...</span>
            </x-mary-button>
        </div>
    </div>

    {{-- Approval Cards --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6" wire:loading.class="opacity-50"
        wire:target="search,statusFilter">
        @forelse($tickets as $ticket)
            <div class="bg-base-100 rounded-box shadow-lg overflow-hidden" wire:key="approval-{{ $ticket->ticket_id }}">
                <div class="p-6">
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="font-bold text-lg text-base-content">{{ $ticket->title }}</h3>
                            <p class="text-sm text-base-content/70 mt-1">
                                {{ $ticket->user->studentOrganization->org_name ?? 'No Organization' }}</p>
                            <p class="text-xs text-base-content/50 mt-1">Ticket
                                #{{ str_pad($ticket->ticket_id ?? 0, 4, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        @php
                            $statusClasses = [
                                'pending_osa_approval' => 'badge-warning',
                                'approved' => 'badge-success',
                                'rejected' => 'badge-error',
                            ];
                        @endphp
                        <x-mary-badge value="{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}"
                            class="{{ $statusClasses[$ticket->status] ?? 'badge-neutral' }}" />
                    </div>

                    {{-- Event Details --}}
                    @if ($ticket->events->isNotEmpty())
                        <div class="bg-base-200 rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="font-medium text-base-content/70">Event Type:</span>
                                    <p>{{ $ticket->eventType->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-base-content/70">Expected Attendees:</span>
                                    <p>{{ $ticket->events->first()->expected_attendees ?? 'N/A' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <span class="font-medium text-base-content/70">Venue:</span>
                                    <p>{{ $ticket->events->first()->venue ?? 'TBD' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Description --}}
                    <div class="mb-4">
                        <p class="text-sm text-base-content/80 line-clamp-3">{{ $ticket->description }}</p>
                    </div>

                    {{-- Previous Approvals Status --}}
                    <div class="mb-4">
                        <div class="flex items-center gap-2 text-sm">
                            <x-mary-icon name="o-check-circle" class="w-4 h-4 text-success" />
                            <span class="text-success">Office approvals completed</span>
                        </div>
                    </div>

                    {{-- Existing OSA Approval --}}
                    @if ($ticket->osaApprovals->isNotEmpty())
                        <div class="bg-base-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <x-mary-icon name="o-user" class="w-4 h-4" />
                                <span class="font-medium">Previous OSA Decision</span>
                            </div>
                            <div class="text-sm space-y-1">
                                <p><strong>Status:</strong> {{ ucfirst($ticket->osaApprovals->first()->status) }}</p>
                                <p><strong>Date:</strong>
                                    {{ $ticket->osaApprovals->first()->approved_at?->format('M d, Y h:i A') }}</p>
                                @if ($ticket->osaApprovals->first()->comments)
                                    <p><strong>Comments:</strong> {{ $ticket->osaApprovals->first()->comments }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <x-mary-button wire:click="viewApproval({{ $ticket->ticket_id }})"
                            class="btn-primary btn-sm flex-1" icon="o-eye">
                            Review & Approve
                        </x-mary-button>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-base-200 px-6 py-3">
                    <div class="flex items-center justify-between text-xs text-base-content/70">
                        <span>Submitted {{ $ticket->created_at?->diffForHumans() ?? 'N/A' }}</span>
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-paper-clip" class="w-3 h-3" />
                            <span>{{ $ticket->attachments->count() }} files</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="flex flex-col items-center gap-4">
                    <x-mary-icon name="o-check-circle" class="w-16 h-16 text-base-content/30" />
                    <div>
                        <h3 class="text-lg font-semibold text-base-content/70">No tickets pending approval</h3>
                        <p class="text-sm text-base-content/50">All tickets have been processed</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($tickets->hasPages())
        <div class="mt-6">
            {{ $tickets->links() }}
        </div>
    @endif

    {{-- Approval Modal --}}
    <x-mary-modal wire:model="showModal" title="Process Approval" class="modal-lg">
        @if ($selectedTicket)
            <div class="space-y-6">
                {{-- Ticket Details --}}
                <div class="border-b border-base-300 pb-4">
                    <h2 class="text-xl font-bold">{{ $selectedTicket->title }}</h2>
                    <p class="text-base-content/70">
                        {{ $selectedTicket->user->studentOrganization->org_name ?? 'No Organization' }}</p>
                    <p class="text-sm text-base-content/50">Ticket
                        #{{ str_pad($selectedTicket->ticket_id ?? 0, 4, '0', STR_PAD_LEFT) }}</p>
                </div>

                {{-- Event Information --}}
                @if ($selectedTicket->events->isNotEmpty())
                    <div class="bg-base-200 rounded-lg p-4">
                        <h3 class="font-semibold mb-3">Event Details</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <label class="font-medium text-base-content/70">Event Type</label>
                                <p>{{ $selectedTicket->eventType->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="font-medium text-base-content/70">Expected Attendees</label>
                                <p>{{ $selectedTicket->events->first()->expected_attendees ?? 'N/A' }}</p>
                            </div>
                            <div class="col-span-2">
                                <label class="font-medium text-base-content/70">Venue</label>
                                <p>{{ $selectedTicket->events->first()->venue ?? 'TBD' }}</p>
                            </div>
                            <div class="col-span-2">
                                <label class="font-medium text-base-content/70">Description</label>
                                <p>{{ $selectedTicket->description }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Approval Form --}}
                <div class="space-y-4">
                    <h3 class="font-semibold">OSA Decision</h3>

                    <x-mary-radio wire:model="approvalAction" :options="[
                        ['id' => 'approved', 'name' => 'Approve this event request'],
                        ['id' => 'rejected', 'name' => 'Reject this event request'],
                    ]" option-value="id" option-label="name" />

                    <x-mary-textarea wire:model="comments" label="Comments (Optional)"
                        placeholder="Add any comments or conditions for approval/rejection..." rows="4" />
                </div>
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button wire:click="closeModal" class="btn-ghost">Cancel</x-mary-button>
            <x-mary-button wire:click="processApproval" class="btn-primary" :disabled="!$approvalAction">
                <span wire:loading.remove wire:target="processApproval">Submit Decision</span>
                <span wire:loading wire:target="processApproval">Processing...</span>
            </x-mary-button>
        </x-slot:actions>
    </x-mary-modal>

    {{-- Success Message --}}
    @if (session()->has('message'))
        <x-mary-toast type="success" title="Success!" description="{{ session('message') }}" />
    @endif
</div>
