<x-tickets.review
    :ticket="$ticket"
    :allowed-actions="['approve', 'for_revision']"
    :status-overview="$statusOverview"
    :backRoute="route('gso.ticket-review')"
/>
