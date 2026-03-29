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
            $pages->push('...');
        }

        for ($i = $start; $i <= $end; $i++) {
            if ($i > 1 && $i < $lastPage) {
                $pages->push($i);
            }
        }

        if ($end < $lastPage - 1) {
            $pages->push('...');
        }

        $pages->push($lastPage);
    }
@endphp

{{-- Pagination --}}
@if ($paginator !== null && $paginator->hasPages())
    <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 w-full">
        <div class="text-sm text-base-content/70">
            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }}
            of {{ $paginator->total() }} {{ $label }}
        </div>

        <div class="flex items-center space-x-1">
            <x-mary-button icon="s-chevron-left" class="btn-sm btn-ghost" wire:click="previousPage" :disabled="!$paginator->previousPageUrl()" />

            @foreach ($pages as $page)
                @if ($page === '...')
                    <span class="px-2 py-1 text-base-content/40 text-sm">...</span>
                @else
                    <x-mary-button :label="(string) $page"
                        class="btn-sm {{ $page == $currentPage ? 'btn-primary' : 'btn-ghost' }}"
                        wire:click="gotoPage({{ $page }})" />
                @endif
            @endforeach

            <x-mary-button icon="s-chevron-right" class="btn-sm btn-ghost" wire:click="nextPage" :disabled="!$paginator->nextPageUrl()" />
        </div>
    </div>
@endif
