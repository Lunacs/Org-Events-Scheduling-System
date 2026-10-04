@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary/30  text-start text-base font-medium text-primary  bg-primary/10  focus:outline-none focus:text-primary  focus:bg-primary/10  focus:border-primary  transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-base-content/70  hover:text-base-content dark:hover:text-base-content/40 hover:bg-base-200 dark:hover:bg-gray-700 hover:border-base-300 dark:hover:border-gray-600 focus:outline-none focus:text-base-content dark:focus:text-base-content/40 focus:bg-base-200 dark:focus:bg-gray-700 focus:border-base-300 dark:focus:border-gray-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
