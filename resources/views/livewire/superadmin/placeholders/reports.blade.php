{{-- Reports Skeleton --}}
<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6 animate-pulse">
        <div class="h-8 bg-base-300 rounded w-48"></div>
        <div class="flex gap-2">
            <div class="h-10 bg-base-300 rounded w-24"></div>
            <div class="h-10 bg-base-300 rounded w-32"></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 animate-pulse mb-6">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="h-10 bg-base-300 rounded"></div>
                <div class="h-10 bg-base-300 rounded"></div>
                <div class="h-10 bg-base-300 rounded"></div>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 animate-pulse">
        @for ($i = 0; $i < 6; $i++)
            <div class="stats shadow border border-base-200">
                <div class="stat">
                    <div class="stat-figure text-base-300">
                        <div class="w-8 h-8 rounded-full bg-base-300"></div>
                    </div>
                    <div class="h-4 bg-base-300 rounded w-24 mb-2"></div>
                    <div class="h-8 bg-base-300 rounded w-16"></div>
                </div>
            </div>
        @endfor
    </div>

    {{-- Charts Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-pulse">
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <div class="h-6 bg-base-300 rounded w-48 mb-4"></div>
                <div class="h-64 bg-base-300 rounded"></div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <div class="h-6 bg-base-300 rounded w-48 mb-4"></div>
                <div class="h-64 bg-base-300 rounded"></div>
            </div>
        </div>
    </div>
</div>
