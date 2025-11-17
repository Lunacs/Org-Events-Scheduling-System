{{-- Communication Form Skeleton --}}
<div class="p-6">
    {{-- Header --}}
    <div class="animate-pulse mb-6">
        <div class="h-8 bg-base-200 rounded w-1/3 mb-2"></div>
        <div class="h-4 bg-base-200 rounded w-1/2"></div>
    </div>

    {{-- Form Skeleton --}}
    <div class="bg-base-100 rounded-box p-6 shadow-lg animate-pulse space-y-6">
        {{-- Form Fields --}}
        @for ($i = 0; $i < 5; $i++)
            <div>
                <div class="h-4 bg-base-200 rounded w-1/4 mb-2"></div>
                <div class="h-12 bg-base-200 rounded w-full"></div>
            </div>
        @endfor

        {{-- Message Field --}}
        <div>
            <div class="h-4 bg-base-200 rounded w-1/4 mb-2"></div>
            <div class="h-32 bg-base-200 rounded w-full"></div>
        </div>

        {{-- Submit Button --}}
        <div class="h-12 bg-base-200 rounded w-32"></div>
    </div>
</div>
