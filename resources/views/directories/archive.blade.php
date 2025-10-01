<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Archive') }}
        </h2>
    </x-slot>
    <livewire:layout.navigation/>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-mary-card title="Archived Requests" subtitle="Past events and closed requests">
                <div class="flex items-center justify-between mb-4">
                    <x-mary-input placeholder="Search archives..." icon-left="o-magnifying-glass" class="w-full max-w-md" />
                    <x-mary-button icon="o-arrow-down-tray" class="btn-ghost">Export</x-mary-button>
                </div>

                <div class="space-y-3">
                    @foreach (range(1,5) as $i)
                        <div class="p-4 rounded border border-base-300 flex items-center justify-between">
                            <div>
                                <div class="font-semibold">Event #{{ $i }} — Sample Archived Event</div>
                                <div class="text-sm opacity-70">Archived on 2025-0{{ $i }}-15</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-mary-badge value="Closed" class="badge-ghost" />
                                <x-mary-badge value="Approved" class="badge-success" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-mary-card>
        </div>
    </div>
</x-app-layout>
