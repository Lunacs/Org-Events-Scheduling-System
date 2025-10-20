@props(['disabled' => false, 'iconClass' => 'fa-lock', 'label' => null])

@if ($label)
    <div>
        <x-forms.input-label :value="$label" />
        <div x-data="{ show: false }" class="relative mt-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas {{ $iconClass }} text-gray-400"></i>
            </div>

            <input :type="show ? 'text' : 'password'" @disabled($disabled)
                {{ $attributes->merge(['class' => 'block w-full pl-10 pr-10 py-2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm']) }}>

            <button type="button" @click="show = !show"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200"
                tabindex="-1">
                <i class="fas fa-eye" x-show="!show"></i>
                <i class="fas fa-eye-slash" x-show="show" style="display: none;"></i>
            </button>
        </div>
    </div>
@else
    <div x-data="{ show: false }" class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas {{ $iconClass }} text-gray-400"></i>
        </div>

        <input :type="show ? 'text' : 'password'" @disabled($disabled)
            {{ $attributes->merge(['class' => 'block w-full pl-10 pr-10 py-2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm']) }}>

        <button type="button" @click="show = !show"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200"
            tabindex="-1">
            <i class="fas fa-eye" x-show="!show"></i>
            <i class="fas fa-eye-slash" x-show="show" style="display: none;"></i>
        </button>
    </div>
@endif
