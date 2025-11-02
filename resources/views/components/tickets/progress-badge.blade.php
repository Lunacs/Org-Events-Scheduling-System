@props(['status'])

@if(in_array(strtolower($status), ['received', 'rescheduled', 'amended']))
    <x-mary-badge value="Under Review" class="badge-info"/>
@elseif(strtolower($status) == 'gso_review')
    <x-mary-badge value="Under GSO Review" class="badge-info"/>
@elseif(strtolower($status) == 'approved')
    <x-mary-badge value="Approved" class="badge-success"/>
@elseif(strtolower($status) == 'needs_revision')
    <x-mary-badge value="Requires Revision" class="badge-warning"/>
@elseif(strtolower($status) == 'for_rescheduling')
    <x-mary-badge value="Needs Rescheduling" class="badge-warning"/>
@elseif(strtolower($status) == 'rejected')
    <x-mary-badge value="Rejected" class="badge-error"/>
@endif
