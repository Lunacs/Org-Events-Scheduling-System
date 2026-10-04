@props([
    'status',
    // Whether the ticket was (or is being) routed through GSO.
    'forwarded' => false,
])

{{--
    x-tickets.status-timeline — where a ticket is in the approval workflow (PRODUCT.md
    "Status is the product"). Submitted → OSA review → GSO review (only when forwarded)
    → Decision. Each step states its condition in text for assistive tech; color and
    icon are reinforcement, never the only signal.
--}}
@php
    $status = strtolower((string) $status);
    $forwarded = $forwarded || in_array($status, ['gso_review', 'pending_osa_approval'], true);

    // done | current | attention | pending | success | error
    $osa = match ($status) {
        'received', 'pending', 'amended' => 'current',
        'for_revision', 'needs_revision' => 'attention',
        default => 'done',
    };
    $gso = match ($status) {
        'gso_review' => 'current',
        'received', 'pending', 'amended', 'for_revision', 'needs_revision' => 'pending',
        default => 'done',
    };
    $decision = match ($status) {
        'approved', 'completed' => 'success',
        'rejected' => 'error',
        'pending_osa_approval' => 'current',
        default => 'pending',
    };

    $steps = [
        ['label' => 'Submitted', 'detail' => 'Request received', 'state' => 'done'],
        [
            'label' => 'OSA review',
            'detail' => $osa === 'attention' ? 'Changes requested' : ($osa === 'current' ? 'In progress' : 'Reviewed'),
            'state' => $osa,
        ],
    ];
    if ($forwarded) {
        $steps[] = [
            'label' => 'GSO review',
            'detail' => $gso === 'current' ? 'Venue and logistics check' : ($gso === 'done' ? 'Responded' : 'Not started'),
            'state' => $gso,
        ];
    }
    $steps[] = [
        'label' => 'Decision',
        'detail' => match ($decision) {
            'success' => 'Approved',
            'error' => 'Not approved',
            'current' => 'Awaiting OSA',
            default => 'Not yet',
        },
        'state' => $decision,
    ];

    $srState = [
        'done' => 'completed',
        'success' => 'completed',
        'current' => 'current step',
        'attention' => 'needs your action',
        'error' => 'not approved',
        'pending' => 'not started',
    ];
@endphp

<ol {{ $attributes->class([
    'grid gap-x-3 gap-y-4',
    'grid-cols-3' => count($steps) === 3,
    'grid-cols-2 sm:grid-cols-4' => count($steps) === 4,
]) }}>
    @foreach ($steps as $step)
        @php
            $s = $step['state'];
        @endphp
        <li class="min-w-0" @if (in_array($s, ['current', 'attention'])) aria-current="step" @endif>
            <div @class([
                'mb-2 h-1 rounded-full',
                'bg-success' => in_array($s, ['done', 'success']),
                'bg-info' => $s === 'current',
                'bg-warning' => $s === 'attention',
                'bg-error' => $s === 'error',
                'bg-base-300' => $s === 'pending',
            ])></div>
            <div class="flex items-start gap-1.5">
                @if (in_array($s, ['done', 'success']))
                    <x-ui.icon name="s-check-circle" class="mt-px size-4 shrink-0 text-success" aria-hidden="true" />
                @elseif ($s === 'current')
                    <x-ui.icon name="s-clock" class="mt-px size-4 shrink-0 text-info" aria-hidden="true" />
                @elseif ($s === 'attention')
                    <x-ui.icon name="s-pencil-square" class="mt-px size-4 shrink-0 text-warning" aria-hidden="true" />
                @elseif ($s === 'error')
                    <x-ui.icon name="s-x-circle" class="mt-px size-4 shrink-0 text-error" aria-hidden="true" />
                @else
                    <span class="mt-[5px] size-2.5 shrink-0 rounded-full border-2 border-base-300" aria-hidden="true"></span>
                @endif
                <div class="min-w-0">
                    <p @class([
                        'truncate text-sm font-medium',
                        'text-base-content' => $s !== 'pending',
                        'text-base-content/60' => $s === 'pending',
                    ])>{{ $step['label'] }}</p>
                    <p class="truncate text-xs text-base-content/70">
                        {{ $step['detail'] }}<span class="sr-only">, {{ $srState[$s] }}</span>
                    </p>
                </div>
            </div>
        </li>
    @endforeach
</ol>
