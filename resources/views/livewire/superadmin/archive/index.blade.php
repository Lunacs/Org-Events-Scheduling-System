<div>
    <div class="p-6 space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold font-heading">Archive Management</h1>
                <p class="text-sm text-base-content/60 mt-1">View and manage archived events and tickets</p>
            </div>
            <x-mary-button icon="o-arrow-path" class="btn-outline" wire:click="$refresh">
                Refresh
            </x-mary-button>
        </div>

        {{-- Filters --}}
        <x-mary-card>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-mary-input label="Search" wire:model.live.debounce.300ms="search"
                    placeholder="Search by title or ID..." icon="o-magnifying-glass" />

                <x-mary-select label="Type" wire:model.live="typeFilter" :options="[
                    ['id' => 'all', 'name' => 'All Items'],
                    ['id' => 'events', 'name' => 'Events Only'],
                    ['id' => 'tickets', 'name' => 'Tickets Only'],
                ]" option-value="id"
                    option-label="name" />

                <x-mary-input label="From Date" wire:model.live="dateFrom" type="date" />

                <x-mary-input label="To Date" wire:model.live="dateTo" type="date" />
            </div>
        </x-mary-card>

        {{-- Items Table --}}
        <x-mary-card shadow>
            @if ($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                @foreach ($headers as $header)
                                    <th>{{ $header['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr class="hover">
                                    <td>
                                        <x-mary-badge :value="ucfirst($item['type'])" :class="$item['type'] === 'event' ? 'badge-primary' : 'badge-info'" />
                                    </td>
                                    <td class="font-medium">{{ $item['title'] }}</td>
                                    <td>
                                        <span class="text-sm font-mono">{{ $item['identifier'] }}</span>
                                    </td>
                                    <td>{{ $item['organization'] }}</td>
                                    <td>
                                        <x-mary-badge :value="ucfirst($item['status'])" :class="match ($item['status']) {
                                            'cancelled' => 'badge-warning',
                                            'rejected' => 'badge-error',
                                            default => 'badge-ghost',
                                        }" />
                                    </td>
                                    <td>
                                        <div class="text-sm">
                                            <div>{{ $item['archived_at']->format('M d, Y') }}</div>
                                            <div class="text-gray-500">{{ $item['archived_at']->format('g:i A') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <x-mary-button size="xs" icon="o-arrow-uturn-left" class="btn-ghost"
                                                wire:click="openRestoreModal({{ $item['id'] }}, '{{ $item['type'] }}')">
                                                Restore
                                            </x-mary-button>
                                            <x-mary-button size="xs" icon="o-trash" class="btn-ghost text-red-600"
                                                wire:click="openDeleteModal({{ $item['id'] }}, '{{ $item['type'] }}')">
                                                Delete
                                            </x-mary-button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-base-content/20"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium">No archived items found</h3>
                    <p class="mt-2 text-sm text-base-content/60">Try adjusting your filters</p>
                </div>
            @endif
        </x-mary-card>
    </div>

    {{-- Restore Modal --}}
    <x-mary-modal wire:model="showRestoreModal" title="Restore Item">
        <div class="space-y-4">
            <p>Are you sure you want to restore this item? It will be set back to "pending" status.</p>
            <div class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    class="stroke-current shrink-0 w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>The item will need to go through the approval process again.</span>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.showRestoreModal = false" />
            <x-mary-button label="Restore" class="btn-primary" wire:click="restoreItem" spinner="restoreItem" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Delete Confirmation Modal --}}
    <x-mary-modal wire:model="showDeleteModal" title="Permanently Delete">
        <div class="space-y-4">
            <div class="alert alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>This action cannot be undone!</span>
            </div>
            <p class="font-semibold">Are you sure you want to permanently delete this item?</p>
            <p class="text-sm text-base-content/70">All associated data will be removed from the database.</p>
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.showDeleteModal = false" />
            <x-mary-button label="Delete Permanently" class="btn-error" wire:click="permanentlyDelete"
                spinner="permanentlyDelete" />
        </x-slot:actions>
    </x-mary-modal>
</div>
