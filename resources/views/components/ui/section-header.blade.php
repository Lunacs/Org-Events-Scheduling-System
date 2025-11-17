@props(['title', 'subtitle' => null, 'icon' => null, 'breadcrumbs' => []])

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        @if (count($breadcrumbs) > 0)
            <div class="text-sm breadcrumbs mb-2">
                <ul>
                    @foreach ($breadcrumbs as $crumb)
                        <li>
                            @if (isset($crumb['link']))
                                <a href="{{ $crumb['link'] }}" class="text-primary">{{ $crumb['label'] }}</a>
                            @else
                                {{ $crumb['label'] }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h1 class="text-3xl font-bold font-heading text-primary flex items-center gap-2">
            @if ($icon)
                <x-mary-icon :name="$icon" class="w-8 h-8" />
            @endif
            {{ $title }}
        </h1>

        @if ($subtitle)
            <p class="text-sm text-gray-600 mt-1">{{ $subtitle }}</p>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
