{{-- Table Skeleton --}}
<div class="animate-pulse space-y-4">
    {{-- Search/Filter Bar --}}
    <div class="flex gap-4 mb-6">
        <div class="h-12 bg-base-200 rounded-lg flex-1"></div>
        <div class="h-12 bg-base-200 rounded-lg w-32"></div>
        <div class="h-12 bg-base-200 rounded-lg w-32"></div>
    </div>

    {{-- Table Header --}}
    <div class="bg-base-100 rounded-box shadow-lg overflow-hidden">
        <div class="h-14 bg-base-200 border-b border-base-300"></div>

        {{-- Table Rows --}}
        @for ($i = 0; $i < 8; $i++)
            <div class="h-16 border-b border-base-300 px-4 flex items-center gap-4">
                <div class="h-4 bg-base-200 rounded w-1/4"></div>
                <div class="h-4 bg-base-200 rounded w-1/6"></div>
                <div class="h-4 bg-base-200 rounded w-1/5"></div>
                <div class="h-4 bg-base-200 rounded w-1/6"></div>
                <div class="h-8 bg-base-200 rounded w-20 ml-auto"></div>
            </div>
        @endfor
    </div>

    {{-- Pagination --}}
    <div class="flex justify-between items-center mt-4">
        <div class="h-10 bg-base-200 rounded w-40"></div>
        <div class="h-10 bg-base-200 rounded w-64"></div>
    </div>
</div>
