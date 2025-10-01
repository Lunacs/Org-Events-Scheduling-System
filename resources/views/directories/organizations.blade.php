<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Student Organizations') }}
        </h2>
    </x-slot>
    <livewire:layout.navigation/>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-mary-card title="Organizations" subtitle="Manage student organizations">
                <div class="flex items-center justify-between mb-4">
                    <x-mary-input placeholder="Search organizations..." icon-left="o-magnifying-glass" class="w-full max-w-md" />
                    <x-mary-button icon="o-plus" class="btn-accent">Add Organization</x-mary-button>
                </div>

                <div class="divide-y divide-base-300">
                    @foreach (['JPIA', 'JPIIE', 'JPCS', 'JPIB', 'JPIE'] as $org)
                        <x-mary-list-item :item="['name' => $org . ' Organization', 'adviser' => 'Adviser: John Doe']" value="name" sub-value="adviser" class="py-4" no-separator>
                            <x-slot:actions>
                                <x-mary-badge value="Active" class="badge-success" />
                                <x-mary-button icon="o-pencil-square" class="btn-ghost btn-sm">Edit</x-mary-button>
                            </x-slot:actions>
                        </x-mary-list-item>
                    @endforeach
                </div>
            </x-mary-card>
        </div>
    </div>
</x-app-layout>
