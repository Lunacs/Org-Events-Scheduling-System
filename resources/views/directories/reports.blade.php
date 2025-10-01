<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Reports') }}
        </h2>
    </x-slot>
    <livewire:layout.navigation/>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-mary-card title="Generate Reports" subtitle="Download summaries and analytics">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-mary-card title="Monthly Summary">
                        <p class="text-sm opacity-80">Overview of events for the selected month.</p>
                        <div class="mt-4 flex gap-2">
                            <x-mary-button icon="o-document-arrow-down" class="btn-primary btn-sm">PDF</x-mary-button>
                            <x-mary-button icon="o-document-text" class="btn-ghost btn-sm">CSV</x-mary-button>
                        </div>
                    </x-mary-card>

                    <x-mary-card title="Pending vs Approved">
                        <p class="text-sm opacity-80">Comparison of request statuses.</p>
                        <div class="mt-4 flex gap-2">
                            <x-mary-badge value="Pending: 12" class="badge-warning" />
                            <x-mary-badge value="Approved: 34" class="badge-success" />
                        </div>
                    </x-mary-card>

                    <x-mary-card title="Top Organizations">
                        <p class="text-sm opacity-80">Most active organizations this quarter.</p>
                        <ul class="list-disc pl-5 mt-3 text-sm">
                            <li>JPIA</li>
                            <li>JPCS</li>
                            <li>JPIE</li>
                        </ul>
                    </x-mary-card>
                </div>
            </x-mary-card>
        </div>
    </div>
</x-app-layout>
