{{-- Stats Grid Skeleton --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    @for ($i = 0; $i < 4; $i++)
        <div class="bg-base-100 rounded-box p-6 shadow-lg animate-pulse">
            <div class="h-4 bg-base-200 rounded w-1/2 mb-4"></div>
            <div class="h-8 bg-base-200 rounded w-3/4"></div>
        </div>
    @endfor
</div>
