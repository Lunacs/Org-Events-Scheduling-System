@props(['status', 'ticket'])

@if(in_array(strtolower($status), ['received', 'rescheduled', 'amended', 'gso_review']) && $ticket->comments->isNotEmpty())
    {{-- Latest Comment/Remark --}}
    <div class="bg-blue-50 p-3 rounded-lg">
        <div class="flex items-start space-x-3">
            <x-mary-icon name="s-chat-bubble-left" class="w-5 h-5 text-blue-500 mt-0.5"/>
            <div class="flex-1">
                <p class="text-sm font-medium text-blue-700">Latest Update
                    from {{ $ticket->latestComment->namingConvention() }}</p>
                <p class="text-sm text-blue-600 mt-1">{{ $ticket->latestComment->content }}</p>
                <p class="text-xs text-blue-500 mt-2">{{ $ticket->latestComment->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
@elseif(strtolower($status) == 'approved')
    {{-- Approval Notice --}}
    <div class="bg-green-50 p-3 rounded-lg">
        <div class="flex items-start space-x-3">
            <x-mary-icon name="s-check-circle" class="w-5 h-5 text-green-500 mt-0.5"/>
            <div class="flex-1">
                <p class="text-sm font-medium text-green-700">Event Approved!</p>
                <p class="text-sm text-green-600 mt-1">Congratulations! Your event has been
                    approved. You may now proceed with your preparations.
                    Please ensure to follow all safety guidelines and submit a post-event
                    report.</p>
                @if($ticket->latestOsaApproval?->remarks)
                    <p class="text-sm font-medium text-green-700 mt-2">Latest Remark from OSA:</p>
                    <p class="text-sm text-green-600 mt-1">{{ $ticket->latestOsaApproval->remarks }}</p>
                @endif
                <p class="text-xs text-green-500 mt-2">{{ $ticket->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
@elseif(in_array(strtolower($status), ['for_rescheduling', 'needs_revision']))
    {{-- Revision Request --}}
    <div class="bg-orange-50 p-3 rounded-lg">
        <div class="flex items-start space-x-3">
            <x-mary-icon name="s-exclamation-triangle"
                         class="w-5 h-5 text-orange-500 mt-0.5"/>
            <div class="flex-1">
                <p class="text-sm font-medium text-orange-700">
                    @if(strtolower($status) == 'for_rescheduling')
                        Needs Rescheduling
                    @else
                        Revision Required
                    @endif
                </p>
                @if($ticket->latestOsaApproval?->remarks)
                    <p class="text-sm text-orange-600 mt-1">{{ $ticket->latestOsaApproval->remarks }}</p>
                @else
                    <p class="text-sm text-orange-600 mt-1">Please provide more details about the
                        workshop facilitators, their qualifications, and a detailed schedule for
                        each day. Also, include the registration process for participants and
                        maximum capacity per session.</p>
                @endif
                <p class="text-xs text-orange-500 mt-2">{{ $ticket->updated_at->diffForHumans() }}</p>
                @if(strtolower($status) == 'for_rescheduling')
                    <x-mary-button label="Reschedule Event" icon="s-arrow-up"
                                   class="btn-sm btn-primary mt-2" link="reschedule?ticket={{ $ticket->ticket_number }}" wire:navigate/>
                @else
                    <x-mary-button label="Submit Revision" icon="s-arrow-up"
                                   class="btn-sm btn-primary mt-2" @click="$dispatch('open-ticket-edit', { ticketId: {{ $ticket->ticket_id }} })" tooltip="Revise Event"/>
                @endif

            </div>
        </div>
    </div>
@elseif(strtolower($status) == 'rejected')
    {{-- Approval Notice --}}
    <div class="bg-red-50 p-3 rounded-lg">
        <div class="flex items-start space-x-3">
            <x-mary-icon name="s-x-circle" class="w-5 h-5 text-red-500 mt-0.5"/>
            <div class="flex-1">
                <p class="text-sm font-medium text-red-700">Event Rejected</p>
                @if($ticket->latestOsaApproval?->remarks)
                    <p class="text-sm text-red-600 mt-1">{{ $ticket->latestOsaApproval->remarks }}</p>
                @else
                    <p class="text-sm text-red-600 mt-1">Event rejected. It may be due to different concerns from the
                        offices, or time conflicts. Submit another ticket and try again.</p>
                @endif
                <p class="text-xs text-red-500 mt-2">{{ $ticket->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
@endif

