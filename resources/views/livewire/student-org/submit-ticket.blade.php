<div>
    @php
        $stepLabels = [
            1 => 'Organization',
            2 => 'Event details',
            3 => 'Schedule and venue',
            4 => 'Budget',
            5 => 'Attachments',
            6 => 'Review and submit',
        ];
    @endphp

    <div class="mx-auto w-full max-w-4xl overflow-x-hidden">
        <div class="space-y-6">
            @if ($amendingTicketId)
                <x-ui.page-heading title="Revise and resubmit"
                    subtitle="Update the details OSA asked about, then resubmit. The ticket keeps its number and comment thread." />
            @else
                <x-ui.page-heading title="Submit a ticket"
                    subtitle="Request approval for an event. Your progress is saved as a draft, so you can finish later." />
            @endif

            @if ($errors->any())
                <div role="alert" class="rounded-box border border-error/30 bg-error/[0.06] p-4">
                    <div class="flex items-start gap-3">
                        <x-ui.icon name="s-exclamation-circle" class="mt-0.5 size-5 shrink-0 text-error" aria-hidden="true" />
                        <div>
                            <p class="text-sm font-semibold text-base-content">
                                Fix {{ $errors->count() === 1 ? 'this field' : 'these ' . $errors->count() . ' fields' }} to continue
                            </p>
                            <ul class="mt-1.5 list-disc space-y-1 ps-5 text-sm text-base-content/80">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form wire:submit="save" wire:ref="submitForm">
                {{-- Progress (Goal-Gradient): full stepper on desktop, compact on mobile --}}
                <nav aria-label="Ticket steps" class="mb-6" id="progress-container">
                    <div class="sm:hidden">
                        <div class="flex items-baseline justify-between text-sm">
                            <p class="font-semibold text-base-content">{{ $stepLabels[$currentStep] }}</p>
                            <p class="tabular-nums text-base-content/70">Step {{ $currentStep }} of {{ $totalSteps }}</p>
                        </div>
                        <progress class="progress progress-primary mt-2 h-1.5 w-full" value="{{ $currentStep }}"
                            max="{{ $totalSteps }}" aria-label="Form progress: step {{ $currentStep }} of {{ $totalSteps }}"></progress>
                    </div>

                    <ol class="hidden items-center sm:flex">
                        @for ($i = 1; $i <= $totalSteps; $i++)
                            <li @class(['flex items-center', 'flex-1' => $i < $totalSteps]) id="step-{{ $i }}">
                                <button type="button" wire:click="goToStep({{ $i }})"
                                    @if ($i > $currentStep + 1) disabled @endif
                                    @if ($currentStep === $i) aria-current="step" @endif
                                    class="group flex items-center gap-2 rounded-field py-1 pe-2 text-start disabled:cursor-not-allowed">
                                    <span @class([
                                        'flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold tabular-nums transition-colors',
                                        'bg-primary text-primary-content' => $currentStep === $i,
                                        'bg-success text-success-content' => $currentStep > $i,
                                        'border border-base-300 bg-base-100 text-base-content/70' => $currentStep < $i,
                                    ])>
                                        @if ($currentStep > $i)
                                            <x-ui.icon name="o-check" class="size-4" aria-hidden="true" />
                                            <span class="sr-only">Completed:</span>
                                        @else
                                            {{ $i }}
                                        @endif
                                    </span>
                                    <span @class([
                                        'hidden whitespace-nowrap text-sm md:inline',
                                        'font-semibold text-base-content' => $currentStep === $i,
                                        'text-base-content/80 group-hover:text-base-content' => $currentStep > $i,
                                        'text-base-content/60' => $currentStep < $i,
                                    ])>{{ $stepLabels[$i] }}</span>
                                    <span class="sr-only md:hidden">{{ $stepLabels[$i] }}</span>
                                </button>
                                @if ($i < $totalSteps)
                                    <span @class([
                                        'mx-2 h-px min-w-3 flex-1 transition-colors',
                                        'bg-success' => $currentStep > $i,
                                        'bg-base-300' => $currentStep <= $i,
                                    ]) aria-hidden="true"></span>
                                @endif
                            </li>
                        @endfor
                    </ol>
                </nav>

                {{-- Step 1: Organization Information --}}
                @if ($currentStep === 1)
                    <details class="group mb-4 rounded-box border border-base-300 bg-base-100 open:pb-4" open>
                        <summary
                            class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-box px-4 py-3 sm:px-5 [&::-webkit-details-marker]:hidden">
                            <span class="flex items-center gap-2 text-sm font-semibold text-base-content">
                                <x-ui.icon name="o-information-circle" class="size-5 text-info" aria-hidden="true" />
                                Before you start
                            </span>
                            <x-ui.icon name="o-chevron-down" class="size-4 text-base-content/60 transition-transform group-open:rotate-180" aria-hidden="true" />
                        </summary>
                        <div class="px-4 sm:px-5">
                            <x-submit_guidelines :guidelines="$ticketGuidelines" />
                        </div>
                    </details>

                    <x-ui.card title="Organization" subtitle="Filled in from your account. Only the adviser's contact number is needed.">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <x-ui.input label="Organization name" wire:model="organizationName" readonly />
                            <x-ui.input label="Organization course" wire:model="organizationCourse" readonly />
                            <x-ui.input label="Name of proponent" wire:model="proponentName" readonly />
                            <x-ui.input label="Contact email" type="email" wire:model="contactEmail" readonly />
                            <x-ui.input label="Proponent position" wire:model="proponentPosition" readonly />
                            <x-ui.input label="Organization adviser" wire:model="adviser" readonly />
                            <x-ui.input label="Proponent contact number" wire:model="proponent_contact"
                                placeholder="0999 999 9999" readonly />
                            <x-ui.input label="Adviser contact number" wire:model.live.debounce.300ms="adviser_contact"
                                required maxlength="11" inputmode="numeric" autocomplete="off" placeholder="09XXXXXXXXX"
                                hint="11-digit mobile number, for example 09171234567."
                                x-on:input="$el.value = $el.value.replace(/\D/g, '').slice(0, 11); $wire.set('adviser_contact', $el.value)" />
                        </div>
                    </x-ui.card>
                @endif

                {{-- Step 2: Event Details --}}
                @if ($currentStep === 2)
                    <x-ui.card title="Event details" subtitle="Information about your proposed event">
                        <div class="space-y-4">
                            <x-ui.input label="Event title" wire:model.live.debounce.300ms="eventTitle"
                                placeholder="Enter your event title" required maxlength="255" />
                            <x-ui.textarea label="Event description" wire:model.live.debounce.300ms="eventDescription"
                                rows="4" required maxlength="2000"
                                x-on:input="if($el.value.length > 2000) $el.value = $el.value.slice(0, 2000)" />
                            <x-ui.select label="Event type" wire:model.live="eventType" :options="$eventTypes"
                                option-value="event_type_id" option-label="type_name" required />
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <x-ui.input label="PLV participants" type="number"
                                    wire:model.live="expectedPLVParticipants" required />
                                <x-ui.input label="Non-PLV participants" type="number"
                                    wire:model.live="expectedNonPLVParticipants" />
                                <x-ui.input label="Total" type="number" value="{{ $this->expectedParticipants }}"
                                    readonly />
                            </div>
                        </div>
                    </x-ui.card>
                @endif

                {{-- Step 3: Schedule & Venue --}}
                @if ($currentStep === 3)
                    <x-ui.card title="Schedule & venue" subtitle="When and where your event will take place">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.datetime label="Event start date" wire:model.live.debounce.300ms="eventStartDate"
                                required min="{{ now()->format('Y-m-d') }}" />

                            <x-ui.datetime label="Event end date" wire:model.live.debounce.300ms="eventEndDate" required
                                min="{{ $eventStartDate ?: now()->format('Y-m-d') }}" />

                            <x-ui.datetime label="Event start time" wire:model.live="eventStartTime" type="time"
                                required />

                            <x-ui.datetime label="Event end time" wire:model.live="eventEndTime" type="time"
                                required
                                min="{{ $eventStartDate === $eventEndDate && $eventStartTime ? $eventStartTime : '' }}" />

                            <div x-data="{
                                preferredVenue: @entangle('preferredVenue').live,
                                showOtherPreferred: @entangle('preferredVenue').live === 'other',
                                venues: @js($venues)
                            }">
                                <x-ui.select label="Preferred venue" wire:model.live="preferredVenue"
                                    :options="[
                                        ...$venues,
                                        ['venue_id' => 'other', 'venue_name' => 'Others (Please Specify)'],
                                    ]" option-value="venue_id" option-label="venue_name"
                                    placeholder="Select a venue" required />

                                <div x-show="preferredVenue === 'other'" x-collapse x-cloak class="mt-4">
                                    <x-ui.input label="Please specify preferred venue"
                                        wire:model.live="preferredVenueOther" placeholder="Enter venue name" required
                                        maxlength="255" />
                                </div>
                            </div>

                            <div x-data="{
                                alternativeVenue: @entangle('alternativeVenue').live,
                                showOtherAlternative: @entangle('alternativeVenue').live === 'other',
                                venues: @js($venues)
                            }">
                                <x-ui.select label="Alternative venue" wire:model.live="alternativeVenue"
                                    :options="[
                                        ...$venues,
                                        ['venue_id' => 'other', 'venue_name' => 'Others (Please Specify)'],
                                    ]" option-value="venue_id" option-label="venue_name"
                                    placeholder="Select backup venue" />

                                <div x-show="alternativeVenue === 'other'" x-collapse x-cloak class="mt-4">
                                    <x-ui.input label="Please specify alternative venue"
                                        wire:model.live.blur="alternativeVenueOther" placeholder="Enter venue name"
                                        maxlength="255" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-ui.textarea label="Special requirements"
                                wire:model.live.debounce.300ms="specialRequirements"
                                placeholder="Audio/visual equipment, seating arrangement, catering, etc."
                                rows="3" maxlength="2000"
                                x-on:input="if($el.value.length > 2000) $el.value = $el.value.slice(0, 2000)" />
                        </div>

                        <div x-data="{ open: @entangle('is_oc') }">
                            <div class="mt-4">
                                <x-ui.checkbox label="Check this box if the activity is off-campus"
                                    wire:model.live="is_oc" />
                            </div>

                            <div x-show="open" x-collapse x-cloak>
                                <div class="mt-4">
                                    <x-ui.textarea label="Accommodation provider (if any)"
                                        wire:model.live.debounce.300ms="oc_accommodation"
                                        placeholder="Accommodation provider details" rows="2" maxlength="2000"
                                        x-on:input="if($el.value.length > 2000) $el.value = $el.value.slice(0, 2000)" />
                                </div>

                                <div x-data="{ tsp: @entangle('oc_tsp') }">
                                    <div class="mb-4">
                                        <x-ui.radio label="Transportation service provider" wire:model.live="oc_tsp"
                                            :options="[
                                                ['id' => 'in-house', 'name' => 'In-house'],
                                                ['id' => 'outsourced', 'name' => 'Outsourced'],
                                            ]" inline />
                                    </div>

                                    <div x-show="tsp === 'outsourced'" x-collapse x-cloak>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <x-ui.input label="Name of driver"
                                                wire:model.live.debounce.300ms="oc_driver_name"
                                                placeholder="Enter the driver name" maxlength="60" />

                                            <x-ui.input label="Contact details"
                                                wire:model.live.debounce.300ms="oc_driver_contact_number"
                                                placeholder="Enter the driver's contact" maxlength="11"
                                                inputmode="numeric"
                                                x-on:input="$el.value = $el.value.replace(/[^0-9\s\-\+\(\)]/g, '').slice(0, 11); $wire.set('oc_driver_contact_number', $el.value)" />

                                            <x-ui.input label="Type of transportation"
                                                wire:model.live.debounce.300ms="oc_transportation_type"
                                                placeholder="Enter the type of transportation" maxlength="50" />

                                            <x-ui.input label="Plate number"
                                                wire:model.live.debounce.300ms="oc_vehicle_plate_number"
                                                placeholder="Enter the plate number" maxlength="100" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                @endif

                {{-- Step 4: Budget Information --}}
                @if ($currentStep === 4)
                    <x-ui.card title="Budget information" subtitle="Financial details of your event">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Inline DaisyUI input: x-ui.input has no `prefix` slot, so the ₱ prefix is rendered inline --}}
                            <div>
                                <label class="block text-sm font-medium mb-1">Estimated Total Proposed Budget</label>
                                <label class="input flex items-center gap-2 w-full">
                                    <span class="opacity-60">₱</span>
                                    <input type="number" step="0.01" wire:model.live="totalBudget"
                                        placeholder="0.00" class="grow" />
                                </label>
                                @error('totalBudget')
                                    <x-ui.input-error :messages="$message" class="mt-1" />
                                @enderror
                            </div>

                            <x-ui.select label="Funding source" wire:model.live="fundingSource" :options="$fundSources"
                                option-value="source_id" option-label="source_name"
                                placeholder="Select funding source" required />
                        </div>

                        <div class="mt-4">
                            <x-ui.textarea :label="(int) $fundingSource === 1 ? 'Budget Proposal Breakdown' : 'Request Details'" wire:model.live.debounce.300ms="budgetBreakdown"
                                :placeholder="(int) $fundingSource === 1
                                    ? 'Itemized list of expenses or Program Parapernalias Information (venue, equipment, materials, etc.)'
                                    : 'Example: 1. 200 packs of foods'" rows="4" maxlength="2000"
                                x-on:input="if($el.value.length > 2000) $el.value = $el.value.slice(0, 2000)" />
                        </div>

                        <div x-data="{ igp: @entangle('igp_requested') }">
                            <div class="mt-4">
                                <x-ui.radio label="IGP (income generated project) request"
                                    wire:model.live="igp_requested" :options="[
                                        ['id' => 'true', 'name' => 'Requested'],
                                        ['id' => 'false', 'name' => 'Not Requested'],
                                    ]" inline required />
                            </div>

                            <div x-show="igp === 'true'" x-collapse x-cloak>
                                <div class="mt-4">
                                    <x-ui.textarea label="IGP (income generated project) brief description"
                                        wire:model.live.debounce.300ms="igp_details"
                                        placeholder="List all descriptions for IGP (income generated project) requested items"
                                        rows="4" maxlength="2000"
                                        x-on:input="if($el.value.length > 2000) $el.value = $el.value.slice(0, 2000)" />
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                @endif

                {{-- Step 5: Attachments --}}
                @if ($currentStep === 5)
                    {{-- File Attachments --}}
                    <x-ui.card title="Attachments" subtitle="Upload required documents and supporting files">
                        <div class="space-y-4">
                            <x-documentary-requirements :event-type-id="$eventType" />

                            <div class="space-y-2">
                                <x-filepond wire-model="uploadedFileIds" :max-files="25" :max-size-mb="10" />
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Additional Information --}}
                    <x-ui.card title="Additional information" subtitle="Any other relevant details">
                        <div class="space-y-4">
                            <x-ui.textarea label="Additional notes" wire:model.live.debounce.300ms="additionalNotes"
                                placeholder="Any other information you'd like to share about your event (security, food service, parking etc.)"
                                rows="3" maxlength="2000"
                                x-on:input="if($el.value.length > 2000) $el.value = $el.value.slice(0, 2000)" />
                        </div>
                    </x-ui.card>
                @endif

                {{-- Step 6: Review & Submit --}}
                @if ($currentStep === 6)
                    <div class="space-y-6 overflow-x-hidden">
                        <x-ui.card title="Review your request"
                            subtitle="Check every detail. Use Back or the steps above to change anything before you submit."
                            class="overflow-hidden">
                            <div class="overflow-hidden">
                                <x-tickets.ticket-preview :ticket="$this->previewTicket" />
                            </div>
                        </x-ui.card>

                        <x-ui.card title="Terms and conditions" subtitle="Read and accept these to submit your request."
                            class="overflow-hidden">
                            <div class="max-h-80 overflow-y-auto rounded-field border border-base-300 p-4"
                                tabindex="0" aria-label="Terms and conditions">
                                <x-terms_and_conditions />
                            </div>

                            <div class="mt-5 rounded-field border border-base-300 p-4">
                                <x-ui.checkbox wire:model.live="agreeToTerms" required>
                                    <x-slot:label>
                                        <span class="text-sm font-medium text-base-content">
                                            I have read and agree to the terms and conditions above
                                        </span>
                                    </x-slot:label>
                                </x-ui.checkbox>
                                <p class="mt-1.5 ps-8 text-xs text-base-content/70">
                                    Your organization is responsible for following all university policies during the event.
                                </p>
                            </div>
                        </x-ui.card>
                    </div>
                @endif

                {{-- Step navigation: sticky so the next action is always within reach (Fitts's Law) --}}
                <div
                    class="sticky bottom-0 z-10 -mx-4 mt-6 flex items-center justify-between gap-3 border-t border-base-300 bg-base-200/95 px-4 py-3 sm:mx-0 sm:rounded-box sm:border sm:bg-base-100 sm:px-5">
                    <div>
                        @if ($currentStep > 1)
                            <x-ui.button label="Back" icon="o-arrow-left" wire:click="previousStep" class="btn-ghost"
                                spinner :disabled="$isProcessing" />
                        @endif
                    </div>
                    <p class="hidden text-sm tabular-nums text-base-content/70 sm:block">
                        Step {{ $currentStep }} of {{ $totalSteps }}
                    </p>
                    @if ($currentStep < $totalSteps)
                        <x-ui.button wire:click="nextStep" class="btn-primary" :disabled="$isProcessing" spinner
                            icon-right="o-arrow-right">
                            <span class="sm:hidden">Next</span>
                            <span class="hidden sm:inline">Next: {{ $stepLabels[$currentStep + 1] }}</span>
                        </x-ui.button>
                    @else
                        <x-ui.button :label="$amendingTicketId ? 'Resubmit ticket' : 'Submit ticket'" icon="o-paper-airplane" wire:click="save" class="btn-primary"
                            :disabled="!$agreeToTerms || $isProcessing" spinner />
                    @endif
                </div>
            </form>
        </div>
    </div>


    @script
        <script>
            // ─── Attachment preview / download ────────────────────────────────────────
            $wire.on('open-attachment-preview', ({
                url
            }) => {
                if (url) window.open(url, '_blank');
            });

            $wire.on('download-attachment', ({
                url,
                filename
            }) => {
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

                showResumeModal({
                    draft_id: data.draftId,
                    savedAt: data.savedAt
                });
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

                setTimeout(() => {
                    isSubmitting = false;
                }, 3000);
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

                const savedDate = new Date(ptr.savedAt);
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
                dateText.className = 'text-sm opacity-70 mb-4';
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
                spinnerText.textContent = 'Loading draft…';

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
                const loadBtn = document.getElementById('loadDraftBtn');
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
                    Livewire.dispatch('load-draft', {
                        data: {
                            draft_id: ptr.draft_id
                        }
                    });

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
                        showResumeModal({
                            draft_id: $wire.draftId,
                            savedAt: $wire.draftSavedAt
                        });
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
    <div id="autosave-indicator" role="status"
        class="hidden fixed bottom-24 right-4 z-40 rounded-field border border-base-300 bg-base-100 px-3 py-2 shadow-float">
        <span class="flex items-center gap-2 text-sm text-base-content">
            <x-ui.icon name="o-check-circle" class="size-4 text-success" aria-hidden="true" />
            Draft saved
        </span>
    </div>
</div>
