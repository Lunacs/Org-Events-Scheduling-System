<x-tickets.review :ticket="$ticket" :allowed-actions="['approve', 'revision', 'forward', 'reject', 'final_approve', 'final_reject']" :backRoute="route('osa.ticket-review.index')" />
