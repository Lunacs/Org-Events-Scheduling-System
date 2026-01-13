{{-- Skeleton Loading State for Notifications --}}
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Header Skeleton --}}
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <div class="skeleton h-8 w-64 mb-2"></div>
                    <div class="skeleton h-4 w-96"></div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="skeleton h-9 w-32"></div>
                    <div class="skeleton h-9 w-32"></div>
                </div>
            </div>
        </div>

        {{-- Stats Skeleton --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @for ($i = 0; $i < 4; $i++)
                <div class="bg-base-100 rounded-box shadow-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="skeleton w-12 h-12 rounded-full"></div>
                        <div class="flex-1">
                            <div class="skeleton h-7 w-12 mb-2"></div>
                            <div class="skeleton h-3 w-16"></div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        {{-- Filters Skeleton --}}
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="skeleton h-12 w-full"></div>
                <div class="skeleton h-12 w-full"></div>
                <div class="skeleton h-12 w-full"></div>
            </div>
        </div>

        {{-- Notifications List Skeleton --}}
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            @include('livewire.placeholders.notification-list')
        </div>

    </div>
</div>
