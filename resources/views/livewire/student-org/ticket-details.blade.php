<div x-data="{
    init() {
        // Auto-scroll to comments section if hash is present
        if (window.location.hash === '#comments') {
            this.$nextTick(() => {
                const commentsSection = document.getElementById('comments');
                if (commentsSection) {
                    setTimeout(() => {
                        commentsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 300);
                }
            });
        }
    }
}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            {{ __('Ticket Details') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Header --}}
            <section class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
                <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
                <div class="relative p-6 sm:p-8">
                    <div class="flex items-center gap-2 text-sm text-base-content/60 mb-3">
                        <a href="{{ route('student-org.my-tickets') }}" class="hover:text-primary transition-colors inline-flex items-center gap-1" wire:navigate>
                            <x-mary-icon name="o-arrow-left" class="w-3.5 h-3.5" />
                            My Tickets
                        </a>
                        <span>/</span>
                        <span class="text-base-content/80">#{{ $ticket->ticket_number }}</span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-base-content font-heading">{{ $ticket->title }}</h1>
                            <p class="text-base-content/70 mt-1">Ticket #{{ $ticket->ticket_number }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @php
                                $statusClasses = [
                                    'received' => 'badge-info',
                                    'gso_review' => 'badge-secondary',
                                    'pending_osa_approval' => 'badge-warning',
                                    'amended' => 'badge-info',
                                    'approved' => 'badge-success',
                                    'for_revision' => 'badge-warning',
                                    'completed' => 'badge-neutral',
                                ];
                                $ticketStatusLabel = ucfirst(str_replace('_', ' ', $ticket->status));
                                $ticketBadgeClass = $statusClasses[$ticket->status] ?? 'badge-neutral';
                            @endphp
                            <span class="badge {{ $ticketBadgeClass }} badge-lg">
                                {{ $ticketStatusLabel }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Main Content --}}

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Ticket Details --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Organization Information --}}
                    <x-tickets.sections.organization-info :ticket="$ticket" />

                    {{-- Event Details --}}
                    <x-tickets.sections.event-details :ticket="$ticket" />

                    {{-- Schedule & Venue --}}
                    <x-tickets.sections.schedule-venue :ticket="$ticket" />

                    {{-- Budget Information --}}
                    <x-tickets.sections.budget-info :ticket="$ticket" />

                    {{-- Additional Information --}}
                    <x-tickets.sections.additional-info :ticket="$ticket" />

                    {{-- Attachments --}}
                    <x-tickets.sections.attachments-list :ticket="$ticket" />

                    {{-- Comments Section --}}
                    <div id="comments" class="scroll-mt-24">
                        <livewire:components.ticket-comments :ticket="$ticket" :key="'ticket-comments-' . $ticket->ticket_id" />
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Ticket Info --}}
                    <div class="bg-base-100 rounded-box shadow-sm border border-base-300/60 p-6">
                        <h2 class="text-lg font-semibold text-base-content mb-4">Ticket Details</h2>
                        @php $userDeleted = $ticket->user?->trashed(); @endphp
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-base-content/70">Ticket Number</label>
                                <p class="text-base-content font-mono">{{ $ticket->ticket_number }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-base-content/70">Submitted By</label>
                                <p class="text-base-content {{ $userDeleted ? 'italic text-base-content/50' : '' }}">
                                    {{ $userDeleted ? 'Deleted User' : $ticket->user?->name }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-base-content/70">Email</label>
                                <p class="text-base-content {{ $userDeleted ? 'italic text-base-content/50' : '' }}">
                                    {{ $userDeleted ? 'N/A' : $ticket->user?->email }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-base-content/70">Submitted</label>
                                <p class="text-base-content">
                                    {{ $ticket->created_at ? $ticket->created_at->format('F d, Y g:i A') : 'TBD' }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-base-content/70">Last Updated</label>
                                <p class="text-base-content">
                                    {{ $ticket->updated_at ? $ticket->updated_at->format('F d, Y g:i A') : 'TBD' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Event Status --}}
                    @if ($ticket->events->isNotEmpty())
                        <div class="bg-base-100 rounded-box shadow-sm border border-base-300/60 p-6">
                            <h2 class="text-lg font-semibold text-base-content mb-4">Event Created</h2>
                            @php
                                $event = $ticket->events->first();
                                $schedule = $event->eventSchedules->first();
                            @endphp
                            @if ($schedule)
                                <div class="space-y-3">
                                    <div class="alert alert-success">
                                        <div class="flex-1">
                                            <p class="font-medium">Event is scheduled!</p>
                                            <p class="text-sm mt-1">
                                                {{ $schedule->start_date ? $schedule->start_date->format('F d, Y') : 'TBD' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-base-content/70">Venue</label>
                                        <p class="text-base-content">{{ $schedule->venue ?? 'TBD' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-base-content/70">Time</label>
                                        <p class="text-base-content">
                                            {{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') : 'TBD' }}
                                            -
                                            {{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') : 'TBD' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-base-content/70">Status</label>
                                        <span
                                            class="badge {{ $schedule->status === 'approved' ? 'badge-success' : 'badge-info' }}">{{ ucfirst($schedule->status) }}</span>
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-base-content/70">Event created but schedule is pending.</p>
                            @endif
                        </div>
                    @endif

                    {{-- Actions --}}
                    @if ($ticket->status === 'for_revision')
                        <div class="bg-base-100 rounded-box shadow-sm border border-base-300/60 p-6">
                            <h2 class="text-lg font-semibold text-base-content mb-4">Actions</h2>
                            <div class="space-y-3">
                                <button class="btn btn-primary w-full" wire:click="openEditDrawer">
                                    <x-mary-icon name="s-pencil" class="w-4 h-4" />
                                    Revise Ticket
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Latest Remark --}}
                    @if (in_array(strtolower($ticket->status), ['approved', 'for_revision']))
                        <div class="bg-base-100 rounded-box shadow-sm border border-base-300/60 p-6">
                            <h2 class="text-lg font-semibold text-base-content mb-4">Latest Decision</h2>
                            <x-tickets.latest-remark :status="$ticket->status" :ticket="$ticket" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Drawer --}}
    <x-mary-drawer wire:model="showEditDrawer" title="{{ 'Edit Ticket - ' . $ticket->ticket_number }}"
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
                @if ($showEditDrawer)
                    <livewire:student-org.edit-ticket :ticketId="$ticket->ticket_id" :key="'edit-ticket-' . $ticket->ticket_id" />
                @endif
            </div>
        </div>
    </x-mary-drawer>

    {{-- JavaScript for attachment handling --}}
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
