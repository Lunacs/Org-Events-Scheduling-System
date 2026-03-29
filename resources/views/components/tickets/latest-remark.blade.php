@props(['status', 'ticket'])

@if(in_array(strtolower($status), ['received', 'amended', 'gso_review']) && $ticket->comments->isNotEmpty())
    <div class="bg-info/10 p-3 rounded-lg">
        <div class="flex items-start gap-3">
            <x-mary-icon name="s-chat-bubble-left" class="w-5 h-5 text-info mt-0.5"/>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-info">Latest Update
                    from {{ $ticket->latestComment->namingConvention() }}</p>
                <p class="text-sm text-base-content/70 mt-1">{{ $ticket->latestComment->content }}</p>
                <p class="text-xs text-base-content/50 mt-2">{{ $ticket->latestComment->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
@elseif(strtolower($status) == 'approved')
    <div class="bg-success/10 p-3 rounded-lg">
        <div class="flex items-start gap-3">
            <x-mary-icon name="s-check-circle" class="w-5 h-5 text-success mt-0.5"/>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-success">Event Approved!</p>
                <p class="text-sm text-base-content/70 mt-1">Congratulations! Your event has been
                    approved. You may now proceed with your preparations.
                    Please ensure to follow all safety guidelines and submit a post-event
                    report.</p>
                @if($ticket->latestOsaApproval?->remarks)
                    <p class="text-sm font-medium text-success mt-2">Latest Remark from OSA:</p>
                    <p class="text-sm text-base-content/70 mt-1">{{ $ticket->latestOsaApproval->remarks }}</p>
                @endif
                <p class="text-xs text-base-content/50 mt-2">{{ $ticket->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
@elseif(in_array(strtolower($status), ['for_revision']))
    <div class="bg-warning/10 p-3 rounded-lg">
        <div class="flex items-start gap-3">
            <x-mary-icon name="s-exclamation-triangle"
                         class="w-5 h-5 text-warning mt-0.5"/>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-warning">
                    For Revision
                </p>
                @if($ticket->latestOsaApproval?->remarks)
                    <p class="text-sm text-base-content/70 mt-1">{{ $ticket->latestOsaApproval->remarks }}</p>
                @else
                    <p class="text-sm text-base-content/70 mt-1">Event for revision. It may be due to different concerns from
                        the
                        offices, or time conflicts. Submit another ticket and try again.</p>
                @endif
                <p class="text-xs text-base-content/50 mt-2">{{ $ticket->updated_at->diffForHumans() }}</p>
                    <x-mary-button label="Submit Revision" icon="s-arrow-up"
                                   class="btn-sm btn-primary mt-2"
                                   tooltip="Revise Event"
                                   wire:click="$dispatch('resubmit-ticket', { ticketId: {{ $ticket->ticket_id }} })"/>
            </div>
        </div>
    </div>
@endif
