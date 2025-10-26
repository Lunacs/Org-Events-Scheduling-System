<?php

namespace App\Livewire\StudentOrg;

use App\Models\Event_Type;
use App\Models\Fund_Sources;
use App\Models\Ticket;
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
    #[Validate('nullable|integer|min:1')]
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
    #[Validate('required|date|after_or_equal:today')]
    public $eventStartDate = '';
    #[Validate('required|date|after_or_equal:eventStartDate')]
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

            $ticketCode = "{$currentUserinfo->org_code}-" . (Ticket::count() + 1);
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
                'igp_requested' => (bool)$this->igp_requested,
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

                    \App\Models\Attachment::create([
                        'ticket_id' => $ticket->ticket_id,
                        'file_name' => $originalName,
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                    ]);
                }
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
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to submit ticket: ' . $e->getMessage());
            $this->toast(
                type: 'error',
                title: 'Ticket Not Created.',
                description: 'Failed to submit ticket: ' . $e->getMessage(),
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
        return (int)$this->expectedPLVParticipants + (int)$this->expectedNonPLVParticipants;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateFilter = '';
    }

    public function render()
    {
        return view('livewire.student-org.submit-ticket', [
            'eventTypes' => Event_Type::all(),
            'fundSources' => Fund_Sources::all(),
        ]);
    }
}
