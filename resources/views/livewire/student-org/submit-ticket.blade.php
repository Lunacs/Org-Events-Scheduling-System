<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Submit Event Ticket (Digital Proposal)') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 overflow-x-hidden">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            {{-- Header --}}
            <div class="mb-8">
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-heading font-bold text-base-content">Submit Ticket</h1>
                            <p class="text-base-content/70 mt-1">Submit a ticket of your event request</p>
                        </div>
                    </div>
                </div>
            </div>
            <x-mary-form wire:submit="save">
                {{-- Progress Indicator --}}
                <div class="mb-6 md:mb-8 overflow-x-auto scroll-smooth snap-x snap-mandatory" id="progress-container">
                    <div class="flex justify-between items-center min-w-max md:min-w-0">
                        @for ($i = 1; $i <= $totalSteps; $i++)
                            <div class="flex flex-col items-center flex-1 min-w-[60px] md:min-w-0 snap-center"
                                id="step-{{ $i }}">
                                <button type="button" wire:click="goToStep({{ $i }})"
                                    aria-label="Step {{ $i }}:
                                        @switch($i)
                                            @case(1) Organization
                                            @case(2) Event Details
                                            @case(3) Schedule
                                            @case(4) Budget
                                            @case(5) Attachments
                                            @case(6) Review
                                        @endswitch"
                                    aria-current="{{ $currentStep === $i ? 'step' : 'false' }}"
                                    class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center mb-2 transition-colors flex-shrink-0
                                        {{ $currentStep === $i ? 'bg-primary text-white' : '' }}
                                        {{ $currentStep > $i ? 'bg-success text-white' : '' }}
                                        {{ $currentStep < $i ? 'bg-base-300 text-base-content' : '' }}"
                                    @if ($i > $currentStep + 1) disabled @endif>
                                    {{ $currentStep > $i ? '✓' : $i }}
                                </button>

                                <span class="text-xs text-center whitespace-nowrap px-1">
                                    @switch($i)
                                        @case(1)
                                            Organization
                                        @break

                                        @case(2)
                                            Event Details
                                        @break

                                        @case(3)
                                            Schedule
                                        @break

                                        @case(4)
                                            Budget
                                        @break

                                        @case(5)
                                            Attachments
                                        @break

                                        @case(6)
                                            Review
                                        @break
                                    @endswitch
                                </span>
                            </div>
                            @if ($i < $totalSteps)
                                <div
                                    class="flex-1 h-1 {{ $currentStep > $i ? 'bg-success' : 'bg-base-300' }} mx-1 md:mx-2 min-w-[20px]">
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
                        <div class="bg-info/10 p-4 rounded-lg border-l-4 border-info mb-4">
                            <div class="flex items-start space-x-2">
                                <x-mary-icon name="s-information-circle" class="w-5 h-5 text-info mt-0.5" />
                                <div class="text-sm">
                                    <p class="font-medium mb-2">Important Guidelines:</p>
                                    <ul class="list-disc list-inside space-y-1 text-gray-600">
                                        <li>Submit your request at least 14 days before your event date</li>
                                        <li>All required fields must be completed</li>
                                        <li>Upload all necessary attachments (permit forms, venue reservations,
                                            etc.)
                                        </li>
                                        <li>Events must comply with university policies and guidelines</li>
                                        <li>You will receive notifications about approval status via email</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </x-mary-card>
                    <div class="bg-warning/10 p-4 rounded-lg border-l-4 border-warning mb-4">
                        <x-mary-checkbox label="Check this box if the proposal is amended from a previous submission."
                            wire:model.live="is_amended" />
                    </div>

                    <x-mary-card title="Organization Information" subtitle="Details about your student organization">
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
                                required />
                        </div>
                    </x-mary-card>
                @endif

                {{-- Step 2: Event Details --}}
                @if ($currentStep === 2)
                    <x-mary-card title="Event Details" subtitle="Information about your proposed event">
                        <div class="space-y-4">
                            <x-mary-input label="Event Title" wire:model.live.debounce.300ms="eventTitle"
                                placeholder="Enter your event title" required />
                            <x-mary-textarea label="Event Description" wire:model.live.debounce.300ms="eventDescription"
                                rows="4" required />
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
                                required />

                            <x-mary-datetime label="Event End Date" wire:model.live.debounce.300ms="eventEndDate"
                                required />

                            <x-mary-datetime label="Event Start Time" wire:model.live="eventStartTime" type="time"
                                required />

                            <x-mary-datetime label="Event End Time" wire:model.live="eventEndTime" type="time"
                                required />

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
                                        required />
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
                                        wire:model.blur="alternativeVenueOther" placeholder="Enter venue name" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-mary-textarea label="Special Requirements"
                                wire:model.live.debounce.300ms="specialRequirements"
                                placeholder="Audio/visual equipment, seating arrangement, catering, etc."
                                rows="3" />
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
                                        placeholder="Accommodation Provider Details" rows="2" />
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
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-mary-input label="Name of Driver"
                                                wire:model.live.debounce.300ms="oc_driver_name"
                                                placeholder="Enter the driver name" />

                                            <x-mary-input label="Contact Details"
                                                wire:model.live.debounce.300ms="oc_driver_contact_number"
                                                placeholder="Enter the driver's contact" />

                                            <x-mary-input label="Type of Transportation"
                                                wire:model.live.debounce.300ms="oc_transportation_type"
                                                placeholder="Enter the type of transportation" />

                                            <x-mary-input label="Plate Number"
                                                wire:model.live.debounce.300ms="oc_vehicle_plate_number"
                                                placeholder="Enter the plate number" />
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
                            <x-mary-textarea label="Budget Breakdown" wire:model.live.debounce.300ms="budgetBreakdown"
                                placeholder="Itemized list of expenses (venue, equipment, materials, etc.)"
                                rows="4" />
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
                                        rows="4" />
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
                                <div role="status" aria-live="polite" aria-atomic="true" class="sr-only">
                                    @if ($errors->any())
                                        {{ count($errors) }} validation errors found
                                    @endif
                                </div>
                                <x-mary-file wire:model="newAttachments" aria-label="Upload event documents"
                                    aria-describedby="file-help-text"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                                    <x-slot:hint>
                                        <span id="file-help-text">
                                            Upload one file at a time, up to 10MB. Accepted: PDF, DOC, images, Excel
                                        </span>
                                    </x-slot:hint>
                                </x-mary-file>

                                @if ($attachments)
                                    <div class="mt-4 space-y-2">
                                        <p class="text-sm font-medium">Attached Files:</p>
                                        @foreach ($attachments as $index => $file)
                                            <div class="flex items-center justify-between bg-base-200 p-2 rounded">
                                                <span class="text-sm">{{ $file->getClientOriginalName() }}</span>
                                                <x-mary-button icon="o-x-mark"
                                                    wire:click="removeAttachment({{ $index }})"
                                                    class="btn-ghost btn-sm" spinner />
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
                            <x-mary-textarea label="Additional Notes" wire:model.live.debounce.300ms="additionalNotes"
                                placeholder="Any other information you'd like to share about your event (security, food service, parking etc.)"
                                rows="3" />
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
                <div class="flex justify-between items-center pt-6">
                    <x-mary-button label="Previous" icon="o-arrow-left" wire:click="previousStep"
                        class="btn-outline" wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed" wire:target="previousStep" spinner
                        :disabled="$currentStep === 1 || $isProcessing" />
                    @if ($currentStep < $totalSteps)
                        <x-mary-button label="Next" icon="o-arrow-right" wire:click="nextStep"
                            x-on:click="isSubmitting = true" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="btn-primary {{ $errors->any() ? '<opacity-75></opacity-75> cursor-not-allowed' : '' }}"
                            :disabled="$errors->any()" />
                    @else
                        <x-mary-button label="Submit Ticket" icon="s-paper-airplane" wire:click="save"
                            wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed"
                            wire:target="save" class="btn-primary" :disabled="!$agreeToTerms || $isProcessing" />
                    @endif
                </div>
                <x-mary-toast />
            </x-mary-form>
        </div>
    </div>

    @script
        <script>
            $wire.on('step-changed', () => {
                setTimeout(() => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
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
        </script>
    @endscript
    @script
        <script>
            const DRAFT_KEY = 'ticket_draft_{{ auth()->id() }}';
            const SUBMITTED_FLAG_KEY = 'ticket_submitted_{{ auth()->id() }}';
            let draftTimeout;
            let modalShown = false;
            let isSubmitting = false; // Flag to prevent auto-save during submission

            // Save draft with debouncing
            $wire.on('save-draft', (event) => {
                // Don't save if currently submitting
                if (isSubmitting) {
                    console.log('Skipping auto-save during submission');
                    return;
                }

                clearTimeout(draftTimeout);
                draftTimeout = setTimeout(() => {
                    const data = event[0] || event;
                    localStorage.setItem(DRAFT_KEY, JSON.stringify({
                        ...data,
                        savedAt: new Date().toISOString(),
                        draftId: Date.now()
                    }));
                    console.log('Draft saved with ID:', Date.now());
                }, 2000);
            });

            // Clear draft (regular)
            $wire.on('clear-draft', () => {
                localStorage.removeItem(DRAFT_KEY);
                console.log('Draft cleared');
            });

            // Clear draft immediately (for submission)
            $wire.on('clear-draft-immediate', () => {
                isSubmitting = true; // Set flag to prevent further auto-saves
                clearTimeout(draftTimeout); // Cancel any pending auto-save

                const draft = localStorage.getItem(DRAFT_KEY);
                let draftId = null;

                if (draft) {
                    try {
                        const draftData = JSON.parse(draft);
                        draftId = draftData.draftId;
                    } catch (e) {
                        console.error('Error parsing draft:', e);
                    }
                }

                // Remove the draft
                localStorage.removeItem(DRAFT_KEY);

                // NEW: Set a persistent "just submitted" flag with timestamp
                localStorage.setItem(`ticket_just_submitted_${draftId}`, Date.now());
                console.log('Draft removed from storage');

                // Store submission record with the draft ID
                if (draftId) {
                    let submissions = [];

                    try {
                        const stored = localStorage.getItem(SUBMITTED_FLAG_KEY);
                        const parsed = stored ? JSON.parse(stored) : [];
                        submissions = Array.isArray(parsed) ? parsed : [];
                    } catch (e) {
                        console.error('Error parsing submissions, resetting:', e);
                        submissions = [];
                    }

                    submissions.push({
                        draftId: draftId,
                        submittedAt: Date.now()
                    });

                    if (submissions.length > 10) {
                        submissions.shift();
                    }

                    localStorage.setItem(SUBMITTED_FLAG_KEY, JSON.stringify(submissions));
                    console.log('Draft cleared immediately after submission, ID:', draftId);
                }

                // Keep flag set for a few seconds to ensure no race conditions
                setTimeout(() => {
                    isSubmitting = false;
                }, 3000);
            });

            // Function to close and cleanup modal
            function closeModal() {
                const modal = document.getElementById('draftModal');
                if (modal) {
                    modal.style.opacity = '0';
                    setTimeout(() => {
                        modal.remove();
                        modalShown = false;
                        console.log('Modal removed from DOM');
                    }, 300);
                }
            }

            // Function to remove any existing modals
            function cleanupExistingModal() {
                const existingModal = document.getElementById('draftModal');
                if (existingModal) {
                    console.log('Removing existing modal');
                    existingModal.remove();
                }
                modalShown = false;
            }

            // Function to check if draft is stale (submitted recently)
            function isDraftStale(draftId) {
                // NEW: Check for "just submitted" flag first
                const justSubmittedTimestamp = localStorage.getItem(`ticket_just_submitted_${draftId}`);
                if (justSubmittedTimestamp) {
                    const elapsed = Date.now() - parseInt(justSubmittedTimestamp);
                    if (elapsed < 5 * 60 * 1000) { // 5 minutes
                        console.log('Draft is stale (submission flag exists)');
                        return true;
                    } else {
                        // Clean up old flag
                        localStorage.removeItem(`ticket_just_submitted_${draftId}`);
                    }
                }

                let submissions = [];

                try {
                    const stored = localStorage.getItem(SUBMITTED_FLAG_KEY);
                    const parsed = stored ? JSON.parse(stored) : [];

                    // Ensure it's an array (migration from old format)
                    if (!Array.isArray(parsed)) {
                        console.log('Invalid submissions format, resetting');
                        localStorage.removeItem(SUBMITTED_FLAG_KEY);
                        return false;
                    }

                    submissions = parsed;
                } catch (e) {
                    console.error('Error parsing submissions:', e);
                    localStorage.removeItem(SUBMITTED_FLAG_KEY);
                    return false;
                }

                const now = Date.now();

                // Clean up old submissions (older than 5 minutes)
                const cleanedSubmissions = submissions.filter(sub => {
                    return sub && sub.submittedAt && (now - sub.submittedAt) < 5 * 60 * 1000;
                });

                // Update storage with cleaned list
                if (cleanedSubmissions.length !== submissions.length) {
                    localStorage.setItem(SUBMITTED_FLAG_KEY, JSON.stringify(cleanedSubmissions));
                }

                // Check if this specific draft was submitted
                const wasSubmitted = cleanedSubmissions.some(sub => sub.draftId === draftId);

                if (wasSubmitted) {
                    console.log('Draft is stale (this specific draft was submitted)');
                    return true;
                }

                return false;
            }

            // Function to attach button listeners
            function attachButtonListeners(draftData, retryCount = 0) {
                const loadBtn = document.getElementById('loadDraftBtn');
                const discardBtn = document.getElementById('discardDraftBtn');

                console.log('Attempting to attach listeners, retry:', retryCount);
                console.log('Load button found:', !!loadBtn);
                console.log('Discard button found:', !!discardBtn);

                if (!loadBtn || !discardBtn) {
                    if (retryCount < 10) {
                        setTimeout(() => attachButtonListeners(draftData, retryCount + 1), 100);
                    } else {
                        console.error('Could not find modal buttons after multiple retries');
                    }
                    return;
                }

                console.log('Attaching click listeners to buttons');

                loadBtn.addEventListener('click', function handleLoadClick() {
                    console.log('Load button clicked');
                    console.log('Draft data:', draftData.data);

                    loadBtn.removeEventListener('click', handleLoadClick);

                    // Show loading spinner
                    const spinner = document.getElementById('draftLoadingSpinner');
                    if (spinner) {
                        spinner.classList.remove('hidden');
                        loadBtn.disabled = true;
                        discardBtn.disabled = true;
                    }

                    // Dispatch load event
                    Livewire.dispatch('load-draft', {
                        data: draftData.data
                    });

                    // Wait for draft to be loaded before closing modal
                    const checkDraftLoaded = setInterval(() => {
                        // Check if draft data has been applied (you can verify by checking a specific property)
                        if ($wire.currentStep === draftData.data.currentStep) {
                            clearInterval(checkDraftLoaded);
                            setTimeout(() => {
                                closeModal();
                            }, 300); // Small delay to ensure all data is rendered
                        }
                    }, 100);

                    // Fallback timeout in case check fails
                    setTimeout(() => {
                        clearInterval(checkDraftLoaded);
                        closeModal();
                    }, 3000);
                });

                discardBtn.addEventListener('click', function handleDiscardClick() {
                    console.log('Discard button clicked');

                    discardBtn.removeEventListener('click', handleDiscardClick);

                    Livewire.dispatch('discard-draft');

                    setTimeout(() => {
                        closeModal();
                    }, 100);
                });
            }

            // Function to check and show draft modal
            function checkAndShowDraftModal() {
                const draft = localStorage.getItem(DRAFT_KEY);
                console.log('Checking for draft:', draft);
                console.log('Modal already shown:', modalShown);

                // Clean up any existing modal first
                cleanupExistingModal();

                if (draft && !modalShown) {
                    const draftData = JSON.parse(draft);
                    const draftId = draftData.draftId;

                    // Check if this specific draft is stale
                    if (isDraftStale(draftId)) {
                        console.log('Removing stale draft');
                        localStorage.removeItem(DRAFT_KEY);
                        return;
                    }

                    modalShown = true;
                    const savedDate = new Date(draftData.savedAt);
                    const formattedDate = savedDate.toLocaleString();

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
                        'hidden absolute inset-0 bg-base-100 bg-opacity-90 flex items-center justify-center rounded-lg';

                    const spinnerContent = document.createElement('div');
                    spinnerContent.className = 'flex flex-col items-center gap-3';

                    const spinner = document.createElement('span');
                    spinner.className = 'loading loading-spinner loading-lg text-primary';

                    const spinnerText = document.createElement('p');
                    spinnerText.className = 'text-sm font-medium';
                    spinnerText.textContent = 'Loading draft...';

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
                    console.log('Modal HTML inserted into DOM');

                    requestAnimationFrame(() => {
                        setTimeout(() => {
                            attachButtonListeners(draftData);
                        }, 50);
                    });
                }
            }

            // Check on initial page load (full reload)
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    setTimeout(checkAndShowDraftModal, 500);
                });
            } else {
                setTimeout(checkAndShowDraftModal, 500);
            }

            // Check when navigating to this page via Livewire (SPA navigation)
            document.addEventListener('livewire:navigated', () => {
                console.log('Livewire navigated event fired');

                cleanupExistingModal();

                setTimeout(() => {
                    const ticketForm = document.querySelector('[wire\\:submit="save"]');
                    if (ticketForm) {
                        console.log('Navigated to ticket submission page, checking for draft');
                        checkAndShowDraftModal();
                    } else {
                        console.log('Not on ticket submission page, skipping draft check');
                    }
                }, 800);
            });

            // Auto-save indicator
            $wire.on('save-draft', () => {
                const indicator = document.getElementById('autosave-indicator');
                if (indicator) {
                    indicator.classList.remove('hidden');
                    setTimeout(() => indicator.classList.add('hidden'), 2000);
                }
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
