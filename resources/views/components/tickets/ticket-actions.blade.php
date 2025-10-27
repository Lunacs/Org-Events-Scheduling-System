@props(['status'])

<div class="flex flex-col space-y-2">
    <x-mary-button icon="s-eye" class="btn-sm btn-ghost" tooltip="View Details"/>
    @if(strtolower($status) == 'approved')
        <x-mary-button icon="s-document-arrow-down" class="btn-sm btn-ghost"
                       tooltip="Download Approval"/>
    @elseif(in_array(strtolower($status), ['for_rescheduling', 'needs_revision']))
        <x-mary-button icon="s-pencil" class="btn-sm btn-primary" tooltip="Revise"/>
    @endif
    <x-mary-button icon="s-chat-bubble-left-right" class="btn-sm btn-ghost"
                   tooltip="Comments"/>
</div>
