<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Submit Event Ticket (Digital Proposal)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <x-mary-form wire:submit="save">
                {{-- Progress Indicator --}}
                <div class="mb-8">
                    <div class="flex justify-between items-center">
                        @for($i = 1; $i <= $totalSteps; $i++)
                            <div class="flex flex-col items-center flex-1">
                                <button
                                    type="button"
                                    wire:click="goToStep({{ $i }})"
                                    class="w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-colors
                            {{ $currentStep === $i ? 'bg-primary text-white' : '' }}
                            {{ $currentStep > $i ? 'bg-success text-white' : '' }}
                            {{ $currentStep < $i ? 'bg-base-300 text-base-content' : '' }}">
                                    {{ $currentStep > $i ? '✓' : $i }}
                                </button>
                                <span class="text-xs text-center">
                        @switch($i)
                                        @case(1) Organization @break
                                        @case(2) Event Details @break
                                        @case(3) Schedule @break
                                        @case(4) Budget @break
                                        @case(5) Attachments @break
                                        @case(6) Review @break
                                    @endswitch
                    </span>
                            </div>
                            @if($i < $totalSteps)
                                <div
                                    class="flex-1 h-1 {{ $currentStep > $i ? 'bg-success' : 'bg-base-300' }} mx-2"></div>
                            @endif
                        @endfor
                    </div>
                </div>

                {{-- Step 1: Organization Information --}}
                @if($currentStep === 1)
                    {{-- Instructions Card --}}
                    <x-mary-card title="Event Request Guidelines" subtitle="Please read before submitting your proposal"
                                 class="mb-6">
                        <div class="bg-info/10 p-4 rounded-lg border-l-4 border-info mb-4">
                            <div class="flex items-start space-x-2">
                                <x-mary-icon name="s-information-circle" class="w-5 h-5 text-info mt-0.5"/>
                                <div class="text-sm">
                                    <p class="font-medium mb-2">Important Guidelines:</p>
                                    <ul class="list-disc list-inside space-y-1 text-gray-600">
                                        <li>Submit your request at least 14 days before your event date</li>
                                        <li>All required fields must be completed</li>
                                        <li>Upload all necessary attachments (permit forms, venue reservations, etc.)</li>
                                        <li>Events must comply with university policies and guidelines</li>
                                        <li>You will receive notifications about approval status via email</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </x-mary-card>

                    <x-mary-card title="Organization Information" subtitle="Details about your student organization">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-mary-input label="Organization Name" wire:model="organizationName" readonly/>
                            <x-mary-input label="Organization Course" wire:model="organizationCourse" readonly/>
                            <x-mary-input label="Name of Proponent" wire:model="proponentName" readonly/>
                            <x-mary-input label="Contact Email" type="email" wire:model="contactEmail" readonly/>
                            <x-mary-input label="Proponent Position" wire:model="proponentPosition" readonly/>
                            <x-mary-input label="Organization Adviser" wire:model="adviser" readonly/>
                            <x-mary-input label="Contact of Proponent" wire:model="proponent_contact"
                                          placeholder="0999 999 9999" required/>
                            <x-mary-input label="Contact of Adviser" wire:model="adviser_contact"
                                          placeholder="0999 999 9999"/>
                        </div>
                    </x-mary-card>
                @endif

                {{-- Step 2: Event Details --}}
                @if($currentStep === 2)
                    <x-mary-card title="Event Details" subtitle="Information about your proposed event">
                        <div class="space-y-4">
                            <x-mary-input label="Event Title" wire:model="eventTitle"
                                          placeholder="Enter your event title" required/>
                            <x-mary-textarea label="Event Description" wire:model="eventDescription" rows="4" required/>
                            <x-mary-select label="Event Type" wire:model.live="eventType" :options="$eventTypes"
                                           option-value="event_type_id" option-label="type_name" required/>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <x-mary-input label="PLV Participants" type="number"
                                              wire:model.live="expectedPLVParticipants" required/>
                                <x-mary-input label="Non-PLV Participants" type="number"
                                              wire:model.live="expectedNonPLVParticipants"/>
                                <x-mary-input label="Total" type="number" value="{{ $this->expectedParticipants }}"
                                              readonly/>
                            </div>
                        </div>
                    </x-mary-card>
                @endif

                {{-- Step 3: Schedule & Venue --}}
                @if($currentStep === 3)
                    <x-mary-card title="Schedule & Venue" subtitle="When and where your event will take place">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-mary-datetime label="Event Start Date" wire:model="eventStartDate" required/>

                            <x-mary-datetime label="Event End Date" wire:model="eventEndDate" required/>

                            <x-mary-datetime label="Event Start Time" wire:model="eventStartTime" type="time"
                                             required/>

                            <x-mary-datetime label="Event End Time" wire:model="eventEndTime" type="time" required/>

                            <x-mary-input label="Preferred Venue" wire:model="preferredVenue"
                                          placeholder="e.g., Student Center Auditorium" required/>

                            <x-mary-input label="Alternative Venue" wire:model="alternativeVenue"
                                          placeholder="Backup venue option"/>
                        </div>

                        <div class="mt-4">
                            <x-mary-textarea label="Special Requirements" wire:model="specialRequirements"
                                             placeholder="Audio/visual equipment, seating arrangement, catering, etc."
                                             rows="3"/>
                        </div>

                        <div class="mt-4">
                            <x-mary-checkbox label="Check this box if the activity is off-campus"
                                             wire:model.live="is_oc"/>
                        </div>

                        @if ($is_oc)
                            <div class="mt-4">
                                <x-mary-textarea label="Accommodation Provider (if any)" wire:model="oc_accommodation"
                                                 placeholder="Accommodation Provider Details" rows="2"/>
                            </div>

                            <div class="mb-4">
                                <x-mary-radio label="Transportation Service Provider" wire:model.live="oc_tsp"
                                              :options="[
                                    ['id' => 'in-house', 'name' => 'In-house'],
                                    ['id' => 'outsourced', 'name' => 'Outsourced'],
                                ]" inline/>
                            </div>

                            @if ($oc_tsp === 'outsourced')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-mary-input label="Name of Driver" wire:model="oc_driver_name"
                                                  placeholder="Enter the driver name"/>

                                    <x-mary-input label="Contact Details" wire:model="oc_driver_contact_number"
                                                  placeholder="Enter the driver's contact"/>

                                    <x-mary-input label="Type of Car" wire:model="oc_vehicle_type"
                                                  placeholder="Enter the type of car"/>

                                    <x-mary-input label="Plate Number" wire:model="oc_vehicle_plate_number"
                                                  placeholder="Enter the plate number"/>
                                </div>
                            @endif
                        @endif
                    </x-mary-card>
                @endif

                {{-- Step 4: Budget Information --}}
                @if($currentStep === 4)
                    <x-mary-card title="Budget Information" subtitle="Financial details of your event">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-mary-input label="Estimated Total Proposed Budget" type="number" step="0.01"
                                          wire:model.live="totalBudget" placeholder="0.00" prefix="₱" required/>

                            <x-mary-select label="Funding Source" wire:model="fundingSource" :options="$fundSources"
                                           option-value="source_id" option-label="source_name"
                                           placeholder="Select funding source" required/>
                        </div>

                        <div class="mt-4">
                            <x-mary-textarea label="Budget Breakdown" wire:model="budgetBreakdown"
                                             placeholder="Itemized list of expenses (venue, equipment, materials, etc.)"
                                             rows="4"/>
                        </div>

                        <div class="mt-4">
                            <x-mary-radio label="IGP Request" wire:model.live="igp_requested" :options="[
                            ['id' => 'true', 'name' => 'Requested'],
                            ['id' => 'false', 'name' => 'Not Requested'],
                        ]" inline required/>
                        </div>

                        @if ($igp_requested === 'true')
                            <div class="mt-4">
                                <x-mary-textarea label="IGP Brief Description" wire:model="igp_details"
                                                 placeholder="List all descriptions for IGP requested items" rows="4"/>
                            </div>
                        @endif
                    </x-mary-card>
                @endif

                {{-- Step 5: Attachments --}}
                @if($currentStep === 5)
                    {{-- File Attachments --}}
                    <x-mary-card title="Attachments" subtitle="Upload required documents and supporting files">
                        <div class="space-y-4">
                            <div class="bg-warning/10 p-4 rounded-lg border-l-4 border-warning">
                                <div class="flex items-start space-x-2">
                                    <x-mary-icon name="s-exclamation-triangle" class="w-5 h-5 text-warning mt-0.5"/>
                                    <div class="text-sm">
                                        <p class="font-medium mb-1">Required Documents:</p>
                                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                                            <li>Document containing the Rationale</li>
                                            @foreach($this->getRequiredDocuments() as $document)
                                                @if(is_array($document) && isset($document['nested']))
                                                    <li>{{ $document[0] }}</li>
                                                    <ul class="list-disc list-inside ml-8 mt-1 space-y-1 text-gray-600">
                                                        @foreach($document['nested'] as $nestedDoc)
                                                            <li>{{ $nestedDoc }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <li>{{ is_array($document) ? $document[0] : $document }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <x-mary-file
                                    wire:model="newAttachments"
                                    multiple
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx"
                                    hint="Upload multiple files (PDF, DOC, JPG, PNG, XLS). Max 10MB per file."/>

                                @if($attachments)
                                    <div class="mt-4 space-y-2">
                                        <p class="text-sm font-medium">Attached Files:</p>
                                        @foreach($attachments as $index => $file)
                                            <div class="flex items-center justify-between bg-base-200 p-2 rounded">
                                                <span class="text-sm">{{ $file->getClientOriginalName() }}</span>
                                                <x-mary-button
                                                    icon="o-x-mark"
                                                    wire:click="removeAttachment({{ $index }})"
                                                    class="btn-ghost btn-sm"
                                                    spinner/>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </x-mary-card>

                    {{-- Additional Information --}}
                    <x-mary-card title="Additional Information" subtitle="Any other relevant details">
                        <div class="space-y-4">
                            <x-mary-textarea label="Additional Notes" wire:model="additionalNotes"
                                             placeholder="Any other information you'd like to share about your event (security, food service, parking etc.)"
                                             rows="3"/>
                        </div>
                    </x-mary-card>
                @endif

                {{-- Step 6: Review & Submit --}}
                @if($currentStep === 6)
                    <x-mary-card title="Review & Submit" subtitle="Please review your information">
                        {{-- Show summary of all entered data --}}
                        <x-tickets.ticket-preview :ticket="$this->previewTicket"/>

                        {{-- Agreement & Submission --}}
                        <x-mary-card title="Agreement & Submission" subtitle="Please review and agree to the terms">
                            <div class="space-y-4">
                                <div class="bg-base-200 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-2">Terms and Conditions:</h4>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <ol class="list-decimal list-inside space-y-1 text-gray-600">
                                            <li><b>No disruption of classes.</b> In cases where classes will be
                                                affected,
                                                permission to
                                                excuse students from classes shall be approved by the respective Deans
                                                through
                                                the
                                                endorsement of the Chairperson.
                                            </li>
                                            <li><b>Observe University rules and regulations.</b></li>
                                            <li>Requested venue is available. However, activities/events considered as
                                                local,
                                                national and/or international that may utilize similar scheduled events
                                                shall be
                                                given priority. Requesting student organizations shall be compelled to
                                                request a
                                                different venue or consider rescheduling of events.
                                            </li>
                                            <li>Fund Source:</li>
                                            <ul class="ml-8 mt-1 space-y-1 text-gray-600">
                                                <li>4.1 If fund source is borne from organizational funds, the level of
                                                    approval
                                                    is
                                                    until the OSA Dean, provided the activity/event is within the
                                                    University;
                                                    otherwise, the approval shall be elevated within the jurisdiction of
                                                    the
                                                    University President.
                                                </li>
                                                <li>4.2 If fund source is borne from the University or any government
                                                    funding
                                                    source, the approval is automatically elevated within the
                                                    jurisdiction of
                                                    the
                                                    University President.
                                                </li>
                                                <li>4.3 A copy of the current Financial Statement is a required document
                                                    that
                                                    should
                                                    be attached with the request.
                                                </li>
                                            </ul>
                                            <li>Inform the Incident Command Preparedness Office of the activity/event.
                                            </li>
                                            <li>Ensure to document the activity/event to update the OSA Accomplishment
                                                Report
                                            </li>
                                            <li>Any change caused by the requesting party shall be subjected to submit
                                                an
                                                updated form.
                                            </li>
                                        </ol>
                                        <p class="mt-4">&nbsp;&nbsp;&nbsp; I hereby certify that the details provided
                                            herein are
                                            true and accurate to the best
                                            of my knowledge. The university shall exercise due diligence; thereby, the
                                            administrator and its faculty member shall not be held liable for any loss,
                                            injury,
                                            or damage beyond its control, including but not limited to the actions of
                                            third
                                            parties or actions of students that are contrary to the Student Code of
                                            Conduct,
                                            university policies, or directives.</p>
                                    </div>
                                </div>

                                <x-mary-checkbox label="I agree to the terms and conditions above"
                                                 wire:model="agreeToTerms"
                                                 required/>
                            </div>
                        </x-mary-card>
                    </x-mary-card>
                @endif

                {{-- Navigation Buttons --}}
                <div class="flex justify-between items-center pt-6">
                    <x-mary-button
                        label="Previous"
                        icon="o-arrow-left"
                        wire:click="previousStep"
                        class="btn-outline"
                        spinner
                        :disabled="$currentStep === 1 || $isProcessing"/>
                    @if($currentStep < $totalSteps)
                        <x-mary-button
                            label="Next"
                            icon="o-arrow-right"
                            wire:click="nextStep"
                            class="btn-primary"
                            spinner
                            :disabled="$isProcessing"/>
                    @else
                        <x-mary-button
                            label="Submit Ticket"
                            icon="s-paper-airplane"
                            type="submit"
                            class="btn-primary"
                            spinner
                            :disabled="$isProcessing"/>
                    @endif
                </div>
                <x-mary-toast/>
            </x-mary-form>
        </div>
    </div>

    @script
    <script>
        $wire.on('step-changed', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
    @endscript
</div>
