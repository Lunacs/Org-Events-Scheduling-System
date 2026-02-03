{{-- Users Skeleton --}}
<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between animate-pulse">
        <div class="h-8 bg-base-300 rounded w-48"></div>
        <div class="h-10 bg-base-300 rounded w-32"></div>
    </div>

    {{-- Filter Card --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 animate-pulse mb-4">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="h-12 bg-base-300 rounded-lg"></div>
                <div class="h-12 bg-base-300 rounded-lg"></div>
                <div class="h-12 bg-base-300 rounded-lg"></div>
            </div>
        </div>
    </div>

    {{-- Users Table Card --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 animate-pulse">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                {{-- Table Header --}}
                <div class="h-12 border-b border-base-200 bg-base-50"></div>

                {{-- Table Rows --}}
                @for ($i = 0; $i < 8; $i++)
                    <div class="h-16 border-b border-base-200 px-6 flex items-center gap-4">
                        <div class="h-4 w-32 bg-base-300 rounded"></div> {{-- Name --}}
                        <div class="h-4 w-48 bg-base-300 rounded"></div> {{-- Email --}}
                        <div class="h-6 w-24 bg-base-300 rounded-full"></div> {{-- Role --}}
                        <div class="h-6 w-20 bg-base-300 rounded-full"></div> {{-- Status --}}
                        <div class="h-4 w-32 bg-base-300 rounded"></div> {{-- Org --}}
                        <div class="h-8 w-16 bg-base-300 rounded ml-auto"></div> {{-- Actions --}}
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
