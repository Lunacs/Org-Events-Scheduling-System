@props(['disabled' => false, 'label' => null, 'value' => null, 'iconClass' => 'fa-envelope'])

@if ($label)
    <div>
        <x-forms.input-label :value="$label" />
        <div class="relative mt-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas {{ $iconClass }} text-gray-400"></i>
            </div>
            <input type="email" @disabled($disabled)
                {{ $attributes->merge(['class' => 'block w-full pl-10 pr-3 py-2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm']) }}>
        </div>
    </div>
@else
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas {{ $iconClass }} text-gray-400"></i>
        </div>
        <input type="email" @disabled($disabled)
            {{ $attributes->merge(['class' => 'block w-full pl-10 pr-3 py-2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm']) }}>
    </div>
@endif
