<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

/*
 * Task 2.4 — Interactive replacement components.
 *
 * Verifies the app-owned x-ui.* controls preserve their Livewire bindings and
 * accessibility affordances after the MaryUI migration:
 *   - button forwards wire:click / disabled and renders loading markup
 *   - input / select / toggle forward wire:model and render @error output
 *   - dropdown trigger exposes aria-expanded
 *
 * Requirements: 3.1, 3.2, 3.3, 3.5, 3.6, 7.6
 */

/**
 * Share an errors bag globally (mirroring ShareErrorsFromSession) so the form
 * components resolve `$errors` when rendered in isolation. Passing an empty bag
 * by default keeps non-error renders working.
 */
function shareErrors(array $errors = []): void
{
    $bag = new ViewErrorBag;
    $bag->put('default', new MessageBag($errors));
    View::share('errors', $bag);
}

beforeEach(fn () => shareErrors());

// --- Button ----------------------------------------------------------------

it('button forwards wire:click to the rendered control', function () {
    $html = Blade::render('<x-ui.button label="Save" wire:click="save" />');

    expect($html)->toContain('wire:click="save"')
        ->and($html)->toContain('Save')
        ->and($html)->toContain('btn');
});

it('button forwards the disabled attribute', function () {
    $html = Blade::render('<x-ui.button label="Save" disabled wire:click="save" />');

    expect($html)->toContain('disabled');
});

it('button renders wire:loading spinner markup when spinner is set', function () {
    $html = Blade::render('<x-ui.button label="Save" spinner wire:click="save" />');

    expect($html)->toContain('wire:loading')
        ->and($html)->toContain('loading loading-spinner')
        ->and($html)->toContain('wire:target="save"');
});

it('button gives leading and trailing icons visible dimensions', function () {
    $leadingIconHtml = Blade::render('<x-ui.button icon="s-eye" tooltip="View details" />');
    $trailingIconHtml = Blade::render('<x-ui.button label="Next" icon-right="s-arrow-right" />');

    expect($leadingIconHtml)
        ->toContain('<svg')
        ->toContain('w-5')
        ->toContain('h-5')
        ->and($trailingIconHtml)
        ->toContain('<svg')
        ->toContain('w-5')
        ->toContain('h-5');
});

// --- Input -----------------------------------------------------------------

it('input forwards wire:model to the native input', function () {
    $html = Blade::render('<x-ui.input label="Email" wire:model="email" />');

    expect($html)->toContain('wire:model="email"')
        ->and($html)->toContain('<input')
        ->and($html)->toContain('Email');
});

it('input renders @error output adjacent to the control', function () {
    shareErrors(['email' => ['The email field is required.']]);

    $html = Blade::render('<x-ui.input label="Email" wire:model="email" />');

    expect($html)->toContain('The email field is required.')
        ->and($html)->toContain('input-error');
});

// --- Select ----------------------------------------------------------------

it('select forwards wire:model and renders generated options', function () {
    $html = Blade::render(
        '<x-ui.select label="Status" wire:model="status" :options="$options" />',
        ['options' => ['Active', 'Inactive']]
    );

    expect($html)->toContain('wire:model="status"')
        ->and($html)->toContain('<select')
        ->and($html)->toContain('Active')
        ->and($html)->toContain('Inactive');
});

it('select renders @error output adjacent to the control', function () {
    shareErrors(['status' => ['The status field is required.']]);

    $html = Blade::render(
        '<x-ui.select label="Status" wire:model="status" :options="$options" />',
        ['options' => ['Active']]
    );

    expect($html)->toContain('The status field is required.')
        ->and($html)->toContain('select-error');
});

// --- Toggle ----------------------------------------------------------------

it('toggle forwards wire:model to the checkbox', function () {
    $html = Blade::render('<x-ui.toggle label="Active" wire:model="active" />');

    expect($html)->toContain('wire:model="active"')
        ->and($html)->toContain('type="checkbox"')
        ->and($html)->toContain('toggle');
});

it('toggle renders @error output adjacent to the control', function () {
    shareErrors(['active' => ['The active field must be accepted.']]);

    $html = Blade::render('<x-ui.toggle label="Active" wire:model="active" />');

    expect($html)->toContain('The active field must be accepted.');
});

// --- Dropdown --------------------------------------------------------------

it('dropdown trigger exposes aria-expanded bound to its open state', function () {
    $html = Blade::render('<x-ui.dropdown label="Menu">Item</x-ui.dropdown>');

    expect($html)->toContain(':aria-expanded="open"')
        ->and($html)->toContain('Menu')
        ->and($html)->toContain('Item');
});
