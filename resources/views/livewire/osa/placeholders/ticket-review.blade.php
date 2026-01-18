{{-- Ticket Review Skeleton --}}
<div class="space-y-6 animate-pulse">
    {{-- Header Card --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-2">
                <div class="h-8 bg-base-200 rounded w-64"></div>
                <div class="h-4 bg-base-200 rounded w-48"></div>
            </div>
            <div class="h-6 bg-base-200 rounded w-24"></div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="h-12 bg-base-200 rounded"></div>
            <div class="h-12 bg-base-200 rounded"></div>
            <div class="h-12 bg-base-200 rounded w-32"></div>
        </div>
    </div>

    {{-- Tickets Grid --}}
    @include('livewire.osa.placeholders.ticket-cards')

    {{-- Pagination --}}
    <div class="flex justify-center">
        <div class="h-10 bg-base-200 rounded w-48"></div>
    </div>
</div>
