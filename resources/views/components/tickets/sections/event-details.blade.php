@props(['ticket'])

<div class="overflow-hidden rounded-box border border-base-300 bg-base-100 p-4 sm:p-5">
    <h2 class="text-base font-semibold text-base-content mb-4 flex items-center gap-2">
        <x-ui.icon name="o-calendar-days" class="size-4 shrink-0 text-base-content/60" aria-hidden="true" />
        <span class="break-words">Event Details</span>
    </h2>
    <div class="space-y-4 overflow-hidden">
        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Event Title</p>
            <p class="text-base-content font-medium text-base md:text-lg break-words">{{ $ticket->title }}</p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Event Type</p>
            <p class="text-base-content break-words">{{ $ticket->eventType->type_name ?? 'N/A' }}</p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Event Description</p>
            <p class="text-base-content whitespace-pre-wrap break-words overflow-wrap-anywhere">{{ $ticket->description }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 overflow-hidden">
            <div class="min-w-0">
                <p class="text-[0.8125rem] text-base-content/70">PLV Participants</p>
                <p class="text-base-content font-semibold break-words">{{ $ticket->plv_participants ?? 0 }}</p>
            </div>

            <div class="min-w-0">
                <p class="text-[0.8125rem] text-base-content/70">External Participants</p>
                <p class="text-base-content font-semibold break-words">{{ $ticket->external_participants ?? 0 }}</p>
            </div>

            <div class="min-w-0">
                <p class="text-[0.8125rem] text-base-content/70">Total Expected Participants</p>
                <p class="text-primary font-semibold break-words">
                    {{ $ticket->total_participants ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>
