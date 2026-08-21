<?php

use Illuminate\Support\Facades\Blade;

/*
|--------------------------------------------------------------------------
| x-ui.table + presentational component rendering (task 3.4)
|--------------------------------------------------------------------------
|
| Example-based rendering tests for the data + presentational replacement
| components built in task 3. They assert the plain-HTML/Tailwind output the
| components emit so the migration preserves structure, content, and the
| DaisyUI token classes the existing theme styles against.
|
*/

/**
 * Render a Blade string in the full app context and return the raw HTML.
 *
 * @param  array<string, mixed>  $data
 */
function renderUi(string $template, array $data = []): string
{
    return Blade::render($template, $data);
}

// ---------------------------------------------------------------------------
// x-ui.table — Requirements 4.1, 4.2, 4.3, 4.4, 4.6
// ---------------------------------------------------------------------------

it('renders table headers in the declared order', function () {
    $html = renderUi(<<<'BLADE'
        <x-ui.table :headers="[
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'role', 'label' => 'Role'],
        ]" :rows="collect()" />
    BLADE);

    expect($html)->toContain('<table')
        ->and($html)->toContain('Name')
        ->and($html)->toContain('Email')
        ->and($html)->toContain('Role');

    // Headers must appear in the same order they were declared.
    expect(mb_strpos($html, 'Name'))->toBeLessThan(mb_strpos($html, 'Email'));
    expect(mb_strpos($html, 'Email'))->toBeLessThan(mb_strpos($html, 'Role'));
});

it('renders one tbody row per record in auto mode', function () {
    $rows = collect([
        ['name' => 'Alice', 'email' => 'alice@example.test'],
        ['name' => 'Bob', 'email' => 'bob@example.test'],
        ['name' => 'Carol', 'email' => 'carol@example.test'],
    ]);

    $html = renderUi(<<<'BLADE'
        <x-ui.table :headers="[
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email'],
        ]" :rows="$rows" />
    BLADE, ['rows' => $rows]);

    // One <tr> per record inside <tbody> (data cells rendered via data_get()).
    expect(substr_count($html, '<tr wire:key="row-'))->toBe(3)
        ->and($html)->toContain('Alice')
        ->and($html)->toContain('alice@example.test')
        ->and($html)->toContain('Bob')
        ->and($html)->toContain('Carol');
});

it('renders custom cell-slot content in custom mode', function () {
    $rows = collect([
        ['name' => 'Alice', 'status' => 'active'],
    ]);

    $html = renderUi(<<<'BLADE'
        <x-ui.table :headers="[
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'status', 'label' => 'Status'],
        ]" :rows="$rows">
            @foreach ($rows as $row)
                <tr wire:key="custom-{{ $loop->index }}">
                    <x-ui.table-column>{{ $row['name'] }}</x-ui.table-column>
                    <x-ui.table-column>
                        <span class="badge badge-success">{{ ucfirst($row['status']) }}</span>
                    </x-ui.table-column>
                </tr>
            @endforeach
        </x-ui.table>
    BLADE, ['rows' => $rows]);

    // The consumer-owned row markup (replacing @scope('cell_*')) is emitted verbatim.
    expect($html)->toContain('wire:key="custom-0"')
        ->and($html)->toContain('<span class="badge badge-success">Active</span>')
        ->and($html)->toContain('Alice');
});

it('renders sort trigger markup with $wire.set and aria-sort on sortable headers', function () {
    $html = renderUi(<<<'BLADE'
        <x-ui.table :headers="[
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ]" :rows="collect()" :sort-by="['column' => 'name', 'direction' => 'asc']" />
    BLADE);

    // Sortable header drives the bound Livewire property via $wire.set and
    // toggles to the next direction (asc -> desc), and exposes aria-sort state.
    expect($html)->toContain("\$wire.set('sortBy', { column: 'name', direction: 'desc' })")
        ->and($html)->toContain('aria-sort="ascending"')
        ->and($html)->toContain('cursor-pointer');
});

it('renders the empty state when there are no records', function () {
    $html = renderUi(<<<'BLADE'
        <x-ui.table :headers="[
            ['key' => 'name', 'label' => 'Name'],
        ]" :rows="collect()" empty-text="Nothing to show here." />
    BLADE);

    expect($html)->toContain('Nothing to show here.')
        ->and($html)->toContain('colspan="1"')
        ->and($html)->not->toContain('wire:key="row-');
});

it('renders a provided empty slot when there are no records', function () {
    $html = renderUi(<<<'BLADE'
        <x-ui.table :headers="[['key' => 'name', 'label' => 'Name']]" :rows="collect()">
            <x-slot:empty>
                <span class="text-warning">No results found.</span>
            </x-slot:empty>
        </x-ui.table>
    BLADE);

    expect($html)->toContain('No results found.')
        ->and($html)->toContain('text-warning');
});

// ---------------------------------------------------------------------------
// Presentational components — Requirement 1.1
// ---------------------------------------------------------------------------

it('renders x-ui.card with title, subtitle and body content plus DaisyUI classes', function () {
    $html = renderUi(<<<'BLADE'
        <x-ui.card title="Monthly Report" subtitle="September" class="lg:col-span-2" shadow>
            Body content here.
        </x-ui.card>
    BLADE);

    expect($html)->toContain('card bg-base-100')
        ->and($html)->toContain('shadow-xs')
        ->and($html)->toContain('lg:col-span-2')
        ->and($html)->toContain('Monthly Report')
        ->and($html)->toContain('September')
        ->and($html)->toContain('Body content here.');
});

it('renders x-ui.badge with the badge class and its value', function () {
    $html = renderUi('<x-ui.badge value="Approved" class="badge-success" />');

    expect($html)->toContain('badge')
        ->and($html)->toContain('badge-success')
        ->and($html)->toContain('Approved');
});

it('renders x-ui.stat with title, value and description', function () {
    $html = renderUi(<<<'BLADE'
        <x-ui.stat title="Total Events" value="42" description="This month" icon="o-calendar" />
    BLADE);

    expect($html)->toContain('bg-base-100')
        ->and($html)->toContain('Total Events')
        ->and($html)->toContain('42')
        ->and($html)->toContain('This month')
        ->and($html)->toContain('stat-desc');
});

it('renders x-ui.alert with role=alert, the alert class and its content', function () {
    $html = renderUi(<<<'BLADE'
        <x-ui.alert title="Saved successfully" icon="o-check-circle" class="alert-success" />
    BLADE);

    expect($html)->toContain('role="alert"')
        ->and($html)->toContain('alert')
        ->and($html)->toContain('alert-success')
        ->and($html)->toContain('Saved successfully');
});
