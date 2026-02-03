{{-- Logs Skeleton --}}
<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between animate-pulse">
        <div class="h-8 bg-base-300 rounded w-48"></div>
        <div class="flex gap-2">
            <div class="h-10 bg-base-300 rounded w-24"></div>
            <div class="h-10 bg-base-300 rounded w-32"></div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm border border-base-200 animate-pulse">
        <div class="card-body p-0">
            {{-- Unified Filter Bar --}}
            <div class="p-4 border-b border-base-200 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="h-12 bg-base-300 rounded-lg"></div>
                <div class="h-12 bg-base-300 rounded-lg"></div>
                <div class="h-12 bg-base-300 rounded-lg"></div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <div class="h-12 border-b border-base-200 bg-base-50"></div>
                @for ($i = 0; $i < 10; $i++)
                    <div class="h-16 border-b border-base-200 px-6 flex items-center gap-4">
                        <div class="h-10 w-32 bg-base-300 rounded"></div>
                        <div class="h-10 w-40 bg-base-300 rounded"></div>
                        <div class="h-6 w-20 bg-base-300 rounded-full"></div>
                        <div class="h-4 w-64 bg-base-300 rounded"></div>
                    </div>
                @endfor
            </div>

            {{-- Pagination --}}
            <div class="p-4 border-t border-base-200 flex justify-between items-center">
                <div class="h-8 w-48 bg-base-300 rounded"></div>
                <div class="h-8 w-64 bg-base-300 rounded"></div>
            </div>
        </div>
    </div>
</div>
