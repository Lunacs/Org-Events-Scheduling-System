{{-- Viewer portal layout — thin wrapper around the shared app shell (x-layouts.portal). --}}
<x-layouts.portal portal="viewer" label="Viewer Portal" :title="$title ?? null"
    :vite="['resources/js/avatar.js', 'resources/js/filepond.js']">
    {{ $slot }}
</x-layouts.portal>
