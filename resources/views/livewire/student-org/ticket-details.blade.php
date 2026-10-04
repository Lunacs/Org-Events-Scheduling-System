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
    @php
        $status = strtolower((string) $ticket->status);
        $userDeleted = $ticket->user?->trashed();
        $forwarded = $ticket->officeApprovals()->exists();
        $remark = $ticket->latestOsaApproval?->remarks;
        $needsRevision = in_array($status, ['for_revision', 'needs_revision'], true);
        $nextStep = \App\Support\TicketStatus::nextStepForOrganization($status);
    @endphp

    <div class="space-y-6">
        <x-ui.page-heading :breadcrumbs="[
            ['label' => 'My tickets', 'link' => route('student-org.my-tickets')],
            ['label' => $ticket->ticket_number],
        ]">
            <x-slot:heading>{{ $ticket->title }}</x-slot:heading>
            <x-slot:meta>
                <x-tickets.progress-badge :status="$ticket->status" />
            </x-slot:meta>
            <x-slot:subtitle>
                <span class="font-mono">{{ $ticket->ticket_number }}</span>
                &middot; Submitted {{ $ticket->created_at?->timezone('Asia/Manila')->format('M j, Y') }}
            </x-slot:subtitle>
        </x-ui.page-heading>

        {{-- Where it stands: status, next step, and the decision that matters --}}
        <section aria-labelledby="status-heading" class="rounded-box border border-base-300 bg-base-100 p-4 sm:p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 id="status-heading" class="text-base font-semibold text-base-content">Where it stands</h2>
                    @if ($nextStep)
                        <p class="mt-0.5 text-sm text-base-content/70">{{ $nextStep }}</p>
                    @endif
                </div>
                @if ($needsRevision)
                    <button type="button" class="btn btn-primary btn-sm" wire:click="openEditDrawer">
                        <x-ui.icon name="o-pencil-square" class="size-4" aria-hidden="true" />
                        Revise and resubmit
                    </button>
                @endif
            </div>

            <x-tickets.status-timeline :status="$ticket->status" :forwarded="$forwarded" class="mt-5" />

            @if (($needsRevision || $status === 'approved' || $status === 'rejected') && $remark)
                <div @class([
                    'mt-5 rounded-field border p-3 text-sm',
                    'border-warning/30 bg-warning/[0.06]' => $needsRevision,
                    'border-success/30 bg-success/[0.06]' => $status === 'approved',
                    'border-error/30 bg-error/[0.06]' => $status === 'rejected',
                ])>
                    <p class="font-medium text-base-content">Remarks from OSA</p>
                    <p class="mt-1 whitespace-pre-line text-base-content/80">{{ $remark }}</p>
                    <p class="mt-2 text-xs text-base-content/70">{{ $ticket->updated_at?->diffForHumans() }}</p>
                </div>
            @elseif ($needsRevision)
                <p class="mt-5 rounded-field border border-warning/30 bg-warning/[0.06] p-3 text-sm text-base-content/80">
                    OSA asked for changes. Check the comments below for details, then revise and resubmit your request.
                </p>
            @endif
        </section>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
            {{-- Request details --}}
            <div class="space-y-6 lg:col-span-2">
                <x-tickets.sections.organization-info :ticket="$ticket" />
                <x-tickets.sections.event-details :ticket="$ticket" />
                <x-tickets.sections.schedule-venue :ticket="$ticket" />
                <x-tickets.sections.budget-info :ticket="$ticket" />
                <x-tickets.sections.additional-info :ticket="$ticket" />
                <x-tickets.sections.attachments-list :ticket="$ticket" />

                <div id="comments" class="scroll-mt-24">
                    <livewire:components.ticket-comments :ticket="$ticket" :key="'ticket-comments-' . $ticket->ticket_id" />
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-6 lg:sticky lg:top-24">
                <x-ui.card title="Request details">
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-base-content/70">Ticket number</dt>
                            <dd class="font-mono text-base-content">{{ $ticket->ticket_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-base-content/70">Submitted by</dt>
                            <dd @class(['text-base-content', 'italic text-base-content/70' => $userDeleted])>
                                {{ $userDeleted ? 'Deleted user' : $ticket->user?->name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-base-content/70">Email</dt>
                            <dd @class(['break-all text-base-content', 'italic text-base-content/70' => $userDeleted])>
                                {{ $userDeleted ? 'Not available' : $ticket->user?->email }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-base-content/70">Submitted</dt>
                            <dd class="text-base-content">
                                {{ $ticket->created_at ? $ticket->created_at->timezone('Asia/Manila')->format('M j, Y g:i A') : 'Not set' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-base-content/70">Last updated</dt>
                            <dd class="text-base-content">
                                {{ $ticket->updated_at ? $ticket->updated_at->timezone('Asia/Manila')->format('M j, Y g:i A') : 'Not set' }}
                            </dd>
                        </div>
                    </dl>
                </x-ui.card>

                @if ($ticket->events->isNotEmpty())
                    @php
                        $event = $ticket->events->first();
                        $schedule = $event->eventSchedules->first();
                    @endphp
                    <x-ui.card title="On the calendar">
                        @if ($schedule)
                            <dl class="space-y-3 text-sm">
                                <div>
                                    <dt class="text-base-content/70">Date</dt>
                                    <dd class="text-base-content">
                                        {{ $schedule->start_date ? $schedule->start_date->format('l, M j, Y') : 'To be announced' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-base-content/70">Time</dt>
                                    <dd class="text-base-content">
                                        {{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') : 'TBA' }}
                                        &ndash;
                                        {{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') : 'TBA' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-base-content/70">Venue</dt>
                                    <dd class="text-base-content">{{ $schedule->venue ?? 'To be announced' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-base-content/70">Schedule status</dt>
                                    <dd class="mt-0.5"><x-tickets.progress-badge :status="$schedule->status" size="sm" /></dd>
                                </div>
                            </dl>
                        @else
                            <p class="text-sm text-base-content/70">The event was created; OSA is finalizing its schedule.</p>
                        @endif
                    </x-ui.card>
                @endif
            </aside>
        </div>
    </div>

    {{-- Edit Drawer --}}
    <x-ui.drawer wire:model="showEditDrawer" title="{{ 'Edit Ticket - ' . $ticket->ticket_number }}"
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
                @if ($showEditDrawer)
                    <livewire:student-org.edit-ticket :ticketId="$ticket->ticket_id" :key="'edit-ticket-' . $ticket->ticket_id" />
                @endif
            </div>
        </div>
    </x-ui.drawer>

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
