<?php

namespace App\Livewire\StudentOrg;

use App\Models\Attachment;
use App\Models\Event_Type;
use App\Models\Fund_Sources;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketSubmittedNotification;
use App\Services\TransactionLogService;
use Exception;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class SubmitTicket extends Component
{
    #[Title('Submit Ticket - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]
    // Step tracking
    public $currentStep = 1;
    public $totalSteps = 6;
    public $agreeToTerms = false;

    // Step 1: Organization
    public $organizationName = '';
    public $organizationCourse = '';
    public $adviser = '';
    public $contactEmail = '';
    public $proponentName = '';
    public $proponentPosition = '';
    public $proponent_contact = '';
    public $adviser_contact = '';

    // Step 2: Event Details
    public $eventTitle = '';
    public $eventDescription = '';
    public $eventType = 1;
    public $expectedPLVParticipants = 0;
    public $expectedNonPLVParticipants = 0;

    // Step 3: Schedule & Venue
    public $eventStartDate = '';
    public $eventEndDate = '';
    public $eventStartTime = '';
    public $eventEndTime = '';
    public $preferredVenue = '';
    public $alternativeVenue = '';
    public $specialRequirements = '';
    public $is_oc = false;
    public $oc_accommodation = '';
    public $oc_tsp = null;
    public $oc_driver_name = '';
    public $oc_driver_contact_number = '';
    public $oc_vehicle_type = '';
    public $oc_vehicle_plate_number = '';

    // Step 4: Budget
    public $totalBudget = 0.00;
    public $fundingSource = '';
    public $budgetBreakdown = '';
    public $igp_requested = '';
    public $igp_details = '';

    // Step 5: Attachments
    public $attachments = [];
    public $newAttachments = [];
    public $additionalNotes = '';

    // UI
    public $isProcessing = false; // To prevent multiple submissions

    use WithFileUploads;
    use Toast;

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
            $this->currentStep = $step;
            $this->dispatch('step-changed');
        }
    }

    protected function getCurrentStepRules(): array
    {
        return match ($this->currentStep) {
            1 => [
                'proponent_contact' => 'required|string|max:255|regex:/^[0-9\s\-\+\(\)]+$/',
                'adviser_contact' => 'nullable|string|max:255|regex:/^[0-9\s\-\+\(\)]+$/',
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
                'eventStartTime' => ['required', 'date_format:H:i'],
                'eventEndTime' => ['required', 'date_format:H:i', 'after:eventStartTime'],
                'preferredVenue' => 'required|string|max:255|min:3',
                'alternativeVenue' => 'nullable|string|max:255|min:3',
                'specialRequirements' => 'nullable|string|max:2000',
                'is_oc' => 'required|boolean',
                'oc_accommodation' => $this->is_oc ? 'nullable|string|max:2000' : 'nullable',
                'oc_tsp' => $this->is_oc ? 'required|string|in:in-house,outsourced' : 'nullable',
                'oc_driver_name' => ($this->is_oc && $this->oc_tsp === 'outsourced') ? 'required|string|max:255|min:2' : 'nullable',
                'oc_driver_contact_number' => ($this->is_oc && $this->oc_tsp === 'outsourced') ? 'required|string|max:255|regex:/^[0-9\s\-\+\(\)]+$/' : 'nullable',
                'oc_vehicle_type' => ($this->is_oc && $this->oc_tsp === 'outsourced') ? 'required|string|max:255|min:2' : 'nullable',
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

    protected function validateCurrentStep()
    {
        $rules = $this->getCurrentStepRules();

        if (!empty($rules)) {
            $this->validate($rules);
        }

        // Custom time range validation
        if ($this->currentStep === 3 && $this->eventStartTime && $this->eventEndTime) {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $this->eventStartTime);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $this->eventEndTime);
            $minTime = \Carbon\Carbon::createFromFormat('H:i', '08:00');
            $maxTime = \Carbon\Carbon::createFromFormat('H:i', '21:00');

            if ($startTime->lt($minTime)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'eventStartTime' => 'Event start time must be at or after 8:00 AM.'
                ]);
            }

            if ($endTime->gt($maxTime)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'eventEndTime' => 'Event end time must be at or before 9:00 PM.'
                ]);
            }
        }
    }


    public function getPreviewTicketProperty()
    {
        $currentUser = auth()->user();
        $currentUserinfo = $currentUser->studentOrganization;

        // Create a temporary ticket object for preview
        $ticket = new Ticket();
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
        $ticket->alternate_venue = $this->alternativeVenue;
        $ticket->special_requirements = $this->specialRequirements;
        $ticket->estimated_budget = $this->totalBudget;
        $ticket->budget_breakdown = $this->budgetBreakdown;
        $ticket->igp_requested = $this->igp_requested === 'true';
        $ticket->igp_details = $this->igp_details;
        $ticket->oc_accommodation = $this->oc_accommodation;
        $ticket->oc_tsp = $this->oc_tsp;
        $ticket->oc_driver_name = $this->oc_driver_name;
        $ticket->oc_vehicle_type = $this->oc_vehicle_type;
        $ticket->oc_vehicle_plate_number = $this->oc_vehicle_plate_number;
        $ticket->oc_driver_contact_number = $this->oc_driver_contact_number;
        $ticket->additional_notes = $this->additionalNotes;

        // Create temporary attachment objects for preview
        $previewAttachments = collect($this->attachments)->map(function ($file) {
            $attachment = new Attachment();
            $attachment->file_name = $file->getClientOriginalName();
            $attachment->file_type = $file->getMimeType();
            $attachment->file_path = null; // Not stored yet

            return $attachment;
        });

        // Load relationships
        $ticket->setRelation('eventType', Event_Type::find($this->eventType));
        $ticket->setRelation('fundSource', Fund_Sources::find($this->fundingSource));
        $ticket->setRelation('attachments', $previewAttachments);

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
        $this->organizationCourse = $currentUserinfo->course->course_name ?? '';
        $this->proponentPosition = $currentUserPosition->position_name ?? '';
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

        try {
            // Validate all steps before submission
            $this->validateCurrentStep();

            \DB::beginTransaction();
            $orgCode = $currentUserinfo->org_code;
            $lastTicket = Ticket::lockForUpdate()
                ->where('ticket_number', 'LIKE', "TKT-{$orgCode}-%")
                ->orderByRaw('CAST(SUBSTRING(ticket_number, LOCATE(\'-\', ticket_number, 5) + 1) AS UNSIGNED) DESC')
                ->first();

            $nextNumber = $lastTicket
                ? ((int)substr(strrchr($lastTicket->ticket_number, '-'), 1)) + 1
                : 1;

            // Generate unique ticket number with locking
            $ticketCode = "TKT-{$orgCode}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $ticket = Ticket::create([
                'user_id' => $currentUser->user_id,
                'ticket_number' => $ticketCode,
                'event_type_id' => $this->eventType,
                'plv_participants' => $this->expectedPLVParticipants,
                'external_participants' => $this->expectedNonPLVParticipants,
                'title' => $this->eventTitle,
                'description' => $this->eventDescription,
                'proponent_contact' => $this->proponent_contact,
                'adviser_contact' => $this->adviser_contact,
                'igp_requested' => $this->igp_requested === 'true',
                'igp_details' => $this->igp_details,
                'oc_accommodation' => $this->oc_accommodation,
                'oc_tsp' => $this->oc_tsp ?: null,
                'oc_driver_name' => $this->oc_driver_name,
                'oc_vehicle_type' => $this->oc_vehicle_type,
                'oc_vehicle_plate_number' => $this->oc_vehicle_plate_number,
                'oc_driver_contact_number' => $this->oc_driver_contact_number,
                'estimated_budget' => $this->totalBudget,
                'budget_breakdown' => $this->budgetBreakdown,
                'venue_requested' => $this->preferredVenue,
                'alternate_venue' => $this->alternativeVenue,
                'special_requirements' => $this->specialRequirements,
                'total_participants' => $this->expectedPLVParticipants + $this->expectedNonPLVParticipants,
                'fund_source_id' => $this->fundingSource,
                'date_from' => $this->eventStartDate,
                'date_to' => $this->eventEndDate,
                'time_from' => $this->eventStartTime,
                'time_to' => $this->eventEndTime,
                'additional_notes' => $this->additionalNotes,
                'status' => 'received',
            ]);

            // Handle file attachments
            if (!empty($this->attachments)) {
                foreach ($this->attachments as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time() . '_' . uniqid() . '_' . $originalName;
                    $path = $file->storeAs(
                        "tickets/{$ticket->ticket_id}/attachments",
                        $filename
                    );

                    Attachment::create([
                        'ticket_id' => $ticket->ticket_id,
                        'file_name' => $originalName,
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
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
        } catch (Exception $e) {
            \DB::rollBack();

            // Keep user data on error
            $this->toast(
                type: 'error',
                title: 'Submission Failed',
                description: 'Your data has been preserved. Please try again.',
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert-error',
                timeout: 5000,
                redirectTo: null
            );

            // Log with context
            \Log::error('Ticket submission failed', [
                'user_id' => $currentUser->user_id,
                'step' => $this->currentStep,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function loadDraft($draftData)
    {
        foreach ($draftData as $key => $value) {
            if (property_exists($this, $key) && !in_array($key, ['newAttachments', 'attachments', 'isProcessing'])) {
                $this->{$key} = $value;
            }
        }

        $this->dispatch('draft-loaded');
    }

    public function discardDraft()
    {
        $this->dispatch('clear-draft');
    }

    public function getExpectedParticipantsProperty()
    {
        return (int)$this->expectedPLVParticipants + (int)$this->expectedNonPLVParticipants;
    }

    public function removeAttachment($index)
    {
        array_splice($this->attachments, $index, 1);
    }

    public function updatedNewAttachments()
    {
        $this->validate([
            'newAttachments.*' => [
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx',
                function ($attribute, $value, $fail) {
                    // Check actual file content, not just extension
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $value->getRealPath());
                    finfo_close($finfo);

                    $allowedMimes = [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'image/jpeg',
                        'image/png',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ];

                    if (!in_array($mimeType, $allowedMimes)) {
                        $fail('Invalid file type detected.');
                    }
                }
            ]
        ]);

        // Check available disk space
        $totalSize = collect($this->newAttachments)->sum(fn($file) => $file->getSize());
        if (disk_free_space(storage_path()) < ($totalSize * 2)) {
            throw ValidationException::withMessages([
                'newAttachments' => 'Insufficient storage space.'
            ]);
        }

        $this->attachments = array_merge($this->attachments, $this->newAttachments);
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

    public function getRequiredDocuments()
    {
        return config("event_requirements.documents.{$this->eventType}", ['Pick an event type to see needed attachments.']);
    }

    public function render()
    {
        return view('livewire.student-org.submit-ticket', [
            'eventTypes' => Event_Type::all(),
            'fundSources' => Fund_Sources::all(),
        ]);
    }
}
