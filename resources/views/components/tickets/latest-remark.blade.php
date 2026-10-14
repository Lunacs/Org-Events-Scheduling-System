@props(['status', 'ticket'])

{{--
    The one remark that matters for a ticket right now: OSA's decision remarks for
    approved / needs-revision tickets, otherwise the latest comment while it is in review.
--}}
@php
    $status = strtolower((string) $status);
    $remark = $ticket->latestOsaApproval?->remarks;
@endphp

@if (in_array($status, ['received', 'amended', 'gso_review', 'pending_osa_approval'], true) && $ticket->comments->isNotEmpty())
    <div class="mt-4 flex items-start gap-2.5 rounded-field bg-base-200 p-3 text-sm">
        <x-ui.icon name="o-chat-bubble-left" class="mt-0.5 size-4 shrink-0 text-base-content/60" aria-hidden="true" />
        <div class="min-w-0 flex-1">
            <p class="font-medium text-base-content">Latest comment from {{ $ticket->latestComment->namingConvention() }}</p>
            <p class="mt-0.5 line-clamp-3 text-base-content/80">{{ $ticket->latestComment->content }}</p>
            <p class="mt-1 text-xs text-base-content/70">{{ $ticket->latestComment->updated_at->diffForHumans() }}</p>
        </div>
    </div>
@elseif ($status === 'approved')
    <div class="mt-4 flex items-start gap-2.5 rounded-field border border-success/30 bg-success/[0.06] p-3 text-sm">
        <x-ui.icon name="s-check-circle" class="mt-0.5 size-4 shrink-0 text-success" aria-hidden="true" />
        <div class="min-w-0 flex-1">
            <p class="font-medium text-base-content">Approved and on the calendar</p>
            <p class="mt-0.5 text-base-content/80">
                {{ $remark ?: 'Follow the safety guidelines during the event and submit your post-event report afterwards.' }}
            </p>
        </div>
    </div>
@elseif (in_array($status, ['for_revision', 'needs_revision'], true))
    <div class="mt-4 flex items-start gap-2.5 rounded-field border border-warning/30 bg-warning/[0.06] p-3 text-sm">
        <x-ui.icon name="s-pencil-square" class="mt-0.5 size-4 shrink-0 text-warning" aria-hidden="true" />
        <div class="min-w-0 flex-1">
            <p class="font-medium text-base-content">OSA requested changes</p>
            <p class="mt-0.5 whitespace-pre-line text-base-content/80">
                {{ $remark ?: 'Open the request and read the comments to see what needs to change, then revise and resubmit it.' }}
            </p>
            <p class="mt-1 text-xs text-base-content/70">{{ $ticket->updated_at->diffForHumans() }}</p>
        </div>
    </div>
@endif
