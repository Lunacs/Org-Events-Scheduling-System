<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>
    <livewire:layout.navigation/>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-mary-card title="Your Profile" subtitle="View and update your information">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-mary-input label="Name" placeholder="Your name" icon-left="o-user" />
                    <x-mary-input label="Email" type="email" placeholder="you@example.com" icon-left="o-envelope" />
                    <x-mary-input label="Phone" placeholder="(+63) 900 000 0000" icon-left="o-phone" />
                    <x-mary-input label="Department" placeholder="e.g. Student Affairs" icon-left="o-building-office" />
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <x-mary-badge value="Verified" class="badge-success" />
                    <x-mary-badge value="2FA Disabled" class="badge-warning" />
                </div>
                <div class="mt-6">
                    <x-mary-button icon="o-check" class="btn-primary">Save Changes</x-mary-button>
                </div>
            </x-mary-card>
        </div>
    </div>
</x-app-layout>
