<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success mb-6" x-data="{ show: true }" x-show="show"
             x-init="setTimeout(() => show = false, 5000)">
            <x-mary-icon name="o-check-circle" class="w-5 h-5"/>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error mb-6" x-data="{ show: true }" x-show="show"
             x-init="setTimeout(() => show = false, 5000)">
            <x-mary-icon name="o-x-circle" class="w-5 h-5"/>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="alert alert-info mb-6" x-data="{ show: true }" x-show="show"
             x-init="setTimeout(() => show = false, 5000)">
            <x-mary-icon name="o-information-circle" class="w-5 h-5"/>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="alert alert-warning mb-6" x-data="{ show: true }" x-show="show"
             x-init="setTimeout(() => show = false, 5000)">
            <x-mary-icon name="o-exclamation-triangle" class="w-5 h-5"/>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('osa.ticket-review.index') }}" class="btn btn-ghost btn-sm" wire:navigate>
                            <x-mary-icon name="o-arrow-left" class="w-4 h-4"/>
                            Back to Tickets
                        </a>
                    </div>
                    <h1 class="text-3xl font-bold text-base-content">Ticket Review</h1>
                    <p class="text-base-content/70 mt-1">Review event proposal and attached documents</p>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $statusClasses = [
                            'received' => 'badge-info',
                            'gso_review' => 'badge-secondary',
                            'pending_osa_approval' => 'badge-warning',
                            'for_rescheduling' => 'badge-warning',
                            'rescheduled' => 'badge-success',
                            'needs_revision' => 'badge-warning',
                            'amended' => 'badge-info',
                            'approved' => 'badge-success',
                            'rejected' => 'badge-error',
                        ];
                    @endphp
                    <x-mary-badge value="{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}"
                                  class="{{ $statusClasses[$ticket->status] ?? 'badge-neutral' }}"/>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Ticket Details --}}
        <x-tickets.ticket-preview :ticket="$ticket"/>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Ticket Info --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Ticket Details</h2>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-base-content/70">Ticket Number</label>
                        <p class="text-base-content font-mono">{{ $ticket->ticket_number }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Submitted By</label>
                        <p class="text-base-content">{{ $ticket->user->name }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Email</label>
                        <p class="text-base-content">{{ $ticket->user->email }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Submitted</label>
                        <p class="text-base-content">{{ $ticket->created_at->format('F d, Y g:i A') }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Last Updated</label>
                        <p class="text-base-content">{{ $ticket->updated_at->format('F d, Y g:i A') }}</p>
                    </div>
                </div>
            </div>

            {{-- Event Status --}}
            @if ($ticket->events->isNotEmpty())
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
                        <x-mary-icon name="o-check-badge" class="w-5 h-5 text-success"/>
                        Event Created
                    </h2>
                    @php
                        $event = $ticket->events->first();
                        $schedule = $event->eventSchedules->first();
                    @endphp
                    @if ($schedule)
                        <div class="space-y-3">
                            <div class="alert alert-success">
                                <x-mary-icon name="o-calendar-days" class="w-5 h-5"/>
                                <div class="flex-1">
                                    <p class="font-medium">Event is scheduled!</p>
                                    <p class="text-sm mt-1">{{ $schedule->start_date->format('F d, Y') }}
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
                                <x-mary-badge value="{{ ucfirst($schedule->status) }}"
                                              class="{{ $schedule->status === 'approved' ? 'badge-success' : 'badge-info' }}"/>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-base-content/70">Event created but schedule is pending.</p>
                    @endif
                </div>
            @endif

            {{-- Approval History --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
                    <x-mary-icon name="o-clock" class="w-5 h-5"/>
                    Approval History
                </h2>

                @php
                    // Combine all approvals into one collection with timestamps
                    $allApprovals = collect();

                    // Add OSA approvals
                    foreach ($ticket->osaApprovals as $approval) {
                        $allApprovals->push([
                            'type' => 'OSA',
                            'office_name' => 'Office of Student Affairs',
                            'user' => $approval->user,
                            'decision' => $approval->decision,
                            'remarks' => $approval->remarks,
                            'created_at' => $approval->created_at,
                        ]);
                    }

                    // Add Office approvals (GSO, etc.)
                    foreach ($ticket->officeApprovals as $approval) {
                        $allApprovals->push([
                            'type' => 'Office',
                            'office_name' => $approval->office->office_name ?? 'Unknown Office',
                            'user' => $approval->user,
                            'decision' => $approval->decision,
                            'remarks' => $approval->remarks,
                            'created_at' => $approval->created_at,
                        ]);
                    }

                    // Sort by date (most recent first)
                    $allApprovals = $allApprovals->sortByDesc('created_at');

                    // Decision badge classes
                    $decisionClasses = [
                        'approved' => 'badge-success',
                        'rejected' => 'badge-error',
                        'pending' => 'badge-warning',
                        'forwarded' => 'badge-info',
                        'under_review' => 'badge-info',
                        'revision_requested' => 'badge-warning',
                        'needs_revision' => 'badge-warning',
                    ];
                @endphp

                @if ($allApprovals->count() > 0)
                    <div class="relative">
                        {{-- Timeline Line --}}
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-base-300"></div>

                        <div class="space-y-6">
                            @foreach ($allApprovals as $index => $approval)
                                <div class="relative pl-10">
                                    {{-- Timeline Dot --}}
                                    <div
                                        class="absolute left-2 top-1 w-4 h-4 rounded-full {{ $approval['decision'] === 'approved' ? 'bg-success' : ($approval['decision'] === 'rejected' ? 'bg-error' : ($approval['decision'] === 'pending' ? 'bg-warning' : ($approval['decision'] === 'forwarded' ? 'bg-info' : 'bg-info'))) }} ring-4 ring-base-100">
                                    </div>

                                    {{-- Content --}}
                                    <div class="bg-base-200 rounded-lg p-3">
                                        <div class="flex justify-between items-start mb-1">
                                            <div>
                                                <p class="font-semibold text-base-content text-sm">
                                                    {{ $approval['office_name'] }}
                                                </p>
                                                <p class="text-xs text-base-content/70">
                                                    {{ $approval['user']->name ?? 'System' }}
                                                </p>
                                            </div>
                                            <x-mary-badge
                                                value="{{ ucfirst(str_replace('_', ' ', $approval['decision'])) }}"
                                                class="{{ $decisionClasses[$approval['decision']] ?? 'badge-neutral' }} badge-sm"/>
                                        </div>

                                        @if ($approval['remarks'])
                                            <div class="mt-2 pt-2 border-t border-base-300">
                                                <p class="text-xs font-medium text-base-content/70 mb-1">
                                                    Remarks:</p>
                                                <p class="text-sm text-base-content/80">
                                                    {{ $approval['remarks'] }}</p>
                                            </div>
                                        @endif

                                        <p class="text-xs text-base-content/50 mt-2">
                                            {{ $approval['created_at']->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-mary-icon name="o-clock" class="w-12 h-12 text-base-content/30 mx-auto mb-3"/>
                        <p class="text-base-content/70">No approval actions yet</p>
                        <p class="text-sm text-base-content/50 mt-1">This ticket is awaiting review</p>
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Actions</h2>

                @if ($ticket->status === 'pending_osa_approval')
                    {{-- Final Decision After GSO Review --}}
                    @php
                        $gsoApproval = $ticket->officeApprovals->first();
                    @endphp

                    @if ($gsoApproval)
                        <div
                            class="alert mb-4 {{ $gsoApproval->decision === 'approved' ? 'alert-success' : 'alert-error' }}">
                            <x-mary-icon name="o-information-circle" class="w-5 h-5"/>
                            <div>
                                <p class="font-semibold">GSO has {{ $gsoApproval->decision }} this request
                                </p>
                                <p class="text-sm mt-1">{{ $gsoApproval->remarks }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <button class="btn btn-success w-full text-base-200 flex justify-between"
                                wire:click="openFinalApprovalModal">
                            Final Approval
                            <x-mary-icon name="o-check-circle" class="w-4 h-4"/>
                        </button>

                        <button class="btn btn-error w-full text-base-200 flex justify-between"
                                wire:click="openFinalRejectionModal">
                            Final Rejection
                            <x-mary-icon name="o-x-circle" class="w-4 h-4"/>
                        </button>
                    </div>
                @elseif (in_array($ticket->status, ['received', 'amended']))
                    {{-- Initial Review Actions --}}
                    <div class="space-y-3">
                        <button class="btn btn-success w-full text-base-200 flex justify-between"
                                wire:click="openApprovalModal">
                            Approve Ticket
                            <x-mary-icon name="o-check-circle" class="w-4 h-4"/>
                        </button>

                        <button class="btn btn-warning w-full text-base-200 flex justify-between"
                                wire:click="openRevisionModal">
                            Request Revision
                            <x-mary-icon name="o-arrow-path" class="w-4 h-4"/>
                        </button>

                        <button class="btn btn-info w-full text-base-200 flex justify-between"
                                wire:click="openForwardModal">
                            Forward to GSO
                            <x-mary-icon name="o-arrow-right" class="w-4 h-4"/>
                        </button>

                        <button class="btn btn-error w-full text-base-200 flex justify-between"
                                wire:click="openRejectionModal">
                            Reject Ticket
                            <x-mary-icon name="o-x-circle" class="w-4 h-4"/>
                        </button>
                    </div>
                @else
                    <div class="alert alert-info">
                        <x-mary-icon name="o-information-circle" class="w-5 h-5"/>
                        <span>
                            @if ($ticket->status === 'approved')
                                This ticket has been <u>approved</u>.
                            @elseif($ticket->status === 'rejected')
                                This ticket has been <u>rejected</u>.
                            @elseif($ticket->status === 'needs_revision')
                                Waiting for student organization to <u>revise and resubmit</u>.
                            @elseif($ticket->status === 'gso_review')
                                This ticket has been <u>forwarded to GSO for review</u>.
                            @else
                                No actions available for current ticket status.
                            @endif
                        </span>
                    </div>
                @endif
            </div>

            {{-- Comments --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Comments</h2>
                @if ($ticket->comments->count() > 0)
                    <div class="mt-4 space-y-3">
                        @foreach ($ticket->comments as $comment)
                            <div class="p-3 bg-base-200 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium text-sm text-base-content">
                                            {{ $comment->user->name }}
                                        </p>
                                        <x-mary-badge value="{{ $comment->user->role }}"
                                                      class="badge-primary text-xs"/>
                                    </div>

                                    <p class="text-xs text-base-content/70">
                                        {{ $comment->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <p class="text-sm text-base-content/80">{{ $comment->content }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="space-y-3 mt-4">
                    <textarea wire:model="comment" class="textarea textarea-bordered w-full h-4"
                              placeholder="Add a comment..."></textarea>
                    <button class="btn btn-primary w-full" wire:click="addComment">
                        <x-mary-icon name="o-chat-bubble-left-right" class="w-4 h-4"/>
                        Add Comment
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval Modal --}}
    <x-mary-modal wire:model="showApprovalModal" title="Confirm Ticket Approval" class="backdrop-blur">
        <div class="space-y-4">
            <div class="alert alert-success">
                <x-mary-icon name="o-check-circle" class="w-6 h-6"/>
                <div>
                    <h3 class="font-bold">You are about to approve this ticket</h3>
                    <p class="text-sm">This action will create an event and schedule it on the calendar.
                    </p>
                </div>
            </div>

            <x-mary-textarea wire:model="approvalRemarks" label="Approval Remarks"
                             placeholder="Enter your remarks for approving this ticket..." rows="4"
                             hint="Provide a brief explanation for this approval"/>
            @error('approvalRemarks')
            <span class="text-error text-sm">{{ $message }}</span>
            @enderror
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="closeApprovalModal"/>
            <x-mary-button label="Confirm Approval" class="btn-success" wire:click="approveTicket"
                           spinner="approveTicket"/>
        </x-slot:actions>
    </x-mary-modal>

    {{-- Rejection Modal --}}
    <x-mary-modal wire:model="showRejectionModal" title="Confirm Ticket Rejection" class="backdrop-blur">
        <div class="space-y-4">
            <div class="alert alert-error">
                <x-mary-icon name="o-x-circle" class="w-6 h-6"/>
                <div>
                    <h3 class="font-bold">You are about to reject this ticket</h3>
                    <p class="text-sm">This action cannot be undone. No event will be created.</p>
                </div>
            </div>

            <x-mary-textarea wire:model="rejectionRemarks" label="Rejection Remarks"
                             placeholder="Explain the reason for rejecting this ticket..." rows="4"
                             hint="Provide detailed explanation for the rejection (minimum 10 characters)"/>
            @error('rejectionRemarks')
            <span class="text-error text-sm">{{ $message }}</span>
            @enderror
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="closeRejectionModal"/>
            <x-mary-button label="Confirm Rejection" class="btn-error" wire:click="rejectTicket"
                           spinner="rejectTicket"/>
        </x-slot:actions>
    </x-mary-modal>

    {{-- Revision Request Modal --}}
    <x-mary-modal wire:model="showRevisionModal" title="Request Ticket Revision" class="backdrop-blur">
        <div class="space-y-4">
            <div class="alert alert-warning">
                <x-mary-icon name="o-arrow-path" class="w-6 h-6"/>
                <div>
                    <h3 class="font-bold">Request changes to this ticket</h3>
                    <p class="text-sm">The student organization will need to revise and resubmit.</p>
                </div>
            </div>

            <x-mary-textarea wire:model="revisionRemarks" label="Revision Instructions"
                             placeholder="Clearly explain what needs to be changed or added..." rows="5"
                             hint="Be specific about what needs to be revised (minimum 10 characters)"/>
            @error('revisionRemarks')
            <span class="text-error text-sm">{{ $message }}</span>
            @enderror
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="closeRevisionModal"/>
            <x-mary-button label="Request Revision" class="btn-warning" wire:click="requestRevision"
                           spinner="requestRevision"/>
        </x-slot:actions>
    </x-mary-modal>

    {{-- Forward to GSO Modal --}}
    <x-mary-modal wire:model="showForwardModal" title="Forward to GSO" class="backdrop-blur">
        <div class="space-y-4">
            <div class="alert alert-info">
                <x-mary-icon name="o-arrow-right" class="w-6 h-6"/>
                <div>
                    <h3 class="font-bold">Forward this ticket to GSO</h3>
                    <p class="text-sm">GSO will review and provide their decision. You'll make the final
                        approval.</p>
                </div>
            </div>

            <x-mary-textarea wire:model="forwardRemarks" label="Forwarding Remarks"
                             placeholder="Enter remarks for GSO..." rows="4"
                             hint="Explain why this needs GSO review or what specific approval is needed"/>
            @error('forwardRemarks')
            <span class="text-error text-sm">{{ $message }}</span>
            @enderror
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="closeForwardModal"/>
            <x-mary-button label="Forward to GSO" class="btn-info" wire:click="forwardToGso"
                           spinner="forwardToGso"/>
        </x-slot:actions>
    </x-mary-modal>

    {{-- Final Approval Modal --}}
    <x-mary-modal wire:model="showFinalApprovalModal" title="Final Approval" class="backdrop-blur">
        <div class="space-y-4">
            <div class="alert alert-success">
                <x-mary-icon name="o-check-badge" class="w-6 h-6"/>
                <div>
                    <h3 class="font-bold">Final approval after GSO review</h3>
                    <p class="text-sm">This will create the event and schedule it on the calendar.</p>
                </div>
            </div>

            <x-mary-textarea wire:model="finalApprovalRemarks" label="Final Approval Remarks"
                             placeholder="Enter your final approval remarks..." rows="4"
                             hint="Document your final decision after considering GSO's input"/>
            @error('finalApprovalRemarks')
            <span class="text-error text-sm">{{ $message }}</span>
            @enderror
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="closeFinalApprovalModal"/>
            <x-mary-button label="Confirm Final Approval" class="btn-success" wire:click="finalApproval"
                           spinner="finalApproval"/>
        </x-slot:actions>
    </x-mary-modal>

    {{-- Final Rejection Modal --}}
    <x-mary-modal wire:model="showFinalRejectionModal" title="Final Rejection" class="backdrop-blur">
        <div class="space-y-4">
            <div class="alert alert-error">
                <x-mary-icon name="o-x-circle" class="w-6 h-6"/>
                <div>
                    <h3 class="font-bold">Final rejection after GSO review</h3>
                    <p class="text-sm">This action cannot be undone. No event will be created.</p>
                </div>
            </div>

            <x-mary-textarea wire:model="finalRejectionRemarks" label="Final Rejection Remarks"
                             placeholder="Explain the reason for final rejection..." rows="4"
                             hint="Provide detailed explanation considering GSO's input (minimum 10 characters)"/>
            @error('finalRejectionRemarks')
            <span class="text-error text-sm">{{ $message }}</span>
            @enderror
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" wire:click="closeFinalRejectionModal"/>
            <x-mary-button label="Confirm Final Rejection" class="btn-error" wire:click="finalRejection"
                           spinner="finalRejection"/>
        </x-slot:actions>
    </x-mary-modal>

    {{-- Add JavaScript for handling Livewire events --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('ticket-approved', () => {
                // Optional: Add any client-side actions when ticket is approved
                console.log('Ticket approved successfully');
            });

            Livewire.on('ticket-forwarded', () => {
                // Optional: Add any client-side actions when ticket is forwarded
                console.log('Ticket forwarded to GSO');
            });

            Livewire.on('ticket-revision-requested', () => {
                // Optional: Add any client-side actions when revision is requested
                console.log('Ticket revision requested');
            });

            Livewire.on('ticket-rejected', () => {
                // Optional: Add any client-side actions when ticket is rejected
                console.log('Ticket rejected');
            });

            Livewire.on('ticket-final-approved', () => {
                // Optional: Add any client-side actions for final approval
                console.log('Ticket final approval completed');
            });

            Livewire.on('ticket-final-rejected', () => {
                // Optional: Add any client-side actions for final rejection
                console.log('Ticket final rejection completed');
            });

            Livewire.on('comment-added', () => {
                // Optional: Add any client-side actions when comment is added
                console.log('Comment added successfully');
            });
        });
    </script>
</div>
