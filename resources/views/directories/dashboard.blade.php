<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <livewire:layout.navigation/>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 dark:text-gray-100 base text-info font-heading font-normal">
                    {{ __("You're logged in!") }}
                </div>
            </div>

            <button class="btn btn-accent rounded-field font-body">
                <i class="fas fa-plus"></i> Add niggers
            </button>
        </div>

    </div>

</x-app-layout>
