@props(['ticket'])

<div class="bg-base-100 rounded-box shadow-lg p-4 md:p-6 overflow-hidden">
    <h2 class="text-lg md:text-xl font-bold text-base-content mb-4 flex items-center gap-2">
        <x-ui.icon name="o-calendar-days" class="w-5 h-5 flex-shrink-0" />
        <span class="break-words">Event Details</span>
    </h2>
    <div class="space-y-4 overflow-hidden">
        <div class="min-w-0">
            <label class="text-sm font-medium text-base-content/70">Event Title</label>
            <p class="text-base-content font-medium text-base md:text-lg break-words">{{ $ticket->title }}</p>
        </div>

        <div class="min-w-0">
            <label class="text-sm font-medium text-base-content/70">Event Type</label>
            <p class="text-base-content break-words">{{ $ticket->eventType->type_name ?? 'N/A' }}</p>
        </div>

        <div class="min-w-0">
            <label class="text-sm font-medium text-base-content/70">Event Description</label>
            <p class="text-base-content whitespace-pre-wrap break-words overflow-wrap-anywhere">{{ $ticket->description }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 overflow-hidden">
            <div class="min-w-0">
                <label class="text-sm font-medium text-base-content/70">PLV Participants</label>
                <p class="text-base-content font-semibold break-words">{{ $ticket->plv_participants ?? 0 }}</p>
            </div>

            <div class="min-w-0">
                <label class="text-sm font-medium text-base-content/70">External Participants</label>
                <p class="text-base-content font-semibold break-words">{{ $ticket->external_participants ?? 0 }}</p>
            </div>

            <div class="min-w-0">
                <label class="text-sm font-medium text-base-content/70">Total Expected Participants</label>
                <p class="text-primary font-semibold break-words">
                    {{ $ticket->total_participants ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>
