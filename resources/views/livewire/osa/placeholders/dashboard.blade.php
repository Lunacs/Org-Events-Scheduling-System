{{-- Dashboard Skeleton --}}
<div class="p-6 space-y-6">
    {{-- Page Header --}}
    <div class="animate-pulse mb-6">
        <div class="h-8 bg-base-200 rounded w-1/4 mb-2"></div>
        <div class="h-4 bg-base-200 rounded w-1/3"></div>
    </div>

    {{-- Stats Grid --}}
    @include('livewire.osa.placeholders.stats-grid')

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6 animate-pulse">
        {{-- Chart 1 --}}
        <div class="bg-base-100 rounded-box p-6 shadow-lg">
            <div class="h-6 bg-base-200 rounded w-1/3 mb-4"></div>
            <div class="h-64 bg-base-200 rounded"></div>
        </div>

        {{-- Chart 2 --}}
        <div class="bg-base-100 rounded-box p-6 shadow-lg">
            <div class="h-6 bg-base-200 rounded w-1/3 mb-4"></div>
            <div class="h-64 bg-base-200 rounded"></div>
        </div>
    </div>

    {{-- Recent Activities Table --}}
    <div class="bg-base-100 rounded-box p-6 shadow-lg animate-pulse">
        <div class="h-6 bg-base-200 rounded w-1/4 mb-4"></div>
        <div class="space-y-3">
            @for ($i = 0; $i < 5; $i++)
                <div class="h-16 bg-base-200 rounded"></div>
            @endfor
        </div>
    </div>
</div>
