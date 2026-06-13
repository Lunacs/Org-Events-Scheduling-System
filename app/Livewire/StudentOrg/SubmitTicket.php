<?php

namespace App\Livewire\StudentOrg;

use App\Livewire\Concerns\HandlesDraftAttachmentPreviews;
use App\Models\Attachment;
use App\Models\ContentSection;
use App\Models\Event_Type;
use App\Models\Fund_Sources;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Venue;
use App\Notifications\TicketSubmittedNotification;
use App\Services\TransactionLogService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
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
    #[Validate('after:eventStartTime', message: 'The end time must be after the start time.')]
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

    #[Validate('nullable|array|max:25')]
    public $newAttachments = [];

    #[Validate('nullable|string|max:2000')]
    public $additionalNotes = '';

    // UI
    public $isProcessing = false;

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
                'eventTitle' => 'required|string|max:255|min:5|regex:/^[^0-9][a-z0-9\\s]*$/i',
                'eventDescription' => 'required|string|max:2000|min:20',
                'eventType' => 'required|integer|exists:event__types,event_type_id',
                'expectedPLVParticipants' => 'required|integer|min:1|max:100000',
                'expectedNonPLVParticipants' => 'nullable|integer|min:0|max:100000',
            ],
            3 => [
                'eventStartDate' => 'required|date|after_or_equal:today',
                'eventEndDate' => 'required|date|after_or_equal:eventStartDate',
                'eventStartTime' => ['required', 'date_format:H:i', 'after_or_equal:00:01'],
                'eventEndTime' => ['required', 'date_format:H:i', 'after:eventStartTime', 'before_or_equal:21:00'],
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
                'oc_driver_name' => ($this->is_oc && $this->oc_tsp === 'outsourced') ? 'required|string|max:255|min:2' : 'nullable',
                'oc_driver_contact_number' => ($this->is_oc && $this->oc_tsp === 'outsourced') ? 'required|string|max:255|regex:/^[0-9\s\-\+\(\)]+$/' : 'nullable',
                'oc_transportation_type' => ($this->is_oc && $this->oc_tsp === 'outsourced') ? 'required|string|max:255|min:2' : 'nullable',
                'oc_vehicle_plate_number' => ($this->is_oc && $this->oc_tsp === 'outsourced') ? 'required|string|max:255|regex:/^[A-Z0-9\-\s]+$/i' : 'nullable',
            ],
            4 => [
                'totalBudget' => 'required|numeric|min:0|max:999999999.99',
                'fundingSource' => 'required|integer|exists:fund__sources,source_id',
                'igp_requested' => 'required|string|in:true,false',
                'budgetBreakdown' => 'nullable|string|max:2000',
                'igp_details' => $this->igp_requested === 'true' ? 'required|string|max:2000|min:10' : 'nullable',
            ],
            5 => [
                'additionalNotes' => 'nullable|string|max:2000',
                'newAttachments' => 'nullable|array|max:25',
                'newAttachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx',
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

    // Auto-save draft on property update
    public function updated($property)
    {
        // Don't auto-save if currently processing submission
        if ($this->isProcessing) {
            return;
        }

        // Exclude certain properties from auto-save
        if (in_array($property, ['newAttachments', 'isProcessing'])) {
            return;
        }

        // Save draft every 2 seconds
        $this->dispatch('save-draft', [
            'step' => $this->currentStep,
            'data' => $this->all(),
        ]);
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
                'adviser_contact.size' => 'The adviser contact number must be 11 digits.',
                'expectedPLVParticipants.required' => 'The number of PLV participants is required.',
                'expectedNonPLVParticipants.required' => 'The number of non-PLV participants is required.',
                'expectedPLVParticipants.integer' => 'The number of PLV participants must be an integer.',
                'expectedNonPLVParticipants.integer' => 'The number of non-PLV participants must be an integer.',
                'expectedPLVParticipants.min' => 'The number of PLV participants must be at least 1.',
                'expectedNonPLVParticipants.min' => 'The number of non-PLV participants must be at least 0.',
                'expectedPLVParticipants.max' => 'The number of PLV participants must be less than 100000.',
                'expectedNonPLVParticipants.max' => 'The number of non-PLV participants must be less than 100000.',
                'eventStartTime.after_or_equal' => 'Event start time must be at or after 12:01 AM.',
                'eventEndTime.before_or_equal' => 'Event end time must be at or before 9:00 PM.',
                'eventEndTime.after' => 'The end time must be after the start time.',
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
        $previewAttachments = collect($this->attachments)->map(function ($file, int $index) {
            $attachment = new Attachment;
            $attachment->file_name = $file->getClientOriginalName();
            $attachment->file_type = $file->getMimeType();
            $attachment->file_path = null;
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
        $currentUser = auth()->user();
        $currentUserinfo = $currentUser->studentOrganization;
        $currentUserPosition = $currentUser->position;

        $this->organizationName = $currentUserinfo->org_code ?? '';
        $this->adviser = $currentUserinfo->adviser_name ?? '';
        $this->contactEmail = $currentUser->email ?? '';
        $this->proponentName = $currentUser->name ?? '';
        $this->organizationCourse = $currentUserinfo->course->course_name ?? 'Non Academic Org';
        $this->proponentPosition = $currentUserPosition->position_name ?? '';
        $this->proponent_contact = $currentUser->phone ?? '';
        $this->venues = Venue::where('is_active', true)->get();

        // Load resubmit data if available
        if (session()->has('resubmit_ticket')) {
            $data = session()->pull('resubmit_ticket');
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
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

            // Check for required organization data
            if (! $currentUserinfo || ! $currentUserinfo->org_code) {
                throw new Exception('Organization information is missing. Please contact support.');
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
            if (! empty($this->attachments)) {
                foreach ($this->attachments as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time().'_'.uniqid().'_'.$originalName;
                    $path = $file->storeAs(
                        "tickets/{$ticket->ticket_id}/attachments",
                        $filename,
                        config('filesystems.default')
                    );

                    Attachment::create([
                        'ticket_id' => $ticket->ticket_id,
                        'file_name' => $originalName,
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
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

            // Clear draft BEFORE showing success message
            $this->dispatch('clear-draft-immediate');

            // Short delay to ensure draft clearing completes before redirect
            usleep(100000); // 100ms delay

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
        }
    }

    public function loadDraft($draftData)
    {
        foreach ($draftData as $key => $value) {
            if (property_exists($this, $key) && ! in_array($key, ['newAttachments', 'attachments', 'isProcessing'])) {
                $this->{$key} = $value;
            }
        }

        $this->dispatch('draft-loaded');
    }

    #[Renderless]
    public function discardDraft()
    {
        $this->dispatch('clear-draft');
    }

    public function getExpectedParticipantsProperty()
    {
        return (int) $this->expectedPLVParticipants + (int) $this->expectedNonPLVParticipants;
    }

    public function removeAttachment($index)
    {
        array_splice($this->attachments, $index, 1);
    }

    public function updatedNewAttachments()
    {
        // Handle single file upload (S3 driver doesn't support multiple)
        // Wrap single file in array for consistent processing
        $files = $this->newAttachments;
        if (! is_array($files)) {
            $files = $files ? [$files] : [];
        }

        // If no files, return early
        if (empty($files)) {
            return;
        }

        // Temporarily set as array for validation
        $this->newAttachments = $files;

        // Note: Using Laravel's built-in mimes validation instead of finfo_file
        // because temp files are stored on S3 and not accessible locally
        $this->validate([
            'newAttachments.*' => [
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx',
            ],
        ]);

        $this->attachments = array_merge($this->attachments, $files);
        $this->reset('newAttachments');
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
