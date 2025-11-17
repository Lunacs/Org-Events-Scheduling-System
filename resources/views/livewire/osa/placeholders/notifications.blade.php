{{-- Notifications Skeleton --}}
<div class="p-6">
    {{-- Header --}}
    <div class="animate-pulse mb-6">
        <div class="h-8 bg-base-200 rounded w-1/4 mb-4"></div>
    </div>

    {{-- Notification List --}}
    <div class="space-y-3 animate-pulse">
        @for ($i = 0; $i < 10; $i++)
            <div class="bg-base-100 rounded-box p-4 shadow-lg">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-base-200 rounded-full"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-base-200 rounded w-3/4"></div>
                        <div class="h-3 bg-base-200 rounded w-1/2"></div>
                        <div class="h-3 bg-base-200 rounded w-1/4"></div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
</div>
