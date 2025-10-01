<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Calendar') }}
        </h2>
    </x-slot>
    <livewire:layout.navigation/>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-mary-card title="Events Calendar" subtitle="Overview of scheduled events">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex gap-2">
                        <x-mary-badge value="Today" class="badge-info" />
                        <x-mary-badge value="Upcoming" class="badge-success" />
                        <x-mary-badge value="Past" class="badge-ghost" />
                    </div>
                    <div class="flex gap-2">
                        <x-mary-button icon="o-chevron-left" class="btn-ghost" />
                        <x-mary-button icon="o-chevron-right" class="btn-ghost" />
                        <x-mary-button icon="o-plus" class="btn-accent">New Event</x-mary-button>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-2">
                    @for ($i = 1; $i <= 28; $i++)
                        <div class="p-3 rounded border border-base-300 min-h-24">
                            <div class="text-xs opacity-70">{{ $i }}</div>
                            <div class="mt-2 space-y-1">
                                @if ($i % 5 === 0)
                                    <x-mary-badge value="Meeting" class="badge-primary badge-sm" />
                                @elseif ($i % 7 === 0)
                                    <x-mary-badge value="Orientation" class="badge-secondary badge-sm" />
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </x-mary-card>
        </div>
    </div>
</x-app-layout>
