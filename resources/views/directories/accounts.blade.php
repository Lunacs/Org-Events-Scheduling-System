<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Users / Accounts') }}
        </h2>
    </x-slot>
    <livewire:layout.navigation/>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-mary-card title="Manage Accounts" subtitle="Create, update, and disable user accounts">
                <div class="flex items-center justify-between mb-4">
                    <x-mary-input placeholder="Search users..." icon-left="o-magnifying-glass" class="w-full max-w-md" />
                    <div class="flex gap-2">
                        <x-mary-button icon="o-plus" class="btn-accent">New User</x-mary-button>
                        <x-mary-button icon="o-arrow-down-tray" class="btn-ghost">Export</x-mary-button>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach (['alice@plv.edu.ph', 'bob@plv.edu.ph', 'carol@plv.edu.ph'] as $email)
                        <div class="p-4 rounded border border-base-300 flex items-center justify-between">
                            <div>
                                <div class="font-semibold">{{ Str::before($email, '@') }}</div>
                                <div class="text-sm opacity-70">{{ $email }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-mary-badge value="Active" class="badge-success" />
                                <x-mary-button icon="o-pencil-square" class="btn-ghost btn-sm">Edit</x-mary-button>
                                <x-mary-button icon="o-no-symbol" class="btn-ghost btn-sm">Disable</x-mary-button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-mary-card>
        </div>
    </div>
</x-app-layout>
