<?php

namespace App\Livewire\StudentOrg;

use App\Models\Attachment;
use App\Models\Event_Type;
use App\Models\Fund_Sources;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketSubmittedNotification;
use Exception;
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
    public $search = '';
    public $statusFilter = '';
    public $dateFilter = '';
    public $organizationName = '';

    #[Validate('required|boolean')]
    public $is_oc = false;

    #[Validate('required|string')]
    public $adviser = '';

    #[Validate('required|string|email')]
    public $contactEmail = '';

    #[Validate('required|string')]
    public $proponentName = '';

    #[Validate('required|string')]
    public $proponentPosition = '';

    #[Validate('required|string')]
    public $organizationCourse = '';

    #[Validate('required|string|max:255')]
    public $proponent_contact = '';

    #[Validate('nullable|string|max:255')]
    public $adviser_contact = '';

    #[Validate('required|integer|min:1')]
    public $expectedPLVParticipants = 0;
    #[Validate('nullable|integer|min:0')]
    public $expectedNonPLVParticipants = 0;

    #[Validate('required|string|max:255')]
    public $eventTitle = '';

    #[Validate('required|string|max:2000')]
    public $eventDescription = '';

    #[Validate('required|string|max:255')]
    public $preferredVenue = '';

    #[Validate('nullable|string|max:255')]
    public $alternativeVenue = '';

    #[Validate('nullable|string|max:2000')]
    public $specialRequirements = '';

    #[Validate('required|date')]
    public $eventStartDate = '';

    #[Validate('required|date')]
    public $eventEndDate = '';

    #[Validate('required|date_format:H:i')]
    public $eventStartTime = '';

    #[Validate('required|date_format:H:i')]
    public $eventEndTime = '';

    #[Validate('required|integer|exists:event__types,event_type_id')]
    public $eventType = '';

    #[Validate('required|integer|exists:fund__sources,source_id')]
    public $fundingSource = '';

    #[Validate('required|numeric|min:0')]
    public $totalBudget = 0.00;

    #[Validate('nullable|string|max:2000')]
    public $budgetBreakdown = '';

    #[Validate('nullable|string|max:2000')]
    public $oc_accommodation = '';

    #[Validate('nullable|string|in:in-house,outsourced')]
    public $oc_tsp = null;

    #[Validate('nullable|string|max:255')]
    public $oc_driver_name = '';

    #[Validate('nullable|string|max:255')]
    public $oc_vehicle_type = '';

    #[Validate('nullable|string|max:255')]
    public $oc_vehicle_plate_number = '';

    #[Validate('nullable|string|max:255')]
    public $oc_driver_contact_number = '';

    #[Validate('required|string|in:true,false')]
    public $igp_requested = '';

    #[Validate('nullable|string|max:2000')]
    public $igp_details = '';

    #[Validate('nullable|string|max:2000')]
    public $additionalNotes = '';

    use WithFileUploads;
    use Toast;

    #[Validate('nullable|array', 'attachments.*', 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx')]
    public $attachments = [];

    #[Validate('nullable|array', 'newAttachments.*', 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx')]
    public $newAttachments = [];

    public $showPreviewModal = false;

    protected function rules()
    {
        return [
            'eventStartDate' => 'required|date|after_or_equal:today',
            'eventEndDate' => 'required|date|after_or_equal:eventStartDate',
            'eventStartTime' => 'required|date_format:H:i|after_or_equal:08:00',
            'eventEndTime' => 'required|date_format:H:i|after:eventStartTime|before_or_equal:21:00',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        // Additional time range validation
        if ($propertyName === 'eventStartTime' && $this->eventStartTime < '08:00') {
            $this->addError('eventStartTime', 'Event start time must be at or after 8:00 AM.');
        }

        if ($propertyName === 'eventEndTime' && $this->eventEndTime > '21:00') {
            $this->addError('eventEndTime', 'Event end time must be at or before 9:00 PM.');
        }
    }


    public function openPreviewModal()
    {
        $this->showPreviewModal = true;
    }

    public function closePreviewModal()
    {
        $this->showPreviewModal = false;
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
        $currentUser = auth()->user();
        $currentUserinfo = $currentUser->studentOrganization;
        try {

            $ticketCode = "TKT-{$currentUserinfo->org_code}-".(Ticket::count() + 1);
            $this->validate();
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
                'igp_requested' => (bool) $this->igp_requested,
                'igp_details' => $this->igp_details,
                'oc_accommodation' => $this->oc_accommodation,
                'oc_tsp' => $this->oc_tsp === '' ? null : $this->oc_tsp,
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

            if ($this->attachments) {
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

            // Notify OSA admins about the new ticket
            $osaUsers = User::where('role_id', User::ROLE_OSA)->get();
            foreach ($osaUsers as $osaUser) {
                $osaUser->notify(new TicketSubmittedNotification($ticket));
            }

            $this->toast(
                type: 'success',
                title: 'Ticket Created!',
                description: null,
                position: 'toast-top toast-end',
                icon: 'o-information-circle',
                css: 'alert-info',
                timeout: 3000,
                redirectTo: route('student-org.dashboard')
            );
        } catch (Exception $e) {
            session()->flash('error', 'Failed to submit ticket: '.$e->getMessage());
            $this->toast(
                type: 'error',
                title: 'Ticket Not Created.',
                description: 'Failed to submit ticket: '.$e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-information-circle',
                css: 'alert-info',
                timeout: 3000,
                redirectTo: null
            );
        }
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
        $this->validate([
            'newAttachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx'
        ]);

        // Merge new files with existing attachments
        $this->attachments = array_merge($this->attachments, $this->newAttachments);

        // Clear the temporary input
        $this->reset('newAttachments');
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateFilter = '';
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
