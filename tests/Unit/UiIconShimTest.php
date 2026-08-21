<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| x-ui.icon translation shim (Requirements 2.1, 2.2, 2.4)
|--------------------------------------------------------------------------
|
| The shim replaces MaryUI's <x-mary-icon>. It maps MaryUI-style names
| (o-/s-/m-/c-, fa-*) to Blade Icons names and renders the SVG, forwarding
| pass-through attributes verbatim and falling back to a documented icon
| (with a logged warning) when a name cannot be resolved.
|
| Bound to the Laravel TestCase (not RefreshDatabase) because the shim is a
| pure view component that never touches the database.
|
*/

uses(TestCase::class);

/**
 * Render the shim to HTML for a given MaryUI-style icon name and attributes.
 */
function renderIcon(string $name, string $attributes = ''): string
{
    return Blade::render('<x-ui.icon :name="$name" '.$attributes.' />', ['name' => $name]);
}

/**
 * Extract the first `d="..."` path fragment from an SVG string so tests can
 * assert one icon's glyph appears inside another rendered output.
 */
function extractSvgPath(string $svg): string
{
    preg_match('/d="([^"]+)"/', $svg, $matches);

    return $matches[1] ?? $svg;
}

it('renders the Heroicons outline SVG for an o- prefixed name', function () {
    $html = renderIcon('o-plus');

    // Outline Heroicons are drawn with strokes over a transparent fill.
    expect($html)
        ->toContain('<svg')
        ->toContain('fill="none"')
        ->toContain('stroke="currentColor"');
});

it('renders the Heroicons solid SVG for an s- prefixed name', function () {
    $html = renderIcon('s-bell');

    // Solid Heroicons fill the glyph and carry no stroke attribute.
    expect($html)
        ->toContain('<svg')
        ->toContain('fill="currentColor"')
        ->not->toContain('stroke="currentColor"');
});

it('forwards sizing and color utility classes onto the rendered SVG', function () {
    $html = renderIcon('o-plus', 'class="w-6 h-6 text-red-500"');

    expect($html)
        ->toContain('<svg')
        ->toContain('w-6')
        ->toContain('h-6')
        ->toContain('text-red-500');
});

it('resolves a dynamic variable icon name', function () {
    // A backend-driven name (e.g. $section->type_icon) must resolve like a literal.
    $html = Blade::render('<x-ui.icon :name="$icon" />', ['icon' => 'o-user']);

    $expected = svg('heroicon-o-user')->toHtml();

    expect($html)
        ->toContain('<svg')
        ->toContain(extractSvgPath($expected));
});

it('renders the documented fallback icon and logs a warning for an unknown name', function () {
    Log::spy();

    $unknownName = 'o-this-icon-definitely-does-not-exist';
    $html = renderIcon($unknownName);

    // The fallback is heroicon-o-question-mark-circle rendered in place.
    $fallback = svg('heroicon-o-question-mark-circle')->toHtml();

    expect($html)
        ->toContain('<svg')
        ->toContain(extractSvgPath($fallback));

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(function (string $message, array $context) use ($unknownName): bool {
            return str_contains($message, 'could not resolve')
                && ($context['requested'] ?? null) === $unknownName
                && ($context['fallback'] ?? null) === 'heroicon-o-question-mark-circle';
        });
});
