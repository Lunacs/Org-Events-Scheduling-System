@props(['ticket'])

<div class="overflow-hidden rounded-box border border-base-300 bg-base-100 p-4 sm:p-5">
    <h2 class="text-base font-semibold text-base-content mb-4 flex items-center gap-2">
        <x-ui.icon name="o-map-pin" class="size-4 shrink-0 text-base-content/60" aria-hidden="true" />
        <span class="break-words">Schedule & Venue</span>
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 overflow-hidden">
        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Event Start Date</p>
            <p class="text-base-content break-words">
                {{ $ticket->date_from ? \Carbon\Carbon::parse($ticket->date_from)->format('F d, Y') : 'TBD' }}
            </p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Event End Date</p>
            <p class="text-base-content break-words">
                {{ $ticket->date_to ? \Carbon\Carbon::parse($ticket->date_to)->format('F d, Y') : 'TBD' }}
            </p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Event Start Time</p>
            <p class="text-base-content break-words">
                {{ $ticket->time_from ? \Carbon\Carbon::parse($ticket->time_from)->format('g:i A') : 'TBD' }}
            </p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Event End Time</p>
            <p class="text-base-content break-words">
                {{ $ticket->time_to ? \Carbon\Carbon::parse($ticket->time_to)->format('g:i A') : 'TBD' }}
            </p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Preferred Venue</p>
            <p class="text-base-content break-words">{{ $ticket->venue_display_name ?? 'TBD' }}</p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Alternative Venue</p>
            <p class="text-base-content break-words">{{ $ticket->alternate_venue_display_name ?? 'None' }}</p>
        </div>
    </div>

    @if ($ticket->special_requirements)
        <div class="mt-4 min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Special Requirements</p>
            <p class="text-base-content whitespace-pre-wrap break-words overflow-wrap-anywhere bg-base-200 p-3 rounded">
                {{ $ticket->special_requirements }}</p>
        </div>
    @endif

    {{-- Off-Campus Activity Details --}}
    @if ($ticket->oc_accommodation || $ticket->oc_tsp)
        <div class="mt-4 p-3 md:p-4 rounded-field border border-warning/30 bg-warning/[0.06] overflow-hidden">
            <h3 class="font-semibold text-base-content mb-3 flex items-center gap-2">
                <x-ui.icon name="o-map" class="w-4 h-4 flex-shrink-0" />
                <span class="break-words">Off-Campus Activity Details</span>
            </h3>

            @if ($ticket->oc_accommodation)
                <div class="mb-3 min-w-0">
                    <p class="text-[0.8125rem] text-base-content/70">Accommodation
                        Provider</p>
                    <p class="text-base-content break-words">{{ $ticket->oc_accommodation }}</p>
                </div>
            @endif

            @if ($ticket->oc_tsp)
                <div class="mb-2 min-w-0">
                    <p class="text-[0.8125rem] text-base-content/70">Transportation
                        Service
                        Provider</p>
                    <p class="text-base-content break-words">{{ ucfirst($ticket->oc_tsp) }}</p>
                </div>

                @if ($ticket->oc_tsp === 'outsourced')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2 overflow-hidden">
                        <div class="min-w-0">
                            <p class="text-xs text-base-content/70">Driver
                                Name</p>
                            <p class="text-sm text-base-content break-words">
                                {{ $ticket->oc_driver_name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-base-content/70">Contact
                                Number</p>
                            <p class="text-sm text-base-content break-words">
                                {{ $ticket->oc_driver_contact_number ?? 'N/A' }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-base-content/70">Transportation Type</p>
                            <p class="text-sm text-base-content break-words">
                                {{ $ticket->oc_transportation_type ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-base-content/70">Plate
                                Number</p>
                            <p class="text-sm text-base-content break-words">
                                {{ $ticket->oc_vehicle_plate_number ?? 'N/A' }}</p>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>
