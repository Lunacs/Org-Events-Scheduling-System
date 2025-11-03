@props(['ticket'])

{{-- Ticket Details --}}
<div class="lg:col-span-2 space-y-6">
    {{-- Organization Information --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6">
        <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
            <x-mary-icon name="o-building-office-2" class="w-5 h-5" />
            Organization Information
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-base-content/70">Organization Name</label>
                <p class="text-base-content font-medium">
                    {{ $ticket->user?->studentOrganization?->org_name ?? 'No Organization' }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-base-content/70">Organization Course</label>
                <p class="text-base-content">
                    {{ $ticket->user?->studentOrganization?->course?->course_name ?? 'N/A' }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-base-content/70">Name of Proponent</label>
                <p class="text-base-content">{{ $ticket->user?->name }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-base-content/70">Proponent Position</label>
                <p class="text-base-content">{{ $ticket->user?->position?->position_name ?? 'N/A' }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-base-content/70">Contact Email</label>
                <p class="text-base-content">{{ $ticket->user?->email }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-base-content/70">Proponent Contact</label>
                <p class="text-base-content">{{ $ticket->proponent_contact ?? 'N/A' }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-base-content/70">Organization Adviser</label>
                <p class="text-base-content">
                    {{ $ticket->user?->studentOrganization?->adviser_name ?? 'N/A' }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-base-content/70">Adviser Contact</label>
                <p class="text-base-content">{{ $ticket->adviser_contact ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    {{-- Event Details --}}
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

    {{-- Schedule & Venue --}}
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

    {{-- Budget Information --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6">
        <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
            <x-mary-icon name="o-currency-dollar" class="w-5 h-5" />
            Budget Information
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-base-content/70">Estimated Total
                    Budget</label>
                <p class="text-base-content font-semibold text-lg">
                    ₱{{ number_format($ticket->estimated_budget ?? 0, 2) }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-base-content/70">Funding Source</label>
                <p class="text-base-content">{{ $ticket->fundSource->source_name ?? 'N/A' }}</p>
            </div>
        </div>

        @if ($ticket->budget_breakdown)
            <div class="mt-4">
                <label class="text-sm font-medium text-base-content/70">Budget Breakdown</label>
                <p class="text-base-content whitespace-pre-wrap bg-base-200 p-3 rounded">
                    {{ $ticket->budget_breakdown }}</p>
            </div>
        @endif

        {{-- IGP Request --}}
        <div class="mt-4">
            <label class="text-sm font-medium text-base-content/70">IGP Request</label>
            <p class="text-base-content">
                @if ($ticket->igp_requested)
                    <x-mary-badge value="Requested" class="badge-success" />
                    @if ($ticket->igp_details)
                        <span
                            class="block mt-2 bg-base-200 p-3 rounded whitespace-pre-wrap">{{ $ticket->igp_details }}</span>
                    @endif
                @else
                    <x-mary-badge value="Not Requested" class="badge-neutral" />
                @endif
            </p>
        </div>
    </div>

    {{-- Additional Information --}}
    @if ($ticket->additional_notes)
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
                <x-mary-icon name="o-document-text" class="w-5 h-5" />
                Additional Information
            </h2>
            <p class="text-base-content whitespace-pre-wrap bg-base-200 p-4 rounded">
                {{ $ticket->additional_notes }}</p>
        </div>
    @endif

    {{-- Attachments --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6">
        <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
            <x-mary-icon name="o-paper-clip" class="w-5 h-5" />
            Attachments
        </h2>
        @if ($ticket->attachments->count() > 0)
            <div class="space-y-3">
                @foreach ($ticket->attachments as $attachment)
                    <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                        <div class="flex items-center gap-3">
                            <x-mary-icon name="o-document" class="w-5 h-5 text-primary" />
                            <div>
                                <p class="font-medium text-base-content">
                                    {{ $attachment->file_name }}</p>
                                <p class="text-sm text-base-content/70">
                                    {{ $attachment->file_type ?? 'Unknown type' }}</p>
                            </div>
                        </div>
                        @if ($attachment->file_path)
                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                class="btn btn-primary btn-sm">
                                <x-mary-icon name="o-arrow-down-tray" class="w-4 h-4" />
                                Download
                            </a>
                        @else
                            <span class="badge badge-info">Preview</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <x-mary-icon name="o-document-text" class="w-12 h-12 text-base-content/30 mx-auto mb-3" />
                <p class="text-base-content/70">No attachments uploaded</p>
            </div>
        @endif
    </div>
</div>
