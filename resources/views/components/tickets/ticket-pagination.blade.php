@props([
    'tickets' => null,
    'notifications' => null,
    'items' => null,
    'label' => null,
])

@php
    // Determine which paginator to use
    $paginator = $items ?? ($tickets ?? $notifications);

    // Determine the label to display
    if ($label === null) {
        if ($items !== null) {
            $label = 'items';
        } elseif ($tickets !== null) {
            $label = 'tickets';
        } elseif ($notifications !== null) {
            $label = 'notifications';
        } else {
            $label = 'items';
        }
    }

    // Smart pagination: show ellipsis when there are many pages
    $currentPage = $paginator?->currentPage() ?? 1;
    $lastPage = $paginator?->lastPage() ?? 1;
    $onEachSide = 1;

    $pages = collect();
    if ($lastPage <= 7) {
        $pages = collect(range(1, $lastPage));
    } else {
        $pages->push(1);

        $start = max(2, $currentPage - $onEachSide);
        $end = min($lastPage - 1, $currentPage + $onEachSide);

        if ($currentPage <= 3) {
            $end = 4;
        }

        if ($currentPage >= $lastPage - 2) {
            $start = $lastPage - 3;
        }

        if ($start > 2) {
            $pages->push('…');
        }

        for ($i = $start; $i <= $end; $i++) {
            if ($i > 1 && $i < $lastPage) {
                $pages->push($i);
            }
        }

        if ($end < $lastPage - 1) {
            $pages->push('…');
        }

        $pages->push($lastPage);
    }
@endphp

{{-- Pagination --}}
@if ($paginator !== null && $paginator->hasPages())
    <nav aria-label="Pagination" class="mt-6 flex w-full flex-col items-center justify-between gap-4 sm:flex-row">
        <p class="text-sm tabular-nums text-base-content/70">
            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }}
            of {{ $paginator->total() }} {{ $label }}
        </p>

        <div class="flex items-center space-x-1">
            <x-ui.button icon="s-chevron-left" class="btn-sm btn-ghost btn-square" aria-label="Previous page" wire:click.preserve-scroll="previousPage" :disabled="!$paginator->previousPageUrl()" />

            @foreach ($pages as $page)
                @if ($page === '…')
                    <span class="px-2 py-1 text-sm text-base-content/60" aria-hidden="true">…</span>
                @else
                    <x-ui.button :label="(string) $page" aria-label="Page {{ $page }}"
                        :aria-current="$page == $currentPage ? 'page' : null"
                        class="btn-sm min-w-8 tabular-nums {{ $page == $currentPage ? 'btn-primary' : 'btn-ghost' }}"
                        wire:click.preserve-scroll="gotoPage({{ $page }})" />
                @endif
            @endforeach

            <x-ui.button icon="s-chevron-right" class="btn-sm btn-ghost btn-square" aria-label="Next page" wire:click.preserve-scroll="nextPage" :disabled="!$paginator->nextPageUrl()" />
        </div>
    </nav>
@endif
