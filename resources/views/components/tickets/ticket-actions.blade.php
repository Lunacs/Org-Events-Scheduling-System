@props(['status', 'ticket'])

<div class="flex flex-col space-y-2">
    <x-mary-button icon="s-eye" class="btn-sm btn-ghost" @click="$dispatch('open-ticket-details', { ticketId: {{ $ticket->ticket_id }} })"
                   tooltip="View Details"/>
    @if(strtolower($status) == 'approved')
        <x-mary-button icon="s-document-arrow-down" class="btn-sm btn-ghost"
                       tooltip="Download Approval" disabled/>
    @elseif(strtolower($status) == 'for_rescheduling')
        <x-mary-button icon="s-pencil" class="btn-sm btn-primary"
                       link="/student-org/reschedule?ticket={{ $ticket->ticket_number }}"
                       tooltip="Reschedule" wire:navigate/>
    @endif
    <x-mary-button icon="s-chat-bubble-left-right" class="btn-sm btn-ghost" @click="$dispatch('open-comment-section', { ticketId: {{ $ticket->ticket_id }} })"
                   tooltip="Comments"/>
</div>
