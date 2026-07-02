<?php

namespace App\Livewire\StudentOrg;

use App\Livewire\Concerns\HandlesDraftAttachmentPreviews;
use App\Models\Attachment;
use App\Models\ContentSection;
use App\Models\Event_Type;
use App\Models\Fund_Sources;
use App\Models\Ticket;
use App\Models\TicketDraft;
use App\Models\TicketDraftAttachment;
use App\Models\User;
use App\Models\Venue;
use App\Notifications\TicketSubmittedNotification;
use App\Services\TransactionLogService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class SubmitTicket extends Component
{
    use HandlesDraftAttachmentPreviews;

    #[Title('Submit Ticket - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]
    // Step tracking
    public $currentStep = 1;

    public $totalSteps = 6;

    #[Validate('required|accepted')]
    public $agreeToTerms = false;

    // Step 1: Organization
    public $organizationName = '';

    public $organizationCourse = '';

    public $adviser = '';

    public $contactEmail = '';

    public $proponentName = '';

    public $proponentPosition = '';

    public $proponent_contact = '';

    #[Validate('required', message: 'The adviser contact number is required.')]
    #[Validate('numeric', message: 'The adviser contact number must contain only numbers.')]
    #[Validate('digits:11', message: 'The adviser contact number must be 11 digits.')]
    public $adviser_contact = '09';

    #[Validate('required|boolean')]
    public $is_amended = false;

    // Step 2: Event Details
    #[Validate('required|string|max:255|min:5|regex:/^[^0-9][a-z0-9\\s]*$/i')]
    public $eventTitle = '';

    #[Validate('required|max:5000|min:20')]
    public $eventDescription = '';

    #[Validate('required|integer|exists:event__types,event_type_id')]
    public $eventType = 1;

    #[Validate('required', message: 'The number of PLV participants is required.')]
    #[Validate('integer', message: 'The number of PLV participants must be an integer.')]
    #[Validate('min:1', message: 'The number of PLV participants must be at least 1.')]
    #[Validate('max:100000', message: 'The number of PLV participants must be less than 100000.')]
    public $expectedPLVParticipants = 0;

    #[Validate('nullable')]
    #[Validate('integer', message: 'The number of non-PLV participants must be an integer.')]
    #[Validate('min:0', message: 'The number of non-PLV participants must be at least 0.')]
    #[Validate('max:100000', message: 'The number of non-PLV participants must be less than 100000.')]
    public $expectedNonPLVParticipants = 0;

    // Step 3: Schedule & Venue
    #[Validate('required', message: 'The event start date is required.')]
    #[Validate('date', message: 'The event start date must be a valid date.')]
    #[Validate('after_or_equal:today', message: 'The event start date must be today or later.')]
    public $eventStartDate = '';

    #[Validate('required', message: 'The event end date is required.')]
    #[Validate('date', message: 'The event end date must be a valid date.')]
    #[Validate('after_or_equal:eventStartDate', message: 'The event end date must be on or after the start date.')]
    public $eventEndDate = '';

    #[Validate('required', message: 'The event start time is required.')]
    #[Validate('date_format:H:i', message: 'The event start time must be in HH:MM format.')]
    #[Validate('after_or_equal:00:01', message: 'Event start time must be at or after 12:01 AM.')]
    public $eventStartTime = '';

    #[Validate('required', message: 'The event end time is required.')]
    #[Validate('date_format:H:i', message: 'The event end time must be in HH:MM format.')]
    #[Validate('before_or_equal:21:00', message: 'Event end time must be at or before 9:00 PM.')]
    public $eventEndTime = '';

    public $venues = [];

    public $preferredVenue;

    public $preferredVenueOther;

    public $alternativeVenue;

    public $alternativeVenueOther;

    #[Validate('nullable|string|max:2000')]
    public $specialRequirements = '';

    #[Validate('required|boolean')]
    public $is_oc = false;

    #[Validate('nullable|string|max:2000')]
    public $oc_accommodation = '';

    #[Validate('required_if:is_oc,true')]
    #[Validate('nullable')]
    #[Validate('string')]
    #[Validate('in:in-house,outsourced')]
    public $oc_tsp = null;

    #[Validate('required_if:oc_tsp,outsourced')]
    #[Validate('nullable')]
    #[Validate('string')]
    #[Validate('max:255')]
    #[Validate('min:2')]
    public $oc_driver_name = '';

    #[Validate('required_if:oc_tsp,outsourced')]
    #[Validate('nullable')]
    #[Validate('string')]
    #[Validate('max:255')]
    #[Validate('regex:/^[0-9\s\-\+\(\)]+$/')]
    public $oc_driver_contact_number = '';

    #[Validate('required_if:oc_tsp,outsourced')]
    #[Validate('nullable')]
    #[Validate('string')]
    #[Validate('max:255')]
    #[Validate('min:2')]
    public $oc_transportation_type = '';

    #[Validate('required_if:oc_tsp,outsourced')]
    #[Validate('nullable')]
    #[Validate('string')]
    #[Validate('max:255')]
    #[Validate('regex:/^[A-Z0-9\-\s]+$/i')]
    public $oc_vehicle_plate_number = '';

    // Step 4: Budget
    #[Validate('required|numeric|min:0|max:999999999.99')]
    public $totalBudget = 0.00;

    #[Validate('required|integer|exists:fund__sources,source_id')]
    public $fundingSource = '';

    #[Validate('nullable|string|max:2000')]
    public $budgetBreakdown = '';

    #[Validate('required|string|in:true,false')]
    public $igp_requested = '';

    #[Validate('required_if:igp_requested,true')]
    #[Validate('nullable')]
    #[Validate('string')]
    #[Validate('max:2000')]
    #[Validate('min:10')]
    public $igp_details = '';

    // Step 5: Attachments
    public $attachments = [];

    // Validation is handled entirely in updatedNewAttachments() — do NOT add
    // #[Validate] here. Livewire v3 applies property-level #[Validate] rules
    // at the /upload-file XHR endpoint (before the component lifecycle runs).
    // The 'array' rule would reject every single-file upload with a 422 because
    // Livewire sends one UploadedFile at a time, not an array.
    public $newAttachments = [];

    #[Validate('nullable|string|max:2000')]
    public $additionalNotes = '';

    // UI
    public $isProcessing = false;

    /**
     * Incremented on every upload attempt (success or failure).
     * The blade binds this to wire:key on the file input so Livewire
     * destroys and recreates the DOM element, clearing any stale
     * browser upload state that would otherwise leave the input stuck.
     */
    public int $uploadKey = 0;

    /** Primary key of the active ticket_drafts row; broadcast to JS as draft pointer. */
    public ?int $draftId = null;

    /** ISO timestamp of when the active draft was last updated; broadcast to JS. */
    public ?string $draftSavedAt = null;

    use Toast;
    // To prevent multiple submissions

    use WithFileUploads;

    public function nextStep()
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        try {
            $this->validateCurrentStep();

            if ($this->currentStep < $this->totalSteps) {
                $this->currentStep++;
                $this->dispatch('step-changed');
            }
        } finally {
            $this->isProcessing = false;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->dispatch('step-changed');
        }
    }

    public function goToStep($step)
    {
        if ($step >= 1 && $step <= $this->totalSteps) {
            // Only allow going to completed steps or the next step
            if ($step <= $this->currentStep || $step === $this->currentStep + 1) {
                // If moving forward, validate current step
                if ($step > $this->currentStep) {
                    try {
                        $this->validateCurrentStep();
                    } catch (ValidationException $e) {
                        // Re-throw to show validation errors
                        throw $e;
                    }
                }

                $this->currentStep = $step;
                $this->dispatch('step-changed');
            }
        }
    }

    protected function getCurrentStepRules(): array
    {
        return match ($this->currentStep) {
            1 => [
                'adviser_contact' => 'required|string|digits:11|regex:/^[0-9]+$/',
                'is_amended' => 'required|boolean',
            ],
            2 => [
                'eventTitle' => 'required|string|max:255|min:5',
                'eventDescription' => 'required|string|max:2000|min:20',
                'eventType' => 'required|integer|exists:event__types,event_type_id',
                'expectedPLVParticipants' => 'required|integer|min:1|max:100000',
                'expectedNonPLVParticipants' => 'nullable|integer|min:0|max:100000',
            ],
            3 => [
                'eventStartDate' => 'required|date|after_or_equal:today',
                'eventEndDate' => 'required|date|after_or_equal:eventStartDate',
                'eventStartTime' => ['required', 'date_format:H:i', 'after_or_equal:00:01'],
                'eventEndTime' => [
                    'required',
                    'date_format:H:i',
                    'before_or_equal:21:00',
                    // Only enforce after-start-time when both events fall on the same date.
                    // When end date is a later day, any valid time is acceptable.
                    function ($attribute, $value, $fail) {
                        if (
                            $this->eventStartDate &&
                            $this->eventEndDate &&
                            $this->eventStartDate === $this->eventEndDate &&
                            $value <= $this->eventStartTime
                        ) {
                            $fail('The event end time must be after the start time when the event is on the same day.');
                        }
                    },
                ],
                'preferredVenue' => ['required', function ($attribute, $value, $fail) {
                    if ($value !== 'other' && ! Venue::where('venue_id', $value)->exists()) {
                        $fail('The selected venue is invalid.');
                    }
                }],
                'preferredVenueOther' => $this->preferredVenue === 'other' ? 'required|string|max:255|min:3' : 'nullable',
                'alternativeVenue' => ['nullable', function ($attribute, $value, $fail) {
                    if ($value && $value !== 'other' && ! Venue::where('venue_id', $value)->exists()) {
                        $fail('The selected alternative venue is invalid.');
                    }
                }],
                'alternativeVenueOther' => $this->alternativeVenue === 'other' ? 'required|string|max:255|min:3' : 'nullable',
                'specialRequirements' => 'nullable|string|max:2000',
                'is_oc' => 'required|boolean',
                'oc_accommodation' => $this->is_oc ? 'nullable|string|max:2000' : 'nullable',
                'oc_tsp' => $this->is_oc ? 'required|string|in:in-house,outsourced' : 'nullable',
                'oc_driver_name' => ($this->is_oc && $this->oc_tsp === 'outsourced') ? 'required|string|max:30|min:2' : 'nullable',
                'oc_driver_contact_number' => ($this->is_oc && $this->oc_tsp === 'outsourced') ? 'required|string|max:11|regex:/^[0-9\s\-\+\(\)]+$/' : 'nullable',
                'oc_transportation_type' => ($this->is_oc && $this->oc_tsp === 'outsourced') ? 'nullable|string|max:50|min:2' : 'nullable',
                'oc_vehicle_plate_number' => ($this->is_oc && $this->oc_tsp === 'outsourced') ? 'nullable|string|max:10|regex:/^[A-Z0-9\-\s]+$/i' : 'nullable',
            ],
            4 => [
                'totalBudget' => 'required|numeric|min:0|max:999999999.99',
                'fundingSource' => [
                    'required',
                    'integer',
                    'exists:fund__sources,source_id',
                    function ($attribute, $value, $fail) {
                        $naFundSource = Fund_Sources::where('source_name', 'N/A')->first();
                        if ($naFundSource && (int) $value === (int) $naFundSource->source_id) {
                            $fail('Please select a valid funding source.');
                        }
                    }
                ],
                'igp_requested' => 'required|string|in:true,false',
                'budgetBreakdown' => 'nullable|string|max:2000',
                'igp_details' => $this->igp_requested === 'true' ? 'required|string|max:2000|min:10' : 'nullable',
            ],
            5 => [
                'additionalNotes' => 'nullable|string|max:2000',
                'newAttachments' => 'nullable|array|max:25',
                'newAttachments.*' => 'file|max:10240|mimes:pdf',
            ],
            6 => [
                'agreeToTerms' => 'required|accepted',
            ],
            default => [],
        };
    }

    protected function isOthersVenue($venueId): bool
    {
        if (! $venueId) {
            return false;
        }

        $venue = Venue::find($venueId);

        return $venue && $venue->venue_name === 'Others (Please Specify)';
    }


    /**
     * When start date changes, clear end date if it precedes the new start date.
     * This prevents stale invalid end dates from silently passing validation.
     */
    public function updatedEventStartDate($value): void
    {
        if ($this->eventEndDate && $this->eventEndDate < $value) {
            $this->eventEndDate = '';
        }
    }

    /**
     * When start time changes, clear end time if they share the same date
     * and the end time is no longer after the start time.
     */
    public function updatedEventStartTime($value): void
    {
        if ($this->eventEndTime && $this->eventStartDate === $this->eventEndDate && $this->eventEndTime <= $value) {
            $this->eventEndTime = '';
        }
    }

    protected function validateCurrentStep()
    {
        $rules = $this->getCurrentStepRules();

        if (! empty($rules)) {
            $this->validate($rules, [
                // Step 1 — Organization
                'adviser_contact.required'              => 'Please enter the adviser\'s contact number.',
                'adviser_contact.digits'                => 'The adviser\'s contact number must be exactly 11 digits.',
                'adviser_contact.size'                  => 'The adviser\'s contact number must be exactly 11 digits.',
                'adviser_contact.regex'                 => 'The adviser\'s contact number must contain numbers only.',

                // Step 2 — Event Details
                'eventTitle.required'                   => 'Please provide a title for your event.',
                'eventTitle.min'                        => 'The event title must be at least 5 characters long.',
                'eventTitle.max'                        => 'The event title may not exceed 255 characters.',
                'eventDescription.required'             => 'Please describe your event.',
                'eventDescription.min'                  => 'The event description must be at least 20 characters long.',
                'eventDescription.max'                  => 'The event description may not exceed 2,000 characters.',
                'eventType.required'                    => 'Please select an event type.',
                'eventType.exists'                      => 'The selected event type is not valid.',
                'expectedPLVParticipants.required'      => 'Please enter the expected number of PLV participants.',
                'expectedPLVParticipants.integer'       => 'The PLV participant count must be a whole number.',
                'expectedPLVParticipants.min'           => 'There must be at least 1 PLV participant.',
                'expectedPLVParticipants.max'           => 'The PLV participant count seems too high. Please verify.',
                'expectedNonPLVParticipants.integer'    => 'The non-PLV participant count must be a whole number.',
                'expectedNonPLVParticipants.min'        => 'The non-PLV participant count cannot be negative.',
                'expectedNonPLVParticipants.max'        => 'The non-PLV participant count seems too high. Please verify.',

                // Step 3 — Schedule & Venue
                'eventStartDate.required'               => 'Please select a start date for your event.',
                'eventStartDate.date'                   => 'The start date is not a valid date.',
                'eventStartDate.after_or_equal'         => 'The event start date must be today or a future date.',
                'eventEndDate.required'                 => 'Please select an end date for your event.',
                'eventEndDate.date'                     => 'The end date is not a valid date.',
                'eventEndDate.after_or_equal'           => 'The event end date must be on or after the start date.',
                'eventStartTime.required'               => 'Please enter the event start time.',
                'eventStartTime.date_format'            => 'The start time must be in a valid HH:MM format.',
                'eventStartTime.after_or_equal'         => 'Event start time must be at or after 12:01 AM.',
                'eventEndTime.required'                 => 'Please enter the event end time.',
                'eventEndTime.date_format'              => 'The end time must be in a valid HH:MM format.',
                'eventEndTime.before_or_equal'          => 'Event end time must be at or before 9:00 PM.',
                'eventEndTime.after'                    => 'The end time must be after the start time.',
                'preferredVenue.required'               => 'Please select a preferred venue.',
                'preferredVenueOther.required'          => 'Please specify the preferred venue name.',
                'preferredVenueOther.min'               => 'The venue name must be at least 3 characters.',
                'preferredVenueOther.max'               => 'The venue name may not exceed 255 characters.',
                'alternativeVenueOther.required'        => 'Please specify the alternative venue name.',
                'alternativeVenueOther.min'             => 'The alternative venue name must be at least 3 characters.',
                'alternativeVenueOther.max'             => 'The alternative venue name may not exceed 255 characters.',
                'specialRequirements.max'               => 'Special requirements may not exceed 2,000 characters.',
                'is_oc.required'                        => 'Please indicate whether this is an off-campus event.',
                'oc_tsp.required'                       => 'Please select a transportation service option.',
                'oc_tsp.in'                             => 'The selected transportation option is not valid.',
                'oc_driver_name.required'               => "Please enter the driver's full name.",
                'oc_driver_name.min'                    => "The driver's name must be at least 2 characters.",
                'oc_driver_name.max'                    => "The driver's name may not exceed 30 characters.",
                'oc_driver_contact_number.required'     => "Please enter the driver's contact number.",
                'oc_driver_contact_number.max'          => "The driver's contact number may not exceed 11 digits.",
                'oc_driver_contact_number.regex'        => "The driver's contact number may only contain digits.",
                'oc_transportation_type.min'            => 'The vehicle type must be at least 2 characters.',
                'oc_transportation_type.max'            => 'The vehicle type may not exceed 50 characters.',
                'oc_vehicle_plate_number.max'           => 'The plate number may not exceed 10 characters.',
                'oc_vehicle_plate_number.regex'         => 'The plate number may only contain letters, digits, dashes, and spaces.',

                // Step 4 — Budget
                'totalBudget.required'                  => 'Please enter the estimated total budget.',
                'totalBudget.numeric'                   => 'The budget must be a valid number.',
                'totalBudget.min'                       => 'The budget cannot be negative.',
                'totalBudget.max'                       => 'The budget amount entered seems too large. Please verify.',
                'fundingSource.required'                => 'Please select a funding source.',
                'fundingSource.exists'                  => 'The selected funding source is not valid.',
                'igp_requested.required'                => 'Please indicate whether an IGP is requested.',
                'igp_requested.in'                      => 'The IGP request selection is not valid.',
                'budgetBreakdown.max'                   => 'The budget breakdown may not exceed 2,000 characters.',
                'igp_details.required'                  => 'Please provide a brief description of the IGP.',
                'igp_details.min'                       => 'The IGP description must be at least 10 characters.',
                'igp_details.max'                       => 'The IGP description may not exceed 2,000 characters.',

                // Step 5 — Attachments
                'additionalNotes.max'                   => 'Additional notes may not exceed 2,000 characters.',
                'newAttachments.max'                    => 'You may not upload more than 25 files.',
                'newAttachments.*.file'                 => 'One or more uploaded items are not valid files.',
                'newAttachments.*.max'                  => 'Each file must not exceed 10 MB.',
                'newAttachments.*.mimes'                => 'Only PDF files are accepted.',

                // Step 6 — Terms
                'agreeToTerms.required'                 => 'You must agree to the terms and conditions to proceed.',
                'agreeToTerms.accepted'                 => 'You must agree to the terms and conditions to proceed.',
            ]);
        }

        // Custom time range validation (runs for step 3 or when validating all steps)
        // if (($this->currentStep === 3 || $this->currentStep === $this->totalSteps) && $this->eventStartTime && $this->eventEndTime) {
        //     try {
        //         $startTime = \Carbon\Carbon::createFromFormat('H:i', $this->eventStartTime);
        //         $endTime = \Carbon\Carbon::createFromFormat('H:i', $this->eventEndTime);
        //         $minTime = \Carbon\Carbon::createFromFormat('H:i', '00:01');
        //         $maxTime = \Carbon\Carbon::createFromFormat('H:i', '21:00');

        //         if ($startTime->lt($minTime)) {
        //             throw \Illuminate\Validation\ValidationException::withMessages([
        //                 'eventStartTime' => 'Event start time must be at or after 12:01 AM.',
        //             ]);
        //         }

        //         if ($endTime->gt($maxTime)) {
        //             throw \Illuminate\Validation\ValidationException::withMessages([
        //                 'eventEndTime' => 'Event end time must be at or before 9:00 PM.',
        //             ]);
        //         }
        //     } catch (\Carbon\Exceptions\InvalidFormatException $e) {
        //         // Invalid time format - let the regular validation handle it
        //     }
        // }
    }

    protected function validateAllSteps()
    {
        // Validate each step sequentially
        for ($step = 1; $step <= $this->totalSteps; $step++) {
            $originalStep = $this->currentStep;
            $this->currentStep = $step;

            try {
                $this->validateCurrentStep();
            } catch (ValidationException $e) {
                // Restore original step before re-throwing
                $this->currentStep = $originalStep;
                throw $e;
            }
        }

        // Restore original step
        $this->currentStep = $this->totalSteps;
    }

    public function getPreviewTicketProperty()
    {
        $currentUser = auth()->user();
        $currentUserinfo = $currentUser->studentOrganization;

        // Create a temporary ticket object for preview
        $ticket = new Ticket;
        $ticket->user = $currentUser;
        $ticket->title = $this->eventTitle;
        $ticket->description = $this->eventDescription;
        $ticket->plv_participants = $this->expectedPLVParticipants;
        $ticket->external_participants = $this->expectedNonPLVParticipants;
        $ticket->total_participants = $this->expectedParticipants;
        $ticket->proponent_contact = $this->proponent_contact;
        $ticket->adviser_contact = $this->adviser_contact;
        $ticket->date_from = $this->eventStartDate;
        $ticket->date_to = $this->eventEndDate;
        $ticket->time_from = $this->eventStartTime;
        $ticket->time_to = $this->eventEndTime;
        $ticket->venue_requested = $this->preferredVenue;
        $ticket->venue_other = $this->preferredVenueOther;
        $ticket->alternate_venue = $this->alternativeVenue;
        $ticket->alternate_venue_other = $this->alternativeVenueOther;
        $ticket->special_requirements = $this->specialRequirements;
        $ticket->estimated_budget = $this->totalBudget;
        $ticket->fund_source_id   = (int) $this->fundingSource;
        $ticket->budget_breakdown = $this->budgetBreakdown;
        $ticket->igp_requested = $this->igp_requested === 'true';
        $ticket->igp_details = $this->igp_details;
        $ticket->oc_accommodation = $this->oc_accommodation;
        $ticket->oc_tsp = $this->oc_tsp;
        $ticket->oc_driver_name = $this->oc_driver_name;
        $ticket->oc_transportation_type = $this->oc_transportation_type;
        $ticket->oc_vehicle_plate_number = $this->oc_vehicle_plate_number;
        $ticket->oc_driver_contact_number = $this->oc_driver_contact_number;
        $ticket->additional_notes = $this->additionalNotes;

        // Create temporary attachment objects for preview
        // Supports both DB-backed array records and legacy TemporaryUploadedFile objects.
        $previewAttachments = collect($this->attachments)->map(function ($file, int $index) {
            $attachment = new Attachment;

            if (is_array($file)) {
                // DB-backed draft attachment (persisted to disk+DB)
                $attachment->file_name = $file['file_name'];
                $attachment->file_type = $file['file_type'];
                $attachment->file_path = null;
            } else {
                // Legacy TemporaryUploadedFile (should not normally occur after migration)
                $attachment->file_name = $file->getClientOriginalName();
                $attachment->file_type = $file->getMimeType();
                $attachment->file_path = null;
            }

            $attachment->setAttribute('preview_upload_index', $index);

            return $attachment;
        });

        // Load relationships
        $ticket->setRelation('eventType', Event_Type::find($this->eventType));
        $ticket->setRelation('fundSource', Fund_Sources::find($this->fundingSource));
        $ticket->setRelation('attachments', $previewAttachments);

        // Load venue relationships
        if ($this->preferredVenue) {
            $ticket->setRelation('preferredVenueRelation', Venue::find($this->preferredVenue));
        }
        if ($this->alternativeVenue) {
            $ticket->setRelation('alternativeVenueRelation', Venue::find($this->alternativeVenue));
        }

        return $ticket;
    }

    public function mount()
    {
        // Eagerly load both relationships so null-access on nested objects never crashes mount().
        // Without eager loading, `->course` is a second lazy query; if course_id is null the
        // subsequent `->course_name` read throws a silent fatal that wipes all autofill values.
        $currentUser = auth()->user()->load(['studentOrganization.course', 'position']);

        $currentUserinfo     = $currentUser->studentOrganization;
        $currentUserPosition = $currentUser->position;

        $this->organizationName    = $currentUserinfo?->org_code ?? '';
        $this->adviser             = $currentUserinfo?->adviser_name ?? '';
        $this->contactEmail        = $currentUser->email ?? '';
        $this->proponentName       = $currentUser->name ?? '';
        $this->organizationCourse  = $currentUserinfo?->course?->course_name ?? 'Non Academic Org';
        $this->proponentPosition   = $currentUserPosition?->position_name ?? '';
        $this->proponent_contact   = $currentUser->phone ?? '';
        $this->venues = Venue::where('is_active', true)->get();

        // Resolve the "N/A" fund source and ensure it exists in the table.
        // firstOrCreate guarantees this works even if the seeder hasn't been re-run.
        $naFundSource = Fund_Sources::firstOrCreate(['source_name' => 'N/A']);
        // Bust the cached list so the new record appears immediately if it was just created.
        Cache::forget('fund_sources_all');
        $this->fundingSource = $naFundSource->source_id;

        // Load resubmit data if available (may override fundingSource with a saved value)
        if (session()->has('resubmit_ticket')) {
            $data = session()->pull('resubmit_ticket');
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        } else {
            // No resubmit session — check for an existing server-side draft for this user.
            // Dispatches a `draft-found` browser event so the JS layer can show the resume modal.
            $existingDraft = TicketDraft::where('user_id', auth()->id())->first();
            if ($existingDraft) {
                $this->draftId = $existingDraft->id;
                $this->draftSavedAt = $existingDraft->updated_at->toISOString();
                $this->dispatch('draft-found',
                    draftId: $existingDraft->id,
                    savedAt: $existingDraft->updated_at->toISOString()
                );
            }
        }

        // Trigger validation for adviser_contact
        try {
            $this->validateOnly('adviser_contact', [
                'adviser_contact' => 'required|string|digits:11|regex:/^[0-9]+$/',
            ], [
                'adviser_contact.digits' => 'The adviser contact number must be 11 digits.',
                'adviser_contact.regex' => 'The adviser contact number must contain only numbers.',
            ]);
        } catch (ValidationException $e) {
            // Validation error will be displayed
        }
    }

    public function save()
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;
        $currentUser = auth()->user();
        $currentUserinfo = $currentUser->studentOrganization;
        $ticketCode = null;

        // Rate limiting: max 3 ticket submissions per minute per user
        $rateLimitKey = 'ticket-submit:'.$currentUser->user_id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $this->isProcessing = false;
            $this->toast(
                type: 'warning',
                title: 'Too Many Attempts',
                description: "Please wait {$seconds} seconds before submitting again.",
                position: 'toast-top toast-end',
                icon: 'o-clock',
                css: 'alert-warning',
                noProgress: true,
                timeout: 5000,
            );

            return;
        }
        RateLimiter::hit($rateLimitKey, 60); // 60 second decay

        try {
            // Validate all steps before submission
            $this->validateAllSteps();

            if (! $currentUserinfo || ! $currentUserinfo->org_code) {
                throw new Exception('Organization information is missing. Please contact support.');
            }

            $lock = Cache::lock("lock:ticket:submit:{$currentUser->user_id}", 10);
            if (!$lock->get()) {
                $this->isProcessing = false;
                $this->warning('Submission is already in progress.');
                return;
            }

            \DB::beginTransaction();
            $orgCode = $currentUserinfo->org_code;
            $lastTicket = Ticket::lockForUpdate()
                ->where('ticket_number', 'LIKE', "TKT-{$orgCode}-%")
                ->orderByRaw('CAST(SUBSTRING(ticket_number, LOCATE(\'-\', ticket_number, 5) + 1) AS UNSIGNED) DESC')
                ->first();

            $nextNumber = $lastTicket
                ? ((int) substr(strrchr($lastTicket->ticket_number, '-'), 1)) + 1
                : 1;

            // Generate unique ticket number with locking
            $ticketCode = "TKT-{$orgCode}-".str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Helper function to convert empty strings to null for nullable fields
            $nullIfEmpty = fn ($value) => ($value === '' || $value === null) ? null : $value;
            $nullIfEmptyInt = fn ($value) => ($value === '' || $value === null) ? null : (int) $value;

            $ticket = Ticket::create([
                'user_id' => $currentUser->user_id,
                'ticket_number' => $ticketCode,
                'event_type_id' => (int) $this->eventType,
                'plv_participants' => (int) $this->expectedPLVParticipants,
                'external_participants' => $nullIfEmptyInt($this->expectedNonPLVParticipants),
                'title' => $this->eventTitle,
                'description' => $this->eventDescription,
                'proponent_contact' => $this->proponent_contact,
                'adviser_contact' => $this->adviser_contact,
                'igp_requested' => $this->igp_requested === 'true',
                'igp_details' => $nullIfEmpty($this->igp_details),
                'oc_accommodation' => $nullIfEmpty($this->oc_accommodation),
                'oc_tsp' => $nullIfEmpty($this->oc_tsp),
                'oc_driver_name' => $nullIfEmpty($this->oc_driver_name),
                'oc_transportation_type' => $nullIfEmpty($this->oc_transportation_type),
                'oc_vehicle_plate_number' => $nullIfEmpty($this->oc_vehicle_plate_number),
                'oc_driver_contact_number' => $nullIfEmpty($this->oc_driver_contact_number),
                'estimated_budget' => $this->totalBudget ? (float) $this->totalBudget : null,
                'budget_breakdown' => $nullIfEmpty($this->budgetBreakdown),
                'venue_requested' => $this->preferredVenue === 'other' ? null : $nullIfEmptyInt($this->preferredVenue),
                'venue_other' => $this->preferredVenue === 'other' ? $this->preferredVenueOther : $nullIfEmpty($this->preferredVenueOther),
                'alternate_venue' => $this->alternativeVenue === 'other' ? null : $nullIfEmptyInt($this->alternativeVenue),
                'alternate_venue_other' => $this->alternativeVenue === 'other' ? $this->alternativeVenueOther : $nullIfEmpty($this->alternativeVenueOther),
                'special_requirements' => $nullIfEmpty($this->specialRequirements),
                'total_participants' => (int) $this->expectedPLVParticipants + (int) ($this->expectedNonPLVParticipants ?: 0),
                'fund_source_id' => (int) $this->fundingSource,
                'date_from' => $this->eventStartDate,
                'date_to' => $this->eventEndDate,
                'time_from' => $this->eventStartTime,
                'time_to' => $this->eventEndTime,
                'additional_notes' => $nullIfEmpty($this->additionalNotes),
                'status' => $this->is_amended ? 'amended' : 'received',
            ]);

            // Handle file attachments
            // Supports DB-backed draft attachments (array) and legacy TemporaryUploadedFile objects.
            if (! empty($this->attachments)) {
                foreach ($this->attachments as $fileData) {
                    if (is_array($fileData)) {
                        // DB-backed: move file from draft-attachments/ to tickets/ folder.
                        $newPath = "tickets/{$ticket->ticket_id}/attachments/" . basename($fileData['file_path']);
                        Storage::move($fileData['file_path'], $newPath);

                        Attachment::create([
                            'ticket_id' => $ticket->ticket_id,
                            'file_name' => $fileData['file_name'],
                            'file_path' => $newPath,
                            'file_type' => $fileData['file_type'],
                            'file_size' => $fileData['file_size'],
                        ]);
                    } else {
                        // Legacy TemporaryUploadedFile fallback (pre-migration users)
                        $originalName = $fileData->getClientOriginalName();
                        $filename     = time() . '_' . uniqid() . '_' . $originalName;
                        $path = $fileData->storeAs(
                            "tickets/{$ticket->ticket_id}/attachments",
                            $filename,
                            config('filesystems.default')
                        );

                        Attachment::create([
                            'ticket_id' => $ticket->ticket_id,
                            'file_name' => $originalName,
                            'file_path' => $path,
                            'file_type' => $fileData->getMimeType(),
                            'file_size' => $fileData->getSize(),
                        ]);
                    }
                }
            }

            // Log transaction
            TransactionLogService::logTicketOperation('created', $ticket);

            // Notify OSA admins about the new ticket
            $osaUsers = User::where('role_id', User::getRoleId('osa'))->get();
            foreach ($osaUsers as $osaUser) {
                $osaUser->notify(new TicketSubmittedNotification($ticket));
            }

            \DB::commit();

            // Remove DB draft now that a real ticket record exists.
            if ($this->draftId) {
                TicketDraft::where('id', $this->draftId)
                           ->where('user_id', $currentUser->user_id)
                           ->delete();
                $this->draftId = null;
                $this->draftSavedAt = null;
            }

            // Clear draft BEFORE showing success message
            $this->dispatch('clear-draft-immediate');

            // Short delay to ensure draft clearing completes before redirect
            usleep(100000); // 100ms delay

            // Clear related caches
            \App\Services\Cache\DashboardCacheService::clearAllDashboards();
            \App\Services\Cache\EventCacheService::clearRequestLists();

            $this->toast(
                type: 'success',
                title: 'Ticket Created!',
                description: "Your ticket {$ticketCode} has been submitted successfully.",
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                css: 'alert-success',
                timeout: 3000,
                redirectTo: route('student-org.dashboard')
            );
        } catch (ValidationException $e) {
            \DB::rollBack();
            $this->isProcessing = false;
            \Log::error('Ticket submission failed', [
                'user_id' => $currentUser->user_id ?? null,
                'step' => $this->currentStep,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Show user-friendly error message
            $errorMessage = config('app.debug')
                ? $e->getMessage()
                : 'Your data has been preserved. Please try again.';

            $this->toast(
                type: 'error',
                title: 'Submission Failed',
                description: $errorMessage,
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert-error',
                timeout: 5000,
                redirectTo: null
            );
            // Re-throw validation exceptions to show field-specific errors
            throw $e;
        } catch (Exception $e) {
            \DB::rollBack();

            // Log with full context
            \Log::error('Ticket submission failed', [
                'user_id' => $currentUser->user_id ?? null,
                'step' => $this->currentStep,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Show user-friendly error message
            $errorMessage = config('app.debug')
                ? $e->getMessage()
                : 'Your data has been preserved. Please try again.';

            $this->toast(
                type: 'error',
                title: 'Submission Failed',
                description: $errorMessage,
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert-error',
                timeout: 5000,
                redirectTo: null
            );
        } finally {
            $this->isProcessing = false;
            if (isset($lock)) {
                $lock->release();
            }
        }
    }

    public function loadDraft($draftData)
    {
        // DB-backed path: JS passes { draft_id: N }
        if (isset($draftData['draft_id'])) {
            $draft = TicketDraft::with('attachments')
                ->where('id', $draftData['draft_id'])
                ->where('user_id', auth()->id())
                ->first();

            if (! $draft) {
                $this->dispatch('draft-loaded');
                return;
            }

            // Fields sourced from the user's profile are intentionally excluded here
            // so that mount()'s fresh values are never clobbered by stale draft data.
            $profileFields = [
                'organizationName', 'organizationCourse', 'adviser',
                'contactEmail', 'proponentName', 'proponentPosition', 'proponent_contact',
            ];

            foreach ($draft->data as $key => $value) {
                if (property_exists($this, $key)
                    && ! in_array($key, ['newAttachments', 'attachments', 'isProcessing', 'draftId'])
                    && ! in_array($key, $profileFields)) {
                    $this->{$key} = $value;
                }
            }

            $this->currentStep = $draft->current_step;
            $this->draftId     = $draft->id;
            $this->draftSavedAt = $draft->updated_at->toISOString();
            // Restore attachment list from DB records (each is an array representation)
            $this->attachments = $draft->attachments->toArray();

            $this->dispatch('draft-loaded');
            return;
        }

        // Legacy path — plain associative array payload (backward compat)
        $profileFields = [
            'organizationName', 'organizationCourse', 'adviser',
            'contactEmail', 'proponentName', 'proponentPosition', 'proponent_contact',
        ];

        foreach ($draftData as $key => $value) {
            if (property_exists($this, $key)
                && ! in_array($key, ['newAttachments', 'attachments', 'isProcessing'])
                && ! in_array($key, $profileFields)) {
                $this->{$key} = $value;
            }
        }

        $this->dispatch('draft-loaded');
    }

    #[Renderless]
    public function discardDraft()
    {
        // Remove DB draft and its files from disk
        if ($this->draftId) {
            $draft = TicketDraft::with('attachments')
                ->where('id', $this->draftId)
                ->where('user_id', auth()->id())
                ->first();

            if ($draft) {
                foreach ($draft->attachments as $a) {
                    Storage::delete($a->file_path);
                }
                $draft->delete(); // cascade removes ticket_draft_attachments rows
            }

            $this->draftId = null;
            $this->draftSavedAt = null;
        }

        $this->dispatch('clear-draft');
    }

    public function getExpectedParticipantsProperty()
    {
        return (int) $this->expectedPLVParticipants + (int) $this->expectedNonPLVParticipants;
    }

    public function removeAttachment($index)
    {
        $attachment = $this->attachments[$index] ?? null;

        // If this is a DB-backed draft attachment, clean up disk + DB record.
        if ($attachment && is_array($attachment) && isset($attachment['id'])) {
            $record = TicketDraftAttachment::find($attachment['id']);
            if ($record) {
                Storage::delete($record->file_path);
                $record->delete();
            }
        }

        array_splice($this->attachments, $index, 1);
    }

    public function updatedNewAttachments()
    {
        // Wrap a single UploadedFile into an array for consistent batch processing.
        // The S3 driver does not support simultaneous multi-file uploads.
        $files = $this->newAttachments;
        if (! is_array($files)) {
            $files = $files ? [$files] : [];
        }

        try {
            if (empty($files)) {
                return;
            }

            $this->newAttachments = $files;

            // Laravel's mimes rule is used instead of finfo_file because Livewire
            // temp files may be stored on S3 and are not locally accessible.
            $this->validate(
                ['newAttachments.*' => ['file', 'max:10240', 'mimes:pdf']],
                [
                    'newAttachments.*.file'  => 'The uploaded item could not be processed. Please try again.',
                    'newAttachments.*.max'   => 'The file exceeds the 10 MB size limit. Please reduce the file size and try again.',
                    'newAttachments.*.mimes' => 'This file type is not supported. Only PDF files are accepted.',
                ]
            );

            // Validation passed — persist files to disk and DB, then append to the component state.
            // Ensure a draft row exists so we can associate files with it.
            if (! $this->draftId) {
                $this->persistDraftToDb();
            }

            $draft = TicketDraft::findOrFail($this->draftId);

            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $storedName   = time() . '_' . uniqid() . '_' . $originalName;
                $path = $file->storeAs(
                    "draft-attachments/{$this->draftId}",
                    $storedName,
                    'local'
                );

                $record = TicketDraftAttachment::create([
                    'ticket_draft_id' => $draft->id,
                    'file_name'       => $originalName,
                    'file_path'       => $path,
                    'file_type'       => $file->getMimeType(),
                    'file_size'       => $file->getSize(),
                ]);

                // Store as array so Livewire can serialise it in component state
                $this->attachments[] = $record->toArray();
            }

        } catch (ValidationException $e) {
            // Extract the first human-readable error from the exception bag.
            $firstError = collect($e->errors())->flatten()->first()
                ?? 'The file could not be uploaded. Verify the file type and that it does not exceed 10 MB.';

            // Scope the error to the file input field so it renders inline
            // below the upload widget — without triggering the global error banner.
            // We intentionally do NOT re-throw here.
            $this->addError('newAttachments', $firstError);

            // Also surface a toast for immediate, unmissable feedback.
            $this->toast(
                type: 'error',
                title: 'Upload Rejected',
                description: $firstError,
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert-error',
                timeout: 7000,
            );
        } finally {
            // Increment uploadKey so the blade wire:key changes on re-render.
            // This forces Livewire to destroy and recreate the file input DOM
            // element, clearing any stale browser upload state that would
            // otherwise leave the input stuck on "uploading".
            $this->uploadKey++;
            $this->reset('newAttachments');
        }
    }

    /**
     * Upsert the current form state into ticket_drafts.
     * Excluded: file objects, upload state, runtime-only properties.
     */
    private function persistDraftToDb(): void
    {
        // Fields derived from the authenticated user's profile are excluded from the
        // draft payload — they are always re-populated from the live User record in
        // mount(), so storing them would cause stale data to overwrite fresh values
        // on draft resume (the original bug: org name / contact disappeared).
        $excluded = [
            'newAttachments', 'attachments', 'isProcessing', 'venues', 'uploadKey', 'draftId',
            // User-profile autofills — never persist to draft:
            'organizationName', 'organizationCourse', 'adviser',
            'contactEmail', 'proponentName', 'proponentPosition', 'proponent_contact',
        ];

        $data = collect($this->all())->except($excluded)->toArray();

        $draft = TicketDraft::updateOrCreate(
            ['user_id' => auth()->id()],
            ['current_step' => $this->currentStep, 'data' => $data]
        );

        // Expose the draft ID so the blade/JS layer can store a lightweight pointer in localStorage.
        if ($this->draftId !== $draft->id) {
            $this->draftId = $draft->id;
        }
        $this->draftSavedAt = $draft->updated_at->toISOString();
    }

    /**
     * Livewire lifecycle: fires after any public property is updated.
     * Persists form state to DB on every field change, then signals the JS auto-save indicator.
     */
    public function updated(string $property): void
    {
        // Skip file upload properties — they are handled in updatedNewAttachments()
        if (in_array($property, ['newAttachments', 'attachments', 'draftId'])) {
            return;
        }

        $this->persistDraftToDb();
        $this->dispatch('save-draft', $this->draftId);
    }

    #[On('load-draft')]
    public function handleLoadDraft($data)
    {
        $this->loadDraft($data);
    }

    #[On('discard-draft')]
    public function handleDiscardDraft()
    {
        $this->discardDraft();
    }

    /**
     * Check if the current step's required fields are filled
     * This is used to enable/disable the Next button
     */
    public function getIsCurrentStepCompleteProperty(): bool
    {
        return match ($this->currentStep) {
            1 => ! empty($this->adviser_contact) && strlen($this->adviser_contact) === 11,
            2 => ! empty($this->eventTitle)
                && ! empty($this->eventDescription)
                && ! empty($this->eventType)
                && ! empty($this->expectedPLVParticipants)
                && $this->expectedPLVParticipants > 0,
            3 => ! empty($this->eventStartDate)
                && ! empty($this->eventEndDate)
                && ! empty($this->eventStartTime)
                && ! empty($this->eventEndTime)
                && ! empty($this->preferredVenue)
                && (! $this->is_oc || ! empty($this->oc_tsp))
                && (! ($this->is_oc && $this->oc_tsp === 'outsourced') || (
                    ! empty($this->oc_driver_name)
                    && ! empty($this->oc_driver_contact_number)
                    && ! empty($this->oc_transportation_type)
                    && ! empty($this->oc_vehicle_plate_number)
                )),
            4 => ! empty($this->totalBudget)
                && ! empty($this->fundingSource)
                && ! empty($this->igp_requested)
                && ($this->igp_requested !== 'true' || ! empty($this->igp_details)),
            5 => true, // No strictly required fields in step 5
            6 => $this->agreeToTerms === true,
            default => false,
        };
    }

    /**
     * @return array<int, mixed>
     */
    protected function draftAttachmentFileList(): array
    {
        return $this->attachments;
    }

    public function getRequiredDocuments()
    {
        // Try to get from database first
        $eventType = Event_Type::find($this->eventType);
        if ($eventType && $eventType->documentary_requirements) {
            // Return as HTML string for rich text display
            return $eventType->documentary_requirements;
        }

        // Fallback to config
        return config("event_requirements.documents.{$this->eventType}", ['Pick an event type to see needed attachments.']);
    }

    public function render()
    {
        $ticketGuidelines = Cache::remember(
            'ticket_guidelines',
            3600,
            fn () => ContentSection::getActiveByType(ContentSection::TYPE_TICKET_GUIDELINES)->first()
        );

        return view('livewire.student-org.submit-ticket', [
            'eventTypes' => Cache::remember('event_types_all', 3600, fn () => Event_Type::all()),
            'fundSources' => Cache::remember('fund_sources_all', 3600, fn () => Fund_Sources::all()),
            'ticketGuidelines' => $ticketGuidelines,
        ]);
    }
}
