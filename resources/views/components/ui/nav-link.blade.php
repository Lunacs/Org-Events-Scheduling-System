@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-primary/30  text-sm font-medium leading-5 text-base-content  focus:outline-none focus:border-primary transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-base-content/60  hover:text-base-content/80 dark:hover:text-base-content/40 hover:border-base-300 dark:hover:border-gray-700 focus:outline-none focus:text-base-content/80 dark:focus:text-base-content/40 focus:border-base-300 dark:focus:border-gray-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
