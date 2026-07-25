@props(['name' => null])

{{--
    x-ui.icon — MaryUI `x-mary-icon` translation shim.

    Translates MaryUI-style icon names to Blade Icons names and renders inline SVG
    via the blade-ui-kit/blade-icons svg() helper. All pass-through attributes
    (class, aria-*, title, x-show, wire:*, etc.) are forwarded verbatim onto the SVG.

    Prefix mapping:
      o- -> heroicon-o-   (Heroicons outline)
      s- -> heroicon-s-   (Heroicons solid)
      m- -> heroicon-m-   (Heroicons mini)
      c- -> heroicon-c-   (Heroicons micro)
      fa- / fas- / far- / fab- / fal- / fad- -> owenvoke/blade-fontawesome set (default fas-)

    Fallback (Requirement 2.4): when the resolved icon does not exist in any installed
    set, render heroicon-o-question-mark-circle at the same size/color and log a warning.
--}}
@php
    $rawName = (string) ($name ?? '');
    $fallbackIcon = 'heroicon-o-question-mark-circle';

    $resolvedName = (function (string $iconName): string {
        // FontAwesome set prefixes already in Blade Icons form.
        foreach (['fas-', 'far-', 'fab-', 'fal-', 'fad-'] as $faPrefix) {
            if (str_starts_with($iconName, $faPrefix)) {
                return $iconName;
            }
        }

        // Generic FontAwesome-style name (fa-*) defaults to the solid set.
        if (str_starts_with($iconName, 'fa-')) {
            return 'fas-' . substr($iconName, 3);
        }

        // Heroicons MaryUI-style prefixes.
        $prefixMap = [
            'o-' => 'heroicon-o-',
            's-' => 'heroicon-s-',
            'm-' => 'heroicon-m-',
            'c-' => 'heroicon-c-',
        ];

        foreach ($prefixMap as $prefix => $replacement) {
            if (str_starts_with($iconName, $prefix)) {
                return $replacement . substr($iconName, strlen($prefix));
            }
        }

        // Already fully-qualified (e.g. heroicon-o-*) or unknown; return as-is.
        return $iconName;
    })($rawName);

    $iconFactory = app(\BladeUI\Icons\Factory::class);

    try {
        $iconFactory->svg($resolvedName);
    } catch (\BladeUI\Icons\Exceptions\SvgNotFound $exception) {
        \Illuminate\Support\Facades\Log::warning(
            "x-ui.icon could not resolve icon name '{$rawName}' (resolved to '{$resolvedName}'); rendering fallback '{$fallbackIcon}'.",
            [
                'requested' => $rawName,
                'resolved' => $resolvedName,
                'fallback' => $fallbackIcon,
            ],
        );
        $resolvedName = $fallbackIcon;
    }
@endphp

{{ svg($resolvedName, '', $attributes->getAttributes()) }}
