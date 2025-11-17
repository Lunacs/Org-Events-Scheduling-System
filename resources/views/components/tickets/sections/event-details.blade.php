@props(['ticket'])

<div class="bg-base-100 rounded-box shadow-lg p-6">
    <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
        <x-mary-icon name="o-calendar-days" class="w-5 h-5" />
        Event Details
    </h2>
    <div class="space-y-4">
        <div>
            <label class="text-sm font-medium text-base-content/70">Event Title</label>
            <p class="text-base-content font-medium text-lg">{{ $ticket->title }}</p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Event Type</label>
            <p class="text-base-content">{{ $ticket->eventType->type_name ?? 'N/A' }}</p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Event Description</label>
            <p class="text-base-content whitespace-pre-wrap">{{ $ticket->description }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium text-base-content/70">PLV Participants</label>
                <p class="text-base-content font-semibold">{{ $ticket->plv_participants ?? 0 }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-base-content/70">External Participants</label>
                <p class="text-base-content font-semibold">{{ $ticket->external_participants ?? 0 }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-base-content/70">Total Expected Participants</label>
                <p class="text-primary font-semibold">
                    {{ $ticket->total_participants ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>
