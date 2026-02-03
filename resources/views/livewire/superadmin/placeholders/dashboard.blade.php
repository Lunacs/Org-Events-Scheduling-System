{{-- Dashboard Skeleton --}}
<div class="p-6 space-y-6">
    {{-- Header Skeleton --}}
    <div class="flex items-center justify-between mb-6 animate-pulse">
        <div class="h-8 bg-base-300 rounded w-48"></div>
        <div class="h-10 bg-base-300 rounded w-32"></div>
    </div>

    {{-- Stats Grid Skeleton --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 animate-pulse">
        @for ($i = 0; $i < 4; $i++)
            <div class="bg-base-100 rounded-2xl p-6 shadow-sm border border-base-200">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="h-4 bg-base-300 rounded w-24 mb-2"></div>
                        <div class="h-8 bg-base-300 rounded w-16"></div>
                    </div>
                    <div class="h-12 w-12 bg-base-300 rounded-full"></div>
                </div>
            </div>
        @endfor
    </div>

    {{-- Main Content Grid Skeleton --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-pulse">
        {{-- Pending Approvals Table --}}
        <div class="col-span-1 lg:col-span-2 bg-base-100 rounded-2xl shadow-sm border border-base-200 overflow-hidden">
            <div class="p-6 border-b border-base-200">
                <div class="h-6 bg-base-300 rounded w-40"></div>
            </div>
            <div class="p-0">
                <div class="h-12 border-b border-base-200 bg-base-50"></div>
                @for ($i = 0; $i < 5; $i++)
                    <div class="h-16 border-b border-base-200 flex items-center px-6 gap-4">
                        <div class="h-4 bg-base-300 rounded w-1/4"></div>
                        <div class="h-4 bg-base-300 rounded w-1/6"></div>
                        <div class="h-4 bg-base-300 rounded w-1/6"></div>
                        <div class="h-6 bg-base-300 rounded w-20 ml-auto"></div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Recent Activity List --}}
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 p-6">
            <div class="h-6 bg-base-300 rounded w-32 mb-6"></div>
            <div class="space-y-6">
                @for ($i = 0; $i < 5; $i++)
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-base-300"></div>
                            @if ($i < 4)
                                <div class="w-px h-full bg-base-200 mt-2"></div>
                            @endif
                        </div>
                        <div class="flex-1 pb-4">
                            <div class="h-4 bg-base-300 rounded w-3/4 mb-2"></div>
                            <div class="h-3 bg-base-300 rounded w-1/2"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>
