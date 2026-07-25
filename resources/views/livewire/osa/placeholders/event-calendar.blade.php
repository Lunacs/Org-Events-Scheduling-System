{{-- Event Calendar Skeleton --}}
<div class="p-6">
    {{-- Header --}}
    <div class="animate-pulse mb-6">
        <div class="h-8 bg-base-200 rounded w-1/3 mb-2"></div>
        <div class="h-4 bg-base-200 rounded w-1/2"></div>
    </div>

    {{-- Calendar Controls --}}
    <div class="bg-base-100 border border-base-300 rounded-box shadow-sm p-4 mb-4 animate-pulse">
        <div class="flex justify-between items-center">
            <div class="h-10 bg-base-200 rounded w-32"></div>
            <div class="h-10 bg-base-200 rounded w-40"></div>
            <div class="flex gap-2">
                <div class="h-10 bg-base-200 rounded w-20"></div>
                <div class="h-10 bg-base-200 rounded w-20"></div>
                <div class="h-10 bg-base-200 rounded w-20"></div>
            </div>
        </div>
    </div>

    {{-- Calendar Grid Skeleton --}}
    <div class="bg-base-100 border border-base-300 rounded-box shadow-sm p-6 animate-pulse">
        {{-- Calendar Header (Days of Week) --}}
        <div class="grid grid-cols-7 gap-2 mb-4">
            @for ($i = 0; $i < 7; $i++)
                <div class="h-8 bg-base-200 rounded"></div>
            @endfor
        </div>

        {{-- Calendar Dates Grid --}}
        <div class="grid grid-cols-7 gap-2">
            @for ($i = 0; $i < 35; $i++)
                <div class="h-24 bg-base-200 rounded"></div>
            @endfor
        </div>
    </div>
</div>
