<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Reschedule Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-8">
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-heading font-bold text-base-content">Reschedule a Ticket</h1>
                            <p class="text-base-content/70 mt-1">Reschedule a ticket of your organization's event request</p>
                        </div>
                    </div>
                </div>
            </div>

            <x-mary-form wire:submit="submitReschedule">
                {{-- Progress Indicator --}}
                <div class="mb-8">
                    <div class="flex justify-between items-center">
                        @for($i = 1; $i <= $totalSteps; $i++)
                            <div class="flex flex-col items-center flex-1 gap-1">
                                <button
                                    type="button"
                                    wire:click="goToStep({{ $i }})"
                                    aria-label="Step {{ $i }}"
                                    aria-current="{{ $currentStep === $i ? 'step' : 'false' }}"
                                    class="w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-colors
                                    {{ $currentStep === $i ? 'bg-primary text-white' : '' }}
                                    {{ $currentStep > $i ? 'bg-success text-white' : '' }}
                                    {{ $currentStep < $i ? 'bg-base-300 text-base-content' : '' }}"
                                    @if($i > $currentStep + 1) disabled @endif>
                                    {{ $currentStep > $i ? '✓' : $i }}
                                </button>
                                <span class="text-xs text-center hidden md:block">
                                    @switch($i)
                                        @case(1) Select Event @break
                                        @case(2) New Schedule @break
                                        @case(3) Documents @break
                                        @case(4) Review @break
                                    @endswitch
                                </span>
                                <span class="text-xs text-center md:hidden">
                                    @switch($i)
                                        @case(1) Select @break
                                        @case(2) Schedule @break
                                        @case(3) Documents @break
                                        @case(4) Review @break
                                    @endswitch
                                </span>
                            </div>

                        @if($i < $totalSteps)
                                <div class="flex-1 h-1 {{ $currentStep > $i ? 'bg-success' : 'bg-base-300' }} mx-2"></div>
                            @endif
                        @endfor
                    </div>
                </div>

                {{-- Step 1: Select Event & Reschedule Type --}}
                @if($currentStep === 1)
                    {{-- Important Notice --}}
                    <x-mary-card>
                        <div class="bg-warning/10 p-4 rounded-lg border-l-4 border-warning">
                            <div class="flex items-start space-x-3">
                                <x-mary-icon name="s-exclamation-triangle" class="w-5 h-5 text-warning mt-0.5"/>
                                <div class="text-sm">
                                    <p class="font-medium mb-2 text-warning-content">Important Notice:</p>
                                    <ul class="list-disc list-inside space-y-1 text-gray-600">
                                        <li>Reschedule requests must be submitted at least 2 days before the current event date</li>
                                        <li>All reschedule requests are subject to approval by OSA and GSO</li>
                                        <li>Venue availability will be checked for your new requested date</li>
                                        <li>You will be notified via email about the status of your request</li>
                                        <li>Frequent reschedule requests may affect future event approvals</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </x-mary-card>

                    <x-mary-card title="Select Event to Reschedule" subtitle="Choose from your approved events">
                        <div class="space-y-4">
                            <x-mary-select label="Select Event" wire:model.live="selectedEventId" :options="$approvedEvents"
                                           placeholder="Select an event to reschedule" required/>

                            @if ($selectedEventId)
                                <div class="bg-base-200 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-3">Current Event Details:</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Event Title:</p>
                                            <p class="font-medium">{{ $selectedEvent->title }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Current Date:</p>
                                            <p class="font-medium">{{ \Carbon\Carbon::parse($selectedEvent->date_from)->format('M d, Y') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Current Time:</p>
                                            <p class="font-medium">{{ \Carbon\Carbon::parse($selectedEvent->time_from)->format('h:i A') }}
                                                - {{ \Carbon\Carbon::parse($selectedEvent->time_to)->format('h:i A') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Current Venue:</p>
                                            <p class="font-medium">{{ $selectedEvent->venue_requested }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Event Type:</p>
                                            <p class="font-medium">{{ $selectedEvent->eventType->type_name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Expected Participants:</p>
                                            <p class="font-medium">{{ $selectedEvent->total_participants }} attendees</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </x-mary-card>

                    @if($selectedEventId)
                        <x-mary-card title="What needs to be changed?" subtitle="Select the type of change you need">
                            <div class="space-y-3">
                                <x-mary-checkbox label="Change Date" wire:model.live="changeDate"/>
                                <x-mary-checkbox label="Change Time" wire:model.live="changeTime"/>
                                <x-mary-checkbox label="Change Venue" wire:model.live="changeVenue"/>
                            </div>
                        </x-mary-card>
                    @endif
                @endif

                {{-- Step 2: New Schedule Details --}}
                @if($currentStep === 2)
                    <x-mary-card title="New Schedule Details" subtitle="Provide your preferred new schedule">
                        <div class="space-y-4">
                            @if ($changeDate)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-mary-datetime label="New Start Date" wire:model="newStartDate" required/>
                                    <x-mary-datetime label="New End Date" wire:model="newEndDate" required/>
                                </div>
                            @endif

                            @if ($changeTime)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-mary-datetime label="New Start Time" type="time" wire:model="newStartTime" required/>
                                    <x-mary-datetime label="New End Time" type="time" wire:model="newEndTime" required/>
                                </div>
                            @endif

                            @if ($changeVenue)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-mary-input label="New Preferred Venue" wire:model="newVenue"
                                                  placeholder="e.g., University Auditorium" required/>
                                    <x-mary-input label="Alternative Venue" wire:model="alternativeVenue"
                                                  placeholder="Backup venue option"/>
                                </div>
                            @endif

                            @if (!$changeDate && !$changeTime && !$changeVenue)
                                <div class="text-center py-8">
                                    <p class="text-base-content/70">Please select at least one change type in the previous step</p>
                                </div>
                            @endif
                        </div>
                    </x-mary-card>
                @endif

                {{-- Step 3: Supporting Documents --}}
                @if($currentStep === 3)
                    <x-mary-card title="Supporting Documents" subtitle="Upload any relevant supporting documents (optional)">
                        <div class="space-y-4">
                            <div class="bg-info/10 p-4 rounded-lg border-l-4 border-info">
                                <div class="flex items-start space-x-2">
                                    <x-mary-icon name="s-information-circle" class="w-5 h-5 text-info mt-0.5"/>
                                    <div class="text-sm">
                                        <p class="font-medium mb-1">Supporting documents may include:</p>
                                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                                            <li>Email correspondence with speakers/guests</li>
                                            <li>Venue availability confirmations</li>
                                            <li>Weather reports or safety advisories</li>
                                            <li>Academic calendar conflicts</li>
                                            <li>Medical certificates or health advisories</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <x-mary-file
                                wire:model="supportingDocuments"
                                multiple
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx"
                                hint="Upload multiple files (PDF, DOC, JPG, PNG, XLS). Max 10MB per file."/>

                            @if($supportingDocuments)
                                <div class="mt-4 space-y-2">
                                    <p class="text-sm font-medium">Attached Files:</p>
                                    @foreach($supportingDocuments as $index => $file)
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
                    </x-mary-card>
                @endif

                {{-- Step 4: Preview & Agreement --}}
                @if($currentStep === 4)
                    <x-mary-card title="Review & Submit" subtitle="Please review your reschedule request">
                        @if($this->previewTicket)
                            <x-tickets.ticket-preview :ticket="$this->previewTicket"/>
                        @endif

                        <x-mary-card title="Agreement and Submission" subtitle="Please review and agree to the terms">
                            <div class="space-y-4">
                                <x-terms_and_conditions />

                                <x-mary-checkbox label="I agree to the terms and conditions above"
                                                 wire:model="agreeToTerms" required/>
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
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        wire:target="previousStep"
                        spinner
                        :disabled="$currentStep === 1 || $isProcessing"/>

                    @if($currentStep < $totalSteps)
                        <x-mary-button
                            label="Next"
                            icon="o-arrow-right"
                            wire:click="nextStep"
                            class="btn-primary"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            wire:target="nextStep"
                            spinner
                            :disabled="$isProcessing || (!$changeDate && !$changeTime && !$changeVenue)"/>
                    @else
                        <x-mary-button
                            label="Submit Reschedule Request"
                            icon="s-paper-airplane"
                            type="submit"
                            class="btn-primary"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            spinner
                            :disabled="$isProcessing || (!$changeDate && !$changeTime && !$changeVenue)"/>
                    @endif
                </div>

                <x-mary-toast/>
            </x-mary-form>
        </div>
    </div>

    @script
    <script>
        $wire.on('step-changed', () => {
            setTimeout(() => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 100);
        });
    </script>
    @endscript
</div>
