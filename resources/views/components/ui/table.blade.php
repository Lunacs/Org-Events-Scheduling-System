@props([
    'headers' => [],
    'rows' => null,
    'sortBy' => null,
    'sortProperty' => 'sortBy',
    'perPageValues' => null,
    'emptyText' => 'No records found.',
    'paginate' => true,
])

{{--
    x-ui.table — MaryUI `x-mary-table` replacement (plain HTML + Tailwind/DaisyUI).

    Two rendering modes:
      • Auto mode  — pass :headers (array) + :rows (Collection/Paginator/array). The
                     component renders <thead> from headers and one <tbody> row per
                     record, emitting one <td> per header via data_get($record, key).
      • Custom mode — provide a default slot that owns the row @foreach and wraps cells
                     in <x-ui.table-column> (replaces MaryUI @scope('cell_*')). The
                     component still renders the <table>/<thead>/<tbody> shell.

    Headers accept either a flat list of string labels (static mode) or an array of
    ['key' => , 'label' => , 'sortable' => bool, 'class' => ] maps.

    Sortable headers set the bound Livewire array property (default `sortBy`) via
    $wire.set('sortBy', { column, direction }), reflect state through aria-sort, and
    render a directional x-ui.icon (o-chevron-up / o-chevron-down / o-chevron-up-down).

    Empty state renders the `empty` slot (or the emptyText prop) in a colspan cell.
    Paginated rows render <x-tickets.ticket-pagination :items="$rows" /> beneath the table.
--}}

@php
    $normalizedHeaders = collect($headers)
        ->map(function ($header) {
            if (is_array($header)) {
                return [
                    'key' => $header['key'] ?? null,
                    'label' => $header['label'] ?? ($header['key'] ?? ''),
                    'sortable' => (bool) ($header['sortable'] ?? false),
                    'class' => $header['class'] ?? null,
                ];
            }

            return [
                'key' => null,
                'label' => (string) $header,
                'sortable' => false,
                'class' => null,
            ];
        })
        ->all();

    $colspan = max(count($normalizedHeaders), 1);

    $currentColumn = data_get($sortBy, 'column');
    $currentDirection = data_get($sortBy, 'direction', 'asc');

    $hasRows = !is_null($rows);
    $isEmpty = false;
    if ($hasRows) {
        if (is_countable($rows)) {
            $isEmpty = count($rows) === 0;
        } elseif ($rows instanceof \Illuminate\Support\Collection) {
            $isEmpty = $rows->isEmpty();
        } else {
            $isEmpty = empty($rows);
        }
    }

    $isCustomMode = trim((string) $slot) !== '';

    $isPaginator =
        $rows instanceof \Illuminate\Contracts\Pagination\Paginator ||
        $rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
@endphp

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'table']) }}>
        @if (count($normalizedHeaders) > 0)
            <thead>
                <tr>
                    @foreach ($normalizedHeaders as $header)
                        @php
                            $isActive = $header['key'] !== null && $header['key'] === $currentColumn;
                            $nextDirection = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';
                            $sortIcon = $isActive
                                ? ($currentDirection === 'asc'
                                    ? 'o-chevron-up'
                                    : 'o-chevron-down')
                                : 'o-chevron-up-down';
                        @endphp
                        <th @class([$header['class']])
                            @if ($isActive) aria-sort="{{ $currentDirection === 'asc' ? 'ascending' : 'descending' }}" @endif>
                            @if ($header['sortable'] && $header['key'])
                                <button type="button"
                                    class="inline-flex items-center gap-1 font-semibold cursor-pointer hover:text-primary transition-colors"
                                    x-on:click="$wire.set('{{ $sortProperty }}', { column: '{{ $header['key'] }}', direction: '{{ $nextDirection }}' })"
                                    wire:click="$set('{{ $sortProperty }}', { column: '{{ $header['key'] }}', direction: '{{ $nextDirection }}' })">
                                    <span>{{ $header['label'] }}</span>
                                    <x-ui.icon :name="$sortIcon" class="w-4 h-4 opacity-70" />
                                </button>
                            @else
                                {{ $header['label'] }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody>
            @if ($hasRows && $isEmpty)
                <tr>
                    <td colspan="{{ $colspan }}">
                        @isset($empty)
                            {{ $empty }}
                        @else
                            <div class="text-center text-base-content/60 py-6">{{ $emptyText }}</div>
                        @endisset
                    </td>
                </tr>
            @elseif ($isCustomMode)
                {{ $slot }}
            @else
                @foreach ($rows ?? [] as $record)
                    <tr wire:key="row-{{ data_get($record, 'id', $loop->index) }}">
                        @foreach ($normalizedHeaders as $header)
                            <td @class([$header['class']])>
                                {{ $header['key'] !== null ? data_get($record, $header['key']) : '' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

@if ($paginate && $isPaginator && $rows->hasPages())
    <x-tickets.ticket-pagination :items="$rows" />
@endif
