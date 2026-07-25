{{-- Reusable Ticket Card Skeleton Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @for ($i = 0; $i < 6; $i++)
        <div class="bg-base-100 border border-base-300 rounded-box shadow-sm overflow-hidden animate-pulse">
            <div class="p-6 space-y-4">
                {{-- Header --}}
                <div class="flex items-start justify-between">
                    <div class="flex-1 space-y-2">
                        <div class="h-5 bg-base-200 rounded w-3/4"></div>
                        <div class="h-4 bg-base-200 rounded w-1/2"></div>
                    </div>
                    <div class="h-5 bg-base-200 rounded w-20"></div>
                </div>

                {{-- Description --}}
                <div class="space-y-2">
                    <div class="h-3 bg-base-200 rounded w-full"></div>
                    <div class="h-3 bg-base-200 rounded w-5/6"></div>
                    <div class="h-3 bg-base-200 rounded w-2/3"></div>
                </div>

                {{-- Event Details --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <div class="h-4 w-4 bg-base-200 rounded"></div>
                        <div class="h-4 bg-base-200 rounded w-28"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-4 w-4 bg-base-200 rounded"></div>
                        <div class="h-4 bg-base-200 rounded w-36"></div>
                    </div>
                </div>

                {{-- Spacer --}}
                <div class="min-h-[20px]"></div>

                {{-- Bottom Section --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="h-4 w-4 bg-base-200 rounded"></div>
                        <div class="h-4 bg-base-200 rounded w-24"></div>
                    </div>
                    <div class="h-10 bg-base-200 rounded w-full"></div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-base-200/50 px-6 py-3 border-t border-base-300">
                <div class="flex items-center justify-between">
                    <div class="h-3 bg-base-200 rounded w-20"></div>
                    <div class="h-3 bg-base-200 rounded w-24"></div>
                </div>
            </div>
        </div>
    @endfor
</div>
