@props(['status'])

@if (in_array(strtolower($status), ['received', 'amended']))
<x-mary-badge value="Under Review" class="badge-info text-white whitespace-nowrap shrink-0" />
@elseif(strtolower($status) == 'gso_review')
<x-mary-badge value="Under GSO Review" class="badge-info text-white whitespace-nowrap shrink-0" />
@elseif(strtolower($status) == 'approved')
<x-mary-badge value="Approved" class="badge-success text-white whitespace-nowrap shrink-0" />
@elseif(strtolower($status) == 'for_revision')
<x-mary-badge value="Requires Revision" class="badge-warning text-white whitespace-nowrap shrink-0" />
@endif