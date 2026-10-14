{{-- OSA portal layout — thin wrapper around the shared app shell (x-layouts.portal). --}}
<x-layouts.portal portal="osa" label="OSA Admin Portal" :title="$title ?? null" :vite="['resources/js/avatar.js']">
    {{ $slot }}
</x-layouts.portal>
