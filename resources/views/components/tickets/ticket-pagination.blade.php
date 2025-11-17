@props(['tickets' => null, 'notifications' => null])

{{-- Pagination --}}
@if ($tickets !== null)
    <div class="mt-6 flex justify-between items-center">
        <div class="text-sm text-gray-600">
            Showing {{ $tickets->firstItem() ?? 0 }} to {{ $tickets->lastItem() ?? 0 }}
            of {{ $tickets->total() }} tickets
        </div>

        <div class="flex space-x-2">
            <x-mary-button icon="s-chevron-left" class="btn-sm btn-ghost" wire:click="previousPage" :disabled="!$tickets->previousPageUrl()" />

            @foreach ($tickets->getUrlRange(1, $tickets->lastPage()) as $page => $url)
                <x-mary-button :label="$page"
                    class="btn-sm {{ $page == $tickets->currentPage() ? 'btn-primary' : 'btn-ghost' }}"
                    wire:click="gotoPage({{ $page }})" />
            @endforeach

            <x-mary-button icon="s-chevron-right" class="btn-sm btn-ghost" wire:click="nextPage" :disabled="!$tickets->nextPageUrl()" />
        </div>
    </div>
@elseif ($notifications !== null)
    <div class="mt-6 flex justify-between items-center">
        <div class="text-sm text-gray-600">
            Showing {{ $notifications->lastItem() ?? 0 }}
            of {{ $notifications->total() }} notifications
        </div>

        <div class="flex space-x-2">
            <x-mary-button icon="s-chevron-left" class="btn-sm btn-ghost" wire:click="previousPage" :disabled="!$notifications->previousPageUrl()" />

            @foreach ($notifications->getUrlRange(1, $notifications->lastPage()) as $page => $url)
                <x-mary-button :label="$page"
                    class="btn-sm {{ $page == $notifications->currentPage() ? 'btn-primary' : 'btn-ghost' }}"
                    wire:click="gotoPage({{ $page }})" />
            @endforeach

            <x-mary-button icon="s-chevron-right" class="btn-sm btn-ghost" wire:click="nextPage" :disabled="!$notifications->nextPageUrl()" />
        </div>
    </div>
@endif
