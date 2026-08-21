<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            {{ __('Reschedule Request') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            {{-- Header --}}
            <section
                class="mb-8 relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-warning/10 shadow-sm">
                <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-warning/15 blur-2xl"></div>
                <div class="relative p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-heading font-bold text-base-content">Reschedule a Ticket</h1>
                            <p class="text-base-content/70 mt-1">Submit a schedule change request for an existing
                                approved event.</p>
                        </div>
                    </div>
                </div>
            </section>

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-error/30 bg-error/10 p-4">
                    <div class="flex items-start gap-3">
                        <x-ui.icon name="s-exclamation-circle" class="w-5 h-5 text-error mt-0.5" />
                        <div>
                            <p class="font-semibold text-error">Please review the required fields below.</p>
                            <ul class="mt-2 list-disc list-inside text-sm text-base-content/80 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form wire:submit="submitReschedule">
                {{-- Progress Indicator --}}
                <div class="mb-8">
                    <div class="overflow-x-auto pb-2">
                        <div class="flex items-center min-w-max md:min-w-0 md:justify-between">
                            @for ($i = 1; $i <= $totalSteps; $i++)
                                <div class="flex flex-col items-center flex-1 min-w-20 gap-1">
                                    <button type="button" wire:click="goToStep({{ $i }})"
                                        aria-label="Step {{ $i }}"
                                        aria-current="{{ $currentStep === $i ? 'step' : 'false' }}"
                                        class="w-11 h-11 md:w-10 md:h-10 rounded-full flex items-center justify-center mb-2 transition-colors shrink-0
                                    {{ $currentStep === $i ? 'bg-primary text-white' : '' }}
                                    {{ $currentStep > $i ? 'bg-success text-white' : '' }}
                                    {{ $currentStep < $i ? 'bg-base-300 text-base-content' : '' }}"
                                        @if ($i > $currentStep + 1) disabled @endif>
                                        {{ $currentStep > $i ? '✓' : $i }}
                                    </button>
                                    <span class="text-xs text-center hidden md:block">
                                        @switch($i)
                                            @case(1)
                                                Select Event
                                            @break

                                            @case(2)
                                                New Schedule
                                            @break

                                            @case(3)
                                                Documents
                                            @break

                                            @case(4)
                                                Review
                                            @break
                                        @endswitch
                                    </span>
                                    <span class="text-xs text-center md:hidden">
                                        @switch($i)
                                            @case(1)
                                                Select
                                            @break

                                            @case(2)
                                                Schedule
                                            @break

                                            @case(3)
                                                Documents
                                            @break

                                            @case(4)
                                                Review
                                            @break
                                        @endswitch
                                    </span>
                                </div>

                                @if ($i < $totalSteps)
                                    <div
                                        class="h-1 min-w-6 flex-1 {{ $currentStep > $i ? 'bg-success' : 'bg-base-300' }} mx-1 md:mx-2">
                                    </div>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>

                {{-- Step 1: Select Event & Reschedule Type --}}
                @if ($currentStep === 1)
                    {{-- Important Notice (CMS-driven) --}}
                    <x-reschedule_guidelines :guidelines="$rescheduleGuidelines" />

                    <x-ui.card title="Select Event to Reschedule" subtitle="Choose from your approved events">
                        <div class="space-y-4">
                            <x-ui.select label="Select Event" wire:model.live="selectedEventId" :options="$approvedEvents"
                                placeholder="Select an event to reschedule" required />

                            @if ($selectedEventId)
                                <div class="bg-base-200 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-3">Current Event Details:</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-base-content/60">Event Title:</p>
                                            <p class="font-medium">{{ $selectedEvent->title }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-base-content/60">Current Date:</p>
                                            <p class="font-medium">
                                                {{ \Carbon\Carbon::parse($selectedEvent->date_from)->format('M d, Y') }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-base-content/60">Current Time:</p>
                                            <p class="font-medium">
                                                {{ \Carbon\Carbon::parse($selectedEvent->time_from)->format('h:i A') }}
                                                - {{ \Carbon\Carbon::parse($selectedEvent->time_to)->format('h:i A') }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-base-content/60">Current Venue:</p>
                                            <p class="font-medium">{{ $selectedEvent->venue_requested }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-base-content/60">Event Type:</p>
                                            <p class="font-medium">{{ $selectedEvent->eventType->type_name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-base-content/60">Expected Participants:</p>
                                            <p class="font-medium">{{ $selectedEvent->total_participants }} attendees
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </x-ui.card>

                    @if ($selectedEventId)
                        <x-ui.card title="What needs to be changed?" subtitle="Select the type of change you need">
                            <div class="space-y-3">
                                <x-ui.checkbox label="Change Date" wire:model.live="changeDate" />
                                <x-ui.checkbox label="Change Time" wire:model.live="changeTime" />
                                <x-ui.checkbox label="Change Venue" wire:model.live="changeVenue" />
                            </div>
                        </x-ui.card>
                    @endif
                @endif

                {{-- Step 2: New Schedule Details --}}
                @if ($currentStep === 2)
                    <x-ui.card title="New Schedule Details" subtitle="Provide your preferred new schedule">
                        <div class="space-y-4">
                            @if ($changeDate)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-ui.datetime label="New Start Date" wire:model="newStartDate" required />
                                    <x-ui.datetime label="New End Date" wire:model="newEndDate" required />
                                </div>
                            @endif

                            @if ($changeTime)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-ui.datetime label="New Start Time" type="time" wire:model="newStartTime"
                                        required />
                                    <x-ui.datetime label="New End Time" type="time" wire:model="newEndTime"
                                        required />
                                </div>
                            @endif

                            @if ($changeVenue)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div x-data="{
                                        newVenue: @entangle('newVenue').live,
                                        showOtherPreferred: @entangle('newVenue').live === 'other',
                                        venues: @js($venues)
                                    }">
                                        <x-ui.select label="New Preferred Venue" wire:model.live="newVenue"
                                            :options="[
                                                ...$venues,
                                                ['venue_id' => 'other', 'venue_name' => 'Others (Please Specify)'],
                                            ]" option-value="venue_id" option-label="venue_name"
                                            placeholder="Select a venue" required />

                                        <div x-show="newVenue === 'other'" x-collapse x-cloak class="mt-4">
                                            <x-ui.input label="Please specify preferred venue"
                                                wire:model.live="newVenueOther" placeholder="Enter venue name"
                                                required />
                                        </div>
                                    </div>

                                    <div x-data="{
                                        alternativeVenue: @entangle('alternativeVenue').live,
                                        showOtherAlternative: @entangle('alternativeVenue').live === 'other',
                                        venues: @js($venues)
                                    }">
                                        <x-ui.select label="Alternative Venue" wire:model.live="alternativeVenue"
                                            :options="[
                                                ...$venues,
                                                ['venue_id' => 'other', 'venue_name' => 'Others (Please Specify)'],
                                            ]" option-value="venue_id" option-label="venue_name"
                                            placeholder="Select backup venue" />

                                        <div x-show="alternativeVenue === 'other'" x-collapse x-cloak class="mt-4">
                                            <x-ui.input label="Please specify alternative venue"
                                                wire:model.live.blur="alternativeVenueOther"
                                                placeholder="Enter venue name" />
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (!$changeDate && !$changeTime && !$changeVenue)
                                <div class="text-center py-8">
                                    <p class="text-base-content/70">Please select at least one change type in the
                                        previous step</p>
                                </div>
                            @endif
                        </div>
                    </x-ui.card>
                @endif

                {{-- Step 3: Supporting Documents --}}
                @if ($currentStep === 3)
                    <x-ui.card title="Supporting Documents"
                        subtitle="Upload any relevant supporting documents (optional)">
                        <div class="space-y-4">
                            <div class="bg-info/10 p-4 rounded-lg border border-info/30">
                                <div class="flex items-start space-x-2">
                                    <x-ui.icon name="s-information-circle" class="w-5 h-5 text-info mt-0.5" />
                                    <div class="text-sm">
                                        <p class="font-medium mb-1">Supporting documents may include:</p>
                                        <ul class="list-disc list-inside space-y-1 text-base-content/70">
                                            <li>Email correspondence with speakers/guests</li>
                                            <li>Venue availability confirmations</li>
                                            <li>Weather reports or safety advisories</li>
                                            <li>Academic calendar conflicts</li>
                                            <li>Medical certificates or health advisories</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <x-ui.file wire:model="supportingDocuments"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx"
                                hint="Upload one file at a time (PDF, DOC, JPG, PNG, XLS). Max 10MB per file." />

                            @if ($supportingDocuments)
                                <div class="mt-4 space-y-2">
                                    <p class="text-sm font-medium">Attached Files:</p>
                                    @foreach ($supportingDocuments as $index => $file)
                                        <div class="flex items-center justify-between bg-base-200 p-2 rounded">
                                            <span class="text-sm">{{ $file->getClientOriginalName() }}</span>
                                            <x-ui.button icon="o-x-mark"
                                                wire:click="removeAttachment({{ $index }})"
                                                class="btn-ghost btn-sm" spinner />
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </x-ui.card>
                @endif

                {{-- Step 4: Preview & Agreement --}}
                @if ($currentStep === 4)
                    <x-ui.card title="Review & Submit" subtitle="Please review your reschedule request">
                        @if ($this->previewTicket)
                            <x-tickets.ticket-preview :ticket="$this->previewTicket" />
                        @endif

                        <x-ui.card title="Agreement and Submission" subtitle="Please review and agree to the terms">
                            <div class="space-y-4">
                                <x-terms_and_conditions />

                                {{-- Agreement Checkbox with Enhanced Styling --}}
                                <div class="mt-6 pt-4 border-t-2 border-base-content/10">
                                    <div class="bg-success/5 border border-success/30 p-4 rounded-lg">
                                        <x-ui.checkbox wire:model.live="agreeToTerms" required>
                                            <x-slot:label>
                                                <span class="text-sm md:text-base font-semibold text-base-content">
                                                    I have read, understood, and agree to all the terms and
                                                    conditions stated above
                                                </span>
                                            </x-slot:label>
                                        </x-ui.checkbox>
                                        <p class="text-xs text-base-content/60 mt-2 ml-6">
                                            By checking this box, you acknowledge your responsibility to comply
                                            with
                                            all university policies
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </x-ui.card>
                    </x-ui.card>
                @endif

                {{-- Navigation Buttons --}}
                <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-between sm:items-center pt-6">
                    <x-ui.button label="Previous" icon="o-arrow-left" wire:click="previousStep"
                        class="btn-outline w-full sm:w-auto data-loading:opacity-50 data-loading:pointer-events-none"
                        spinner :disabled="$currentStep === 1 || $isProcessing" />

                    @if ($currentStep < $totalSteps)
                        <x-ui.button label="Next" icon="o-arrow-right" wire:click="nextStep"
                            class="btn-primary w-full sm:w-auto data-loading:opacity-50 data-loading:pointer-events-none"
                            spinner :disabled="$isProcessing || (!$changeDate && !$changeTime && !$changeVenue)" />
                    @else
                        <x-ui.button label="Submit Reschedule Request" icon="s-paper-airplane"
                            wire:click="submitReschedule"
                            class="btn-primary w-full sm:w-auto data-loading:opacity-50 data-loading:pointer-events-none"
                            spinner :disabled="$isProcessing || (!$changeDate && !$changeTime && !$changeVenue)" />
                    @endif
                </div>

            </form>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-attachment-preview', ({
                url
            }) => {
                if (url) {
                    window.open(url, '_blank');
                }
            });
            Livewire.on('download-attachment', ({
                url,
                filename
            }) => {
                if (url) {
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = filename || 'download';
                    link.target = '_blank';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });
        });
    </script>

    @script
        <script>
            $wire.on('step-changed', () => {
                setTimeout(() => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }, 100);
            });
        </script>
    @endscript
</div>
