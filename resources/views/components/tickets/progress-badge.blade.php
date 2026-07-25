@props(['status'])

@if (in_array(strtolower($status), ['received', 'amended']))
    <x-ui.badge value="Under Review" class="badge-info text-white whitespace-normal h-auto" />
@elseif(strtolower($status) == 'gso_review')
    <x-ui.badge value="Under GSO Review" class="badge-info text-white whitespace-normal h-auto" />
@elseif(strtolower($status) == 'approved')
    <x-ui.badge value="Approved" class="badge-success text-white whitespace-normal h-auto" />
@elseif(strtolower($status) == 'for_revision')
    <x-ui.badge value="Requires Revision" class="badge-warning text-white whitespace-normal h-auto" />
@endif
