<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            {{ __('Submit Event Ticket (Digital Proposal)') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            {{-- Header --}}
            <section
                class="mb-8 relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-info/10 shadow-sm">
                <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-info/15 blur-2xl"></div>
                <div class="relative p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-heading font-bold text-base-content">Submit Ticket</h1>
                            <p class="text-base-content/70 mt-1">Create a new event proposal request and track its
                                approval progress.</p>
                        </div>
                    </div>
                </div>
            </section>

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-error/30 bg-error/10 p-4">
                    <div class="flex items-start gap-3">
                        <x-mary-icon name="s-exclamation-circle" class="w-5 h-5 text-error mt-0.5" />
                        <div>
                            <p class="font-semibold text-error">Please review the required fields in this step.</p>
                            <ul class="mt-2 list-disc list-inside text-sm text-base-content/80 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <x-mary-form wire:submit="save" wire:ref="submitForm">
                {{-- Progress Indicator --}}
                <div class="mb-6 md:mb-8 overflow-x-auto scroll-smooth snap-x snap-mandatory" id="progress-container">
                    <div class="flex justify-between items-center min-w-max md:min-w-0">
                        @php
                            $stepLabels = [
                                1 => ['full' => 'Organization', 'short' => 'Org'],
                                2 => ['full' => 'Event Details', 'short' => 'Event'],
                                3 => ['full' => 'Schedule', 'short' => 'Schedule'],
                                4 => ['full' => 'Budget', 'short' => 'Budget'],
                                5 => ['full' => 'Attachments', 'short' => 'Files'],
                                6 => ['full' => 'Review', 'short' => 'Review'],
                            ];
                        @endphp
                        @for ($i = 1; $i <= $totalSteps; $i++)
                            <div class="flex flex-col items-center flex-1 min-w-15 md:min-w-0 snap-center"
                                id="step-{{ $i }}">
                                <button type="button" wire:click="goToStep({{ $i }})"
                                    aria-label="Step {{ $i }}: {{ $stepLabels[$i]['full'] }}"
                                    aria-current="{{ $currentStep === $i ? 'step' : 'false' }}"
                                    class="w-9 h-9 md:w-10 md:h-10 rounded-full flex items-center justify-center mb-1.5 text-sm font-medium transition-all duration-200 shrink-0
                                        {{ $currentStep === $i ? 'bg-primary text-primary-content shadow-sm ring-2 ring-primary/30' : '' }}
                                        {{ $currentStep > $i ? 'bg-success text-success-content' : '' }}
                                        {{ $currentStep < $i ? 'bg-base-300 text-base-content/50' : '' }}"
                                    @if ($i > $currentStep + 1) disabled @endif>
                                    @if ($currentStep > $i)
                                        <x-mary-icon name="o-check" class="w-4 h-4" />
                                    @else
                                        {{ $i }}
                                    @endif
                                </button>

                                <span class="text-xs text-center whitespace-nowrap px-1 hidden md:block {{ $currentStep === $i ? 'font-medium text-primary' : ($currentStep > $i ? 'text-base-content/70' : 'text-base-content/40') }}">
                                    {{ $stepLabels[$i]['full'] }}
                                </span>
                                <span class="text-xs text-center whitespace-nowrap px-1 md:hidden {{ $currentStep === $i ? 'font-medium text-primary' : ($currentStep > $i ? 'text-base-content/70' : 'text-base-content/40') }}">
                                    {{ $stepLabels[$i]['short'] }}
                                </span>
                            </div>
                            @if ($i < $totalSteps)
                                <div
                                    class="flex-1 h-0.5 {{ $currentStep > $i ? 'bg-success' : 'bg-base-300' }} mx-1 md:mx-2 min-w-4 rounded-full transition-colors duration-200">
                                </div>
                            @endif
                        @endfor
                    </div>
                </div>


                {{-- Step 1: Organization Information --}}
                @if ($currentStep === 1)
                    {{-- Instructions Card --}}
                    <x-mary-card title="Event Request Guidelines" subtitle="Please read before submitting your proposal"
                        class="mb-1">
                        <x-submit_guidelines :guidelines="$ticketGuidelines" />
                    </x-mary-card>
                    <div class="bg-warning/10 p-4 rounded-lg border-l-4 border-warning mb-4">
                        <x-mary-checkbox label="Check this box if the proposal is amended from a previous submission."
                            wire:model.live="is_amended" />
                    </div>

                    <x-mary-card title="Organization Information" subtitle="Details about your student organization">

                        {{-- Data Privacy Notice --}}
                        <div class="mb-5 rounded-xl border border-info/30 bg-info/5 overflow-hidden">
                            {{-- Header bar --}}
                            <div class="flex items-center gap-3 bg-info/10 px-4 py-3 border-b border-info/20">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-info/20 flex items-center justify-center">
                                    <x-mary-icon name="o-shield-check" class="w-4 h-4 text-info" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-info leading-tight">Data Privacy Notice</p>
                                    <p class="text-xs text-info/70 leading-tight">Republic Act No. 10173 — Data Privacy Act of the Philippines</p>
                                </div>
                            </div>

                            {{-- Body --}}
                            <div class="px-4 py-4 space-y-3 text-xs text-base-content/80 leading-relaxed">
                                <p>
                                    In compliance with <strong class="text-base-content">Republic Act No. 10173</strong>, otherwise known as the
                                    <strong class="text-base-content">Data Privacy Act of 2012</strong>, and its Implementing Rules and
                                    Regulations, <strong class="text-base-content">Pamantasan ng Lungsod ng Valenzuela (PLV)</strong> —
                                    through the Office of Student Affairs (OSA) — collects and processes the personal
                                    information provided in this form.
                                </p>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="rounded-lg bg-base-100/60 border border-base-300/60 p-3">
                                        <p class="font-semibold text-base-content mb-1 flex items-center gap-1.5">
                                            <x-mary-icon name="o-document-text" class="w-3.5 h-3.5 text-info" />
                                            Data Collected
                                        </p>
                                        <ul class="space-y-0.5 text-base-content/70">
                                            <li>• Name &amp; Position of Proponent</li>
                                            <li>• Contact Number &amp; Email Address</li>
                                            <li>• Adviser's Name &amp; Contact Number</li>
                                            <li>• Student Organization Details</li>
                                        </ul>
                                    </div>
                                    <div class="rounded-lg bg-base-100/60 border border-base-300/60 p-3">
                                        <p class="font-semibold text-base-content mb-1 flex items-center gap-1.5">
                                            <x-mary-icon name="o-lock-closed" class="w-3.5 h-3.5 text-info" />
                                            Purpose of Collection
                                        </p>
                                        <ul class="space-y-0.5 text-base-content/70">
                                            <li>• Processing of event proposals</li>
                                            <li>• Communication &amp; coordination</li>
                                            <li>• Approval workflow management</li>
                                            <li>• Institutional compliance &amp; records</li>
                                        </ul>
                                    </div>
                                </div>

                                <p class="text-base-content/60">
                                    Your personal data will be processed solely for the purposes stated above, retained
                                    only for the period necessary, and will not be disclosed to unauthorized third parties
                                    without your consent. As a data subject, you have the right to
                                    <strong class="text-base-content/80">access, correct, erase, object, and lodge a complaint</strong>
                                    regarding the processing of your personal information, in accordance with RA 10173.
                                </p>

                                <p class="text-base-content/50 italic">
                                    By proceeding with this form, you acknowledge that you have read and understood this
                                    Data Privacy Notice and consent to the collection and processing of your personal data.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-mary-input label="Organization Name" wire:model="organizationName" readonly />
                            <x-mary-input label="Organization Course" wire:model="organizationCourse" readonly />
                            <x-mary-input label="Name of Proponent" wire:model="proponentName" readonly />
                            <x-mary-input label="Contact Email" type="email" wire:model="contactEmail" readonly />
                            <x-mary-input label="Proponent Position" wire:model="proponentPosition" readonly />
                            <x-mary-input label="Organization Adviser" wire:model="adviser" readonly />
                            <x-mary-input label="Contact of Proponent" wire:model="proponent_contact"
                                placeholder="0999 999 9999" readonly />
                            <x-mary-input label="Contact of Adviser" wire:model.live.debounce.300ms="adviser_contact"
                                required
                                maxlength="11"
                                inputmode="numeric"
                                placeholder="09XXXXXXXXX"
                                x-on:input="$el.value = $el.value.replace(/\D/g, '').slice(0, 11); $wire.set('adviser_contact', $el.value)" />
                        </div>
                    </x-mary-card>

                @endif

                {{-- Step 2: Event Details --}}
                @if ($currentStep === 2)
                    <x-mary-card title="Event Details" subtitle="Information about your proposed event">
                        <div class="space-y-4">
                            <x-mary-input label="Event Title" wire:model.live.debounce.300ms="eventTitle"
                                placeholder="Enter your event title" required maxlength="255" />
                            <x-mary-textarea label="Event Description" wire:model.live.debounce.300ms="eventDescription"
                                rows="4" required maxlength="2000"
                                x-on:input="if($el.value.length > 2000) $el.value = $el.value.slice(0, 2000)" />
                            <x-mary-select label="Event Type" wire:model.live="eventType" :options="$eventTypes"
                                option-value="event_type_id" option-label="type_name" required />
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <x-mary-input label="PLV Participants" type="number"
                                    wire:model.live="expectedPLVParticipants" required />
                                <x-mary-input label="Non-PLV Participants" type="number"
                                    wire:model.live="expectedNonPLVParticipants" />
                                <x-mary-input label="Total" type="number" value="{{ $this->expectedParticipants }}"
                                    readonly />
                            </div>
                        </div>
                    </x-mary-card>
                @endif

                {{-- Step 3: Schedule & Venue --}}
                @if ($currentStep === 3)
                    <x-mary-card title="Schedule & Venue" subtitle="When and where your event will take place">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-mary-datetime label="Event Start Date" wire:model.live.debounce.300ms="eventStartDate"
                                required
                                min="{{ now()->format('Y-m-d') }}" />

                            <x-mary-datetime label="Event End Date" wire:model.live.debounce.300ms="eventEndDate"
                                required
                                min="{{ $eventStartDate ?: now()->format('Y-m-d') }}" />

                            <x-mary-datetime label="Event Start Time" wire:model.live="eventStartTime" type="time"
                                required />

                            <x-mary-datetime label="Event End Time" wire:model.live="eventEndTime" type="time"
                                required
                                min="{{ ($eventStartDate === $eventEndDate && $eventStartTime) ? $eventStartTime : '' }}" />

                            <div x-data="{
                                preferredVenue: @entangle('preferredVenue').live,
                                showOtherPreferred: @entangle('preferredVenue').live === 'other',
                                venues: @js($venues)
                            }">
                                <x-mary-select label="Preferred Venue" wire:model.live="preferredVenue"
                                    :options="[
                                        ...$venues,
                                        ['venue_id' => 'other', 'venue_name' => 'Others (Please Specify)'],
                                    ]" option-value="venue_id" option-label="venue_name"
                                    placeholder="Select a venue" required />

                                <div x-show="preferredVenue === 'other'" x-collapse x-cloak class="mt-4">
                                    <x-mary-input label="Please specify preferred venue"
                                        wire:model.live="preferredVenueOther" placeholder="Enter venue name"
                                        required maxlength="255" />
                                </div>
                            </div>

                            <div x-data="{
                                alternativeVenue: @entangle('alternativeVenue').live,
                                showOtherAlternative: @entangle('alternativeVenue').live === 'other',
                                venues: @js($venues)
                            }">
                                <x-mary-select label="Alternative Venue" wire:model.live="alternativeVenue"
                                    :options="[
                                        ...$venues,
                                        ['venue_id' => 'other', 'venue_name' => 'Others (Please Specify)'],
                                    ]" option-value="venue_id" option-label="venue_name"
                                    placeholder="Select backup venue" />

                                <div x-show="alternativeVenue === 'other'" x-collapse x-cloak class="mt-4">
                                    <x-mary-input label="Please specify alternative venue"
                                        wire:model.live.blur="alternativeVenueOther" placeholder="Enter venue name"
                                        maxlength="255" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-mary-textarea label="Special Requirements"
                                wire:model.live.debounce.300ms="specialRequirements"
                                placeholder="Audio/visual equipment, seating arrangement, catering, etc."
                                rows="3" maxlength="2000"
                                x-on:input="if($el.value.length > 2000) $el.value = $el.value.slice(0, 2000)" />
                        </div>

                        <div x-data="{ open: @entangle('is_oc') }">
                            <div class="mt-4">
                                <x-mary-checkbox label="Check this box if the activity is off-campus"
                                    wire:model.live="is_oc" />
                            </div>

                            <div x-show="open" x-collapse x-cloak>
                                <div class="mt-4">
                                    <x-mary-textarea label="Accommodation Provider (if any)"
                                        wire:model.live.debounce.300ms="oc_accommodation"
                                        placeholder="Accommodation Provider Details" rows="2" maxlength="2000"
                                        x-on:input="if($el.value.length > 2000) $el.value = $el.value.slice(0, 2000)" />
                                </div>

                                <div x-data="{ tsp: @entangle('oc_tsp') }">
                                    <div class="mb-4">
                                        <x-mary-radio label="Transportation Service Provider" wire:model.live="oc_tsp"
                                            :options="[
                                                ['id' => 'in-house', 'name' => 'In-house'],
                                                ['id' => 'outsourced', 'name' => 'Outsourced'],
                                            ]" inline />
                                    </div>

                                    <div x-show="tsp === 'outsourced'" x-collapse x-cloak>
                                        {{-- Vehicle Cards --}}
                                        <div class="space-y-4">
                                            @foreach ($oc_vehicles as $vIdx => $vehicle)
                                                <div class="border border-base-300 rounded-xl p-4 bg-base-200/40 relative"
                                                    wire:key="vehicle-card-{{ $vIdx }}">

                                                    {{-- Card Header --}}
                                                    <div class="flex items-center justify-between mb-3">
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-7 h-7 rounded-full bg-primary/15 flex items-center justify-center shrink-0">
                                                                <x-mary-icon name="o-truck" class="w-4 h-4 text-primary" />
                                                            </div>
                                                            <span class="text-sm font-semibold text-base-content">
                                                                Vehicle / Driver #{{ $vIdx + 1 }}
                                                            </span>
                                                        </div>

                                                        {{-- Remove button — disabled when only 1 card --}}
                                                        <button
                                                            type="button"
                                                            wire:click="removeVehicle({{ $vIdx }})"
                                                            wire:loading.attr="disabled"
                                                            @disabled(count($oc_vehicles) <= 1)
                                                            class="btn btn-xs btn-ghost text-error gap-1 disabled:opacity-30 disabled:cursor-not-allowed"
                                                            title="{{ count($oc_vehicles) <= 1 ? 'At least one vehicle is required' : 'Remove this vehicle' }}"
                                                        >
                                                            <x-mary-icon name="o-trash" class="w-3.5 h-3.5" />
                                                            Remove
                                                        </button>
                                                    </div>

                                                    {{-- Fields --}}
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <x-mary-input
                                                            label="Name of Driver"
                                                            wire:model.live.debounce.300ms="oc_vehicles.{{ $vIdx }}.driver_name"
                                                            placeholder="Enter the driver name"
                                                            maxlength="60"
                                                            id="oc_driver_name_{{ $vIdx }}" />

                                                        <x-mary-input
                                                            label="Contact Details"
                                                            wire:model.live.debounce.300ms="oc_vehicles.{{ $vIdx }}.contact_number"
                                                            placeholder="Enter the driver's contact"
                                                            maxlength="11"
                                                            inputmode="numeric"
                                                            id="oc_contact_{{ $vIdx }}"
                                                            x-on:input="$el.value = $el.value.replace(/[^0-9\s\-\+\(\)]/g, '').slice(0, 11); $wire.set('oc_vehicles.{{ $vIdx }}.contact_number', $el.value)" />

                                                        <x-mary-input
                                                            label="Type of Transportation"
                                                            wire:model.live.debounce.300ms="oc_vehicles.{{ $vIdx }}.transportation_type"
                                                            placeholder="Enter the type of transportation"
                                                            maxlength="50"
                                                            id="oc_transport_type_{{ $vIdx }}" />

                                                        <x-mary-input
                                                            label="Plate Number"
                                                            wire:model.live.debounce.300ms="oc_vehicles.{{ $vIdx }}.plate_number"
                                                            placeholder="Enter the plate number"
                                                            maxlength="15"
                                                            id="oc_plate_{{ $vIdx }}" />
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Add Vehicle Button --}}
                                        <div class="mt-3">
                                            @if (count($oc_vehicles) < 10)
                                                <button
                                                    type="button"
                                                    wire:click="addVehicle"
                                                    wire:loading.attr="disabled"
                                                    class="btn btn-sm btn-outline btn-primary gap-2 w-full sm:w-auto"
                                                >
                                                    <x-mary-icon name="o-plus-circle" class="w-4 h-4" />
                                                    Add Another Vehicle / Driver
                                                </button>
                                            @else
                                                <div class="flex items-center gap-2 text-xs text-base-content/50 bg-base-200 rounded-lg px-3 py-2 w-full sm:w-auto inline-flex">
                                                    <x-mary-icon name="o-information-circle" class="w-4 h-4 shrink-0" />
                                                    Maximum of 10 vehicles reached.
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </x-mary-card>
                @endif

                {{-- Step 4: Budget Information --}}
                @if ($currentStep === 4)
                    <x-mary-card title="Budget Information" subtitle="Financial details of your event">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-mary-input label="Estimated Total Proposed Budget" type="number" step="0.01"
                                wire:model.live="totalBudget" placeholder="0.00" prefix="₱" />

                            <x-mary-select label="Funding Source" wire:model.live="fundingSource" :options="$fundSources"
                                option-value="source_id" option-label="source_name"
                                placeholder="Select funding source" required />
                        </div>

                        <div class="mt-4">
                            <x-mary-textarea
                                :label="(int) $fundingSource === 1 ? 'Budget Proposal Breakdown' : 'Request Details'"
                                wire:model.live.debounce.300ms="budgetBreakdown"
                                :placeholder="(int) $fundingSource === 1 ? 'Itemized list of expenses or Program Parapernalias Information (venue, equipment, materials, etc.)' : 'Example: 1. 200 packs of foods'"
                                rows="4" maxlength="2000"
                                x-on:input="if($el.value.length > 2000) $el.value = $el.value.slice(0, 2000)" />
                        </div>

                        <div x-data="{ igp: @entangle('igp_requested') }">
                            <div class="mt-4">
                                <x-mary-radio label="IGP (Income Generated Project) Request"
                                    wire:model.live="igp_requested" :options="[
                                        ['id' => 'true', 'name' => 'Requested'],
                                        ['id' => 'false', 'name' => 'Not Requested'],
                                    ]" inline required />
                            </div>

                            <div x-show="igp === 'true'" x-collapse x-cloak>
                                <div class="mt-4">
                                    <x-mary-textarea label="IGP (Income Generated Project) Brief Description"
                                        wire:model.live.debounce.300ms="igp_details"
                                        placeholder="List all descriptions for IGP (Income Generated Project) requested items"
                                        rows="4" maxlength="2000"
                                        x-on:input="if($el.value.length > 2000) $el.value = $el.value.slice(0, 2000)" />
                                </div>
                            </div>
                        </div>
                    </x-mary-card>
                @endif

                {{-- Step 5: Attachments --}}
                @if ($currentStep === 5)
                    {{-- File Attachments --}}
                    <x-mary-card title="Attachments" subtitle="Upload required documents and supporting files">
                        <div class="space-y-4">
                            <x-documentary-requirements :event-type-id="$eventType" />

                            <div class="space-y-2">
                                <x-filepond wire-model="uploadedFileIds"
                                    :max-files="25"
                                    :max-size-mb="10" />
                            </div>
                        </div>
                    </x-mary-card>

                    {{-- Additional Information --}}
                    <x-mary-card title="Additional Information" subtitle="Any other relevant details">
                        <div class="space-y-4">
                            <x-mary-textarea label="Additional Notes" wire:model.live.debounce.300ms="additionalNotes"
                                placeholder="Any other information you'd like to share about your event (security, food service, parking etc.)"
                                rows="3" maxlength="2000"
                                x-on:input="if($el.value.length > 2000) $el.value = $el.value.slice(0, 2000)" />
                        </div>
                    </x-mary-card>
                @endif

                {{-- Step 6: Review & Submit --}}
                @if ($currentStep === 6)
                    <div class="overflow-x-hidden">
                        <x-mary-card title="Review & Submit" subtitle="Please review your information"
                            class="overflow-hidden">
                            {{-- Show summary of all entered data --}}
                            <div class="overflow-hidden">
                                <x-tickets.ticket-preview :ticket="$this->previewTicket" />
                            </div>

                            {{-- Agreement & Submission --}}
                            <div class="mt-6">
                                <x-mary-card title="Terms and Conditions"
                                    subtitle="Please review and agree to the terms" class="overflow-hidden">
                                    <x-terms_and_conditions />

                                    {{-- Agreement Checkbox with Enhanced Styling --}}
                                    <div class="mt-6 pt-4 border-t-2 border-base-content/10">
                                        <div class="bg-success/5 border-l-4 border-success p-4 rounded-r-lg">
                                            <x-mary-checkbox wire:model.live="agreeToTerms" required>
                                                <x-slot:label>
                                                    <span class="text-sm md:text-base font-semibold text-base-content">
                                                        I have read, understood, and agree to all the terms and
                                                        conditions stated above
                                                    </span>
                                                </x-slot:label>
                                            </x-mary-checkbox>
                                            <p class="text-xs text-base-content/60 mt-2 ml-6">
                                                By checking this box, you acknowledge your responsibility to comply
                                                with
                                                all university policies
                                            </p>
                                        </div>
                                    </div>
                                </x-mary-card>
                            </div>
                        </x-mary-card>
                    </div>
                @endif

                {{-- Navigation Buttons --}}
                <div class="flex justify-between items-center pt-6 border-t border-base-300/50 mt-2">
                    @if ($currentStep > 1)
                        <x-mary-button label="Previous" icon="o-arrow-left" wire:click="previousStep"
                            class="btn-ghost data-loading:opacity-50 data-loading:pointer-events-none"
                            spinner :disabled="$isProcessing" />
                    @else
                        <div></div>
                    @endif
                    @if ($currentStep < $totalSteps)
                        <x-mary-button label="Next" icon="o-arrow-right" wire:click="nextStep"
                            class="btn-primary data-loading:opacity-50 data-loading:pointer-events-none"
                            :disabled="$isProcessing" spinner />
                    @else
                        <x-mary-button label="Submit Ticket" icon="s-paper-airplane" wire:click="save"
                            class="btn-primary data-loading:opacity-50 data-loading:pointer-events-none"
                            :disabled="!$agreeToTerms || $isProcessing" spinner />
                    @endif
                </div>
                <x-mary-toast />
            </x-mary-form>
        </div>
    </div>


    @script

        <script>
            // ─── Attachment preview / download ────────────────────────────────────────
            $wire.on('open-attachment-preview', ({ url }) => {
                if (url) window.open(url, '_blank');
            });

            $wire.on('download-attachment', ({ url, filename }) => {
                if (!url) return;
                const link = document.createElement('a');
                link.href = url;
                link.download = filename || 'download';
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // ─── Step changed: scroll to top & keep progress bar in view ─────────
            $wire.on('step-changed', () => {
                setTimeout(() => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    const container = document.getElementById('progress-container');
                    const currentStepElement = document.getElementById(`step-${$wire.currentStep}`);
                    if (container && currentStepElement && window.innerWidth < 768) {
                        currentStepElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest',
                            inline: 'center'
                        });
                    }
                }, 100);
            });

            /**
             * DB-backed draft system.
             * localStorage is used only as a lightweight pointer: { draft_id: N, savedAt: ISO }
             * All real form data lives in the ticket_drafts DB table (server-side).
             */
            const DRAFT_PTR_KEY = 'ticket_draft_ptr_{{ auth()->id() }}';
            let draftTimeout;
            let modalShown = false;
            let isSubmitting = false

            /**
             * save-draft: server emits the draft_id after every field update.
             * We persist just that ID + timestamp to localStorage as a pointer.
             */
            $wire.on('save-draft', (payload) => {
                if (isSubmitting) return;

                clearTimeout(draftTimeout);
                draftTimeout = setTimeout(() => {
                    const draftId = Array.isArray(payload) ? payload[0] : payload;
                    if (!draftId) return;

                    localStorage.setItem(DRAFT_PTR_KEY, JSON.stringify({
                        draft_id: draftId,
                        savedAt: new Date().toISOString()
                    }));

                    // Show auto-save indicator
                    const indicator = document.getElementById('autosave-indicator');
                    if (indicator) {
                        indicator.classList.remove('hidden');
                        setTimeout(() => indicator.classList.add('hidden'), 2000);
                    }
                }, 1500); // 1.5 s debounce
            });

            /**
             * draft-found: fallback for XHR-triggered navigations (e.g. redirect back
             * to submit-ticket). For initial full-page loads and wire:navigate SPA
             * navigations, the eager $wire.draftId check below is more reliable because
             * Livewire public props are guaranteed to be hydrated before script runs,
             * whereas buffered mount() events may replay before $wire.on() listeners
             * are fully active.
             */
            $wire.on('draft-found', (payload) => {
                const data = Array.isArray(payload) ? payload[0] : payload;
                if (!data || modalShown) return;

                // Store/refresh the pointer in localStorage
                localStorage.setItem(DRAFT_PTR_KEY, JSON.stringify({
                    draft_id: data.draftId,
                    savedAt: data.savedAt
                }));

                showResumeModal({ draft_id: data.draftId, savedAt: data.savedAt });
            });

            /**
             * clear-draft: server signals that the draft was discarded.
             */
            $wire.on('clear-draft', () => {
                localStorage.removeItem(DRAFT_PTR_KEY);
                console.log('[draft] pointer cleared');
            });

            /**
             * clear-draft-immediate: ticket was submitted; wipe pointer and lock saves.
             */
            $wire.on('clear-draft-immediate', () => {
                isSubmitting = true;
                clearTimeout(draftTimeout);
                localStorage.removeItem(DRAFT_PTR_KEY);
                console.log('[draft] pointer cleared after submission');

                setTimeout(() => { isSubmitting = false; }, 3000);
            });

            // â”€â”€â”€ Modal helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

            function closeModal() {
                const modal = document.getElementById('draftModal');
                if (modal) {
                    modal.style.opacity = '0';
                    setTimeout(() => {
                        modal.remove();
                        modalShown = false;
                    }, 300);
                }
            }

            function cleanupExistingModal() {
                const m = document.getElementById('draftModal');
                if (m) m.remove();
                modalShown = false;
            }

            function showResumeModal(ptr) {
                // Guard: never build a second modal if one is already up.
                if (modalShown) return;
                modalShown = true;

                const savedDate    = new Date(ptr.savedAt);
                const formattedDate = savedDate.toLocaleString();

                // Build modal DOM
                const modal = document.createElement('div');
                modal.id = 'draftModal';
                modal.className = 'modal modal-open';

                const modalBox = document.createElement('div');
                modalBox.className = 'modal-box relative max-w-md mx-auto';

                const title = document.createElement('h3');
                title.className = 'font-bold text-lg mb-4';
                title.textContent = 'Resume Previous Draft?';

                const text1 = document.createElement('p');
                text1.className = 'mb-2';
                text1.textContent = 'You have an unsaved draft from:';

                const dateText = document.createElement('p');
                dateText.className = 'text-sm text-gray-600 mb-4';
                dateText.textContent = formattedDate;

                const text2 = document.createElement('p');
                text2.className = 'mb-4';
                text2.textContent = 'Would you like to continue where you left off?';

                // Loading spinner (hidden by default)
                const spinnerWrapper = document.createElement('div');
                spinnerWrapper.id = 'draftLoadingSpinner';
                spinnerWrapper.className =
                    'hidden absolute inset-0 bg-base-100 bg-opacity-90 items-center justify-center rounded-lg';

                const spinnerContent = document.createElement('div');
                spinnerContent.className = 'flex flex-col items-center gap-3';

                const spinner = document.createElement('span');
                spinner.className = 'loading loading-spinner loading-lg text-primary';

                const spinnerText = document.createElement('p');
                spinnerText.className = 'text-sm font-medium';
                spinnerText.textContent = 'Loading draftâ€¦';

                spinnerContent.appendChild(spinner);
                spinnerContent.appendChild(spinnerText);
                spinnerWrapper.appendChild(spinnerContent);

                const actionDiv = document.createElement('div');
                actionDiv.className = 'modal-action';

                const discardBtn = document.createElement('button');
                discardBtn.className = 'btn btn-ghost';
                discardBtn.id = 'discardDraftBtn';
                discardBtn.textContent = 'Start Fresh';

                const loadBtn = document.createElement('button');
                loadBtn.className = 'btn btn-primary';
                loadBtn.id = 'loadDraftBtn';
                loadBtn.textContent = 'Resume Draft';

                actionDiv.appendChild(discardBtn);
                actionDiv.appendChild(loadBtn);

                modalBox.appendChild(title);
                modalBox.appendChild(text1);
                modalBox.appendChild(dateText);
                modalBox.appendChild(text2);
                modalBox.appendChild(spinnerWrapper);
                modalBox.appendChild(actionDiv);
                modal.appendChild(modalBox);

                document.body.appendChild(modal);

                // Attach listeners
                requestAnimationFrame(() => {
                    setTimeout(() => attachButtonListeners(ptr), 50);
                });
            }

            function attachButtonListeners(ptr, retryCount = 0) {
                const loadBtn    = document.getElementById('loadDraftBtn');
                const discardBtn = document.getElementById('discardDraftBtn');

                if (!loadBtn || !discardBtn) {
                    if (retryCount < 10) {
                        setTimeout(() => attachButtonListeners(ptr, retryCount + 1), 100);
                    }
                    return;
                }

                loadBtn.addEventListener('click', function handleLoadClick() {
                    loadBtn.removeEventListener('click', handleLoadClick);

                    // Show spinner
                    const spinner = document.getElementById('draftLoadingSpinner');
                    if (spinner) {
                        spinner.classList.remove('hidden');
                        spinner.classList.add('flex');
                        loadBtn.disabled = true;
                        discardBtn.disabled = true;
                    }

                    // Dispatch DB-backed load â€” passes only the draft_id; PHP fetches the rest.
                    Livewire.dispatch('load-draft', { data: { draft_id: ptr.draft_id } });

                    // Wait for draft-loaded confirmation then close
                    const checkLoaded = setInterval(() => {
                        // PHP will have updated currentStep; close once Livewire re-renders
                        clearInterval(checkLoaded);
                        setTimeout(closeModal, 300);
                    }, 200);

                    // Fallback
                    setTimeout(() => {
                        clearInterval(checkLoaded);
                        closeModal();
                    }, 3000);
                });

                discardBtn.addEventListener('click', function handleDiscardClick() {
                    discardBtn.removeEventListener('click', handleDiscardClick);
                    Livewire.dispatch('discard-draft');
                    setTimeout(closeModal, 100);
                });
            }

        
            // We defer by one rAF + 50 ms so this fires AFTER livewire:navigated
            // (which Livewire dispatches at the tail of SPA navigation and could
            // otherwise call cleanupExistingModal before the modal is built).
            if ($wire.draftId && $wire.draftSavedAt && !modalShown) {
                localStorage.setItem(DRAFT_PTR_KEY, JSON.stringify({
                    draft_id: $wire.draftId,
                    savedAt: $wire.draftSavedAt
                }));

                requestAnimationFrame(() => {
                    setTimeout(() => {
                        // Re-check modalShown — draft-found may have already shown it
                        showResumeModal({ draft_id: $wire.draftId, savedAt: $wire.draftSavedAt });
                    }, 80);
                });
            }

            // ——— On navigating (SPA) ─────────────────────────────────────────────────
            // livewire:navigating fires BEFORE the new page loads, so cleanup runs
            // before any new script builds a fresh modal. Using :navigated (after)
            // caused the modal to be destroyed right after it appeared.
            document.addEventListener('livewire:navigating', () => {
                cleanupExistingModal();
                // Server will emit draft-found if applicable â€” no client-side check needed.
            });
        </script>
    @endscript

    {{-- Add auto-save indicator to the form --}}
    <div id="autosave-indicator" class="hidden fixed bottom-4 right-4 bg-base-200 px-4 py-2 rounded-lg shadow-lg">
        <span class="text-sm flex items-center gap-2">
            <x-mary-icon name="o-clock" class="w-4 h-4" />
            Draft saved
        </span>
    </div>
</div>
