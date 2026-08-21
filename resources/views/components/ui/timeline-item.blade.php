@props([
    'title' => null,
    'subtitle' => null,
    'description' => null,
    'icon' => null,
    'pending' => false,
    'first' => false,
    'last' => false,
    'connectorPendingClass' => 'border-s-base-300',
    'connectorActiveClass' => '!border-s-primary',
    'bulletActiveClass' => '!bg-primary',
    'bulletPendingClass' => 'bg-base-300',
])

{{--
    x-ui.timeline-item — replaces `x-mary-timeline-item`.

    A vertical timeline node built with flex + border utilities, mirroring MaryUI's
    TimelineItem layout. The left border acts as the connector line, a bullet marks the
    node (enlarged when an icon is present), and title/subtitle/description render the
    content. Icons render through the x-ui.icon shim. `pending` renders a muted state;
    `first`/`last` trim the connector at the ends. Pass-through class is forwarded to the
    root element.
--}}
<div {{ $attributes }}>
    {{-- Last item connector cut --}}
    <div @class([
        "border-s-2 {$connectorPendingClass} h-5 -mb-5" => $last,
        $connectorActiveClass => !$pending,
    ])></div>

    {{-- WRAPPER THAT ALSO ACTS AS A LINE CONNECTOR --}}
    <div @class([
        "border-s-2 {$connectorPendingClass} ps-8 py-3",
        $connectorActiveClass => !$pending,
        'pt-0' => $first,
        '!border-s-0' => $last,
    ])>
        {{-- BULLET --}}
        <div @class([
            "w-4 h-4 -mb-5 -ms-[41px] {$bulletPendingClass} rounded-full",
            $bulletActiveClass => !$pending,
            '!-ms-[39px]' => $last,
            'w-8 h-8 !-ms-[48px] -mb-7' => filled($icon),
        ])>
            @if (filled($icon))
                <x-ui.icon :name="$icon" @class(['ms-2 mt-1 w-4 h-4', 'text-base-100' => !$pending]) />
            @endif
        </div>

        {{-- TITLE --}}
        <div class="font-bold mb-1">{{ $title }}</div>

        {{-- SUBTITLE --}}
        @if (filled($subtitle))
            <div class="text-xs text-base-content/30 font-bold">{{ $subtitle }}</div>
        @endif

        {{-- DESCRIPTION --}}
        @if (filled($description))
            <div class="text-sm mt-3">{{ $description }}</div>
        @endif
    </div>
</div>
