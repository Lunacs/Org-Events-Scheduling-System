@props(['ticket'])

<div class="bg-base-100 rounded-box shadow-lg p-6">
    <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
        <x-mary-icon name="o-map-pin" class="w-5 h-5" />
        Schedule & Venue
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-base-content/70">Event Start Date</label>
            <p class="text-base-content">
                {{ $ticket->date_from ? \Carbon\Carbon::parse($ticket->date_from)->format('F d, Y') : 'TBD' }}
            </p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Event End Date</label>
            <p class="text-base-content">
                {{ $ticket->date_to ? \Carbon\Carbon::parse($ticket->date_to)->format('F d, Y') : 'TBD' }}
            </p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Event Start Time</label>
            <p class="text-base-content">
                {{ $ticket->time_from ? \Carbon\Carbon::parse($ticket->time_from)->format('g:i A') : 'TBD' }}
            </p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Event End Time</label>
            <p class="text-base-content">
                {{ $ticket->time_to ? \Carbon\Carbon::parse($ticket->time_to)->format('g:i A') : 'TBD' }}
            </p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Preferred Venue</label>
            <p class="text-base-content">{{ $ticket->venue_requested ?? 'TBD' }}</p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Alternative Venue</label>
            <p class="text-base-content">{{ $ticket->alternate_venue ?? 'None' }}</p>
        </div>
    </div>

    @if ($ticket->special_requirements)
        <div class="mt-4">
            <label class="text-sm font-medium text-base-content/70">Special Requirements</label>
            <p class="text-base-content whitespace-pre-wrap bg-base-200 p-3 rounded">
                {{ $ticket->special_requirements }}</p>
        </div>
    @endif

    {{-- Off-Campus Activity Details --}}
    @if ($ticket->oc_accommodation || $ticket->oc_tsp)
        <div class="mt-4 p-4 bg-warning/10 border-l-4 border-warning rounded">
            <h3 class="font-semibold text-base-content mb-3 flex items-center gap-2">
                <x-mary-icon name="o-map" class="w-4 h-4" />
                Off-Campus Activity Details
            </h3>

            @if ($ticket->oc_accommodation)
                <div class="mb-3">
                    <label class="text-sm font-medium text-base-content/70">Accommodation
                        Provider</label>
                    <p class="text-base-content">{{ $ticket->oc_accommodation }}</p>
                </div>
            @endif

            @if ($ticket->oc_tsp)
                <div class="mb-2">
                    <label class="text-sm font-medium text-base-content/70">Transportation
                        Service
                        Provider</label>
                    <p class="text-base-content">{{ ucfirst($ticket->oc_tsp) }}</p>
                </div>

                @if ($ticket->oc_tsp === 'outsourced')
                    <div class="grid grid-cols-2 gap-3 mt-2">
                        <div>
                            <label class="text-xs font-medium text-base-content/70">Driver
                                Name</label>
                            <p class="text-sm text-base-content">
                                {{ $ticket->oc_driver_name ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-base-content/70">Contact
                                Number</label>
                            <p class="text-sm text-base-content">
                                {{ $ticket->oc_driver_contact_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-base-content/70">Vehicle
                                Type</label>
                            <p class="text-sm text-base-content">
                                {{ $ticket->oc_vehicle_type ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-base-content/70">Plate
                                Number</label>
                            <p class="text-sm text-base-content">
                                {{ $ticket->oc_vehicle_plate_number ?? 'N/A' }}</p>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>
