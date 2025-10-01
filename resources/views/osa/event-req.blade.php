<x-app-layout>
    <livewire:layout.navigation/>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 dark:text-gray-100 base text-info font-heading font-normal">
                    {{ __("You're logged in!") }}
                </div>
            </div>

            <x-mary-button icon="o-plus" class="btn-accent">Add Request</x-mary-button>
        </div>

    </div>

</x-app-layout>
