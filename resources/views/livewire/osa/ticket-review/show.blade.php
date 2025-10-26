<div>
    {{-- Header --}}
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('osa.ticket-review.index') }}" class="btn btn-ghost btn-sm" wire:navigate>
                            <x-mary-icon name="o-arrow-left" class="w-4 h-4" />
                            Back to Tickets
                        </a>
                    </div>
                    <h1 class="text-3xl font-bold text-base-content">Ticket Review</h1>
                    <p class="text-base-content/70 mt-1">Review event proposal and attached documents</p>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $statusClasses = [
                            'pending' => 'badge-warning',
                            'under_review' => 'badge-info',
                            'pending_osa_approval' => 'badge-secondary',
                            'approved' => 'badge-success',
                            'rejected' => 'badge-error',
                        ];
                    @endphp
                    <x-mary-badge value="{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}"
                        class="{{ $statusClasses[$ticket->status] ?? 'badge-neutral' }}" />
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Ticket Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Basic Information --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Event Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-base-content/70">Event Title</label>
                        <p class="text-base-content font-medium">{{ $ticket->title }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Event Type</label>
                        <p class="text-base-content font-medium">{{ $ticket->eventType->type_name }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Organization</label>
                        <p class="text-base-content">
                            {{ $ticket->user->studentOrganization->org_name ?? 'No Organization' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-base-content/70">Description</label>
                        <p class="text-base-content">{{ $ticket->description }}</p>
                    </div>

                    @if ($ticket->events->isNotEmpty() && $ticket->events->first()->eventSchedules->isNotEmpty())
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Event Date</label>
                            <p class="text-base-content">
                                {{ $ticket->events->first()->eventSchedules->first()->schedule_date?->format('F d, Y') ?? 'TBD' }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-base-content/70">Event Time</label>
                            <p class="text-base-content">
                                {{ $ticket->events->first()->eventSchedules->first()->schedule_date?->format('g:i A') ?? 'TBD' }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-base-content/70">Venue</label>
                            <p class="text-base-content">
                                {{ $ticket->events->first()->eventSchedules->first()->schedule_venue ?? 'TBD' }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-base-content/70">Status</label>
                            <p class="text-base-content">
                                {{ ucfirst($ticket->events->first()->eventSchedules->first()->status ?? 'TBD') }}</p>
                        </div>
                    @elseif($ticket->events->isNotEmpty())
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Requested Date</label>
                            <p class="text-base-content">
                                {{ $ticket->date_requested ? \Carbon\Carbon::parse($ticket->date_requested)->format('F d, Y') : 'TBD' }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-base-content/70">Requested Venue</label>
                            <p class="text-base-content">{{ $ticket->venue_requested ?? 'TBD' }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Attachments --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Attachments</h2>
                @if ($ticket->attachments->count() > 0)
                    <div class="space-y-3">
                        @foreach ($ticket->attachments as $attachment)
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <x-mary-icon name="o-document" class="w-5 h-5 text-primary" />
                                    <div>
                                        <p class="font-medium text-base-content">{{ $attachment->original_name }}</p>
                                        <p class="text-sm text-base-content/70">{{ $attachment->file_size_formatted }}
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('attachments.download', $attachment->id) }}"
                                    class="btn btn-primary btn-sm">
                                    <x-mary-icon name="o-arrow-down-tray" class="w-4 h-4" />
                                    Download
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-mary-icon name="o-document-text" class="w-12 h-12 text-base-content/30 mx-auto mb-3" />
                        <p class="text-base-content/70">No attachments uploaded</p>
                    </div>
                @endif
            </div>
        </div>

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

            {{-- Actions --}}
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-xl font-bold text-base-content mb-4">Actions</h2>
                <div class="space-y-3">
                    <button class="btn btn-success w-full text-base-200 flex justify-between"
                        wire:click="approveTicket">
                        Approve Ticket
                        <x-mary-icon name="o-check-circle" class="w-4 h-4" />
                    </button>

                    <button class="btn btn-warning w-full text-base-200 flex justify-between"
                        wire:click="requestRevision">
                        Request Revision
                        <x-mary-icon name="o-arrow-path" class="w-4 h-4" />
                    </button>

                    <button class="btn btn-info w-full text-base-200 flex justify-between" wire:click="forwardToGso">
                        Forward to GSO
                        <x-mary-icon name="o-arrow-right" class="w-4 h-4" />
                    </button>

                    <button class="btn btn-error w-full text-base-200 flex justify-between" wire:click="rejectTicket">
                        Reject Ticket
                        <x-mary-icon name="o-x-circle" class="w-4 h-4" />
                    </button>
                </div>
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
                                        <p class="font-medium text-sm text-base-content">{{ $comment->user->name }}
                                        </p>
                                        <x-mary-badge value="{{ $comment->user->role }}"
                                            class="badge-primary text-xs" />
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
                    <textarea wire:model="comment" class="textarea textarea-bordered w-full h-4" placeholder="Add a comment..."></textarea>
                    <button class="btn btn-primary w-full" wire:click="addComment">
                        <x-mary-icon name="o-chat-bubble-left-right" class="w-4 h-4" />
                        Add Comment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
