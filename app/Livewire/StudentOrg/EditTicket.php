<?php

namespace App\Livewire\StudentOrg;

use App\Models\Attachment;
use Exception;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Models\Ticket;
use App\Models\Event_Type;
use App\Models\Fund_Sources;
use Mary\Traits\Toast;

class EditTicket extends Component
{
    use WithFileUploads, Toast;

    public $ticketId;
    public $ticket;
    public $isForRescheduling = false;
    public $isNeedsRevision = false;
    public $showPreviewModal = false;

    // All your existing properties from SubmitTicket
    public $organizationName = '';
    public $adviser = '';
    // ... (copy all properties from SubmitTicket.php)

    #[On('load-ticket-for-edit')]
    public function loadTicket($ticketId)
    {
        $this->ticketId = $ticketId;
        $this->ticket = Ticket::with(['eventType', 'fundSource', 'attachments', 'user.studentOrganization'])
            ->findOrFail($ticketId);

        $this->isForRescheduling = strtolower($this->ticket->status) === 'for_rescheduling';
        $this->isNeedsRevision = strtolower($this->ticket->status) === 'needs_revision';

        // Populate form fields
        $this->populateFormFields();
    }

    private function populateFormFields()
    {
        $userInfo = $this->ticket->user->studentOrganization;

        // Organization Info (always readonly)
        $this->organizationName = $userInfo->org_code ?? '';
        $this->adviser = $userInfo->adviser_name ?? '';
        $this->contactEmail = $this->ticket->user->email ?? '';
        $this->proponentName = $this->ticket->user->name ?? '';
        $this->organizationCourse = $userInfo->course->course_name ?? '';
        $this->proponentPosition = $this->ticket->user->position->position_name ?? '';

        // Editable fields
        $this->proponent_contact = $this->ticket->proponent_contact;
        $this->adviser_contact = $this->ticket->adviser_contact;
        $this->eventTitle = $this->ticket->title;
        $this->eventDescription = $this->ticket->description;
        $this->eventType = $this->ticket->event_type_id;
        $this->expectedPLVParticipants = $this->ticket->plv_participants;
        $this->expectedNonPLVParticipants = $this->ticket->external_participants;
        $this->eventStartDate = $this->ticket->date_from;
        $this->eventEndDate = $this->ticket->date_to;
        $this->eventStartTime = $this->ticket->time_from;
        $this->eventEndTime = $this->ticket->time_to;
        $this->preferredVenue = $this->ticket->venue_requested;
        $this->alternativeVenue = $this->ticket->alternate_venue;
        $this->specialRequirements = $this->ticket->special_requirements;
        $this->totalBudget = $this->ticket->estimated_budget;
        $this->budgetBreakdown = $this->ticket->budget_breakdown;
        $this->fundingSource = $this->ticket->fund_source_id;
        $this->igp_requested = $this->ticket->igp_requested ? 'true' : 'false';
        $this->igp_details = $this->ticket->igp_details;
        $this->is_oc = (bool)$this->ticket->oc_accommodation;
        $this->oc_accommodation = $this->ticket->oc_accommodation;
        $this->oc_tsp = $this->ticket->oc_tsp;
        $this->oc_driver_name = $this->ticket->oc_driver_name;
        $this->oc_vehicle_type = $this->ticket->oc_vehicle_type;
        $this->oc_vehicle_plate_number = $this->ticket->oc_vehicle_plate_number;
        $this->oc_driver_contact_number = $this->ticket->oc_driver_contact_number;
        $this->additionalNotes = $this->ticket->additional_notes;

        // Load existing attachments
        $this->attachments = $this->ticket->attachments->toArray();
    }

    public function isFieldEditable($field)
    {
        // For rescheduling, only date/time/venue fields are editable
        if ($this->isForRescheduling) {
            return in_array($field, [
                'eventStartDate', 'eventEndDate', 'eventStartTime', 'eventEndTime',
                'preferredVenue', 'alternativeVenue'
            ]);
        }

        // For needs_revision, all fields except organization info are editable
        if ($this->isNeedsRevision) {
            return !in_array($field, [
                'organizationName', 'organizationCourse', 'proponentName',
                'contactEmail', 'proponentPosition', 'adviser'
            ]);
        }

        return false;
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
        // Create updated ticket object for preview
        $previewTicket = clone $this->ticket;

        // Update with new values
        $previewTicket->title = $this->eventTitle;
        $previewTicket->description = $this->eventDescription;
        $previewTicket->proponent_contact = $this->proponent_contact;
        $previewTicket->adviser_contact = $this->adviser_contact;
        $previewTicket->plv_participants = $this->expectedPLVParticipants;
        $previewTicket->external_participants = $this->expectedNonPLVParticipants;
        $previewTicket->total_participants = $this->expectedParticipants;
        $previewTicket->date_from = $this->eventStartDate;
        $previewTicket->date_to = $this->eventEndDate;
        $previewTicket->time_from = $this->eventStartTime;
        $previewTicket->time_to = $this->eventEndTime;
        $previewTicket->venue_requested = $this->preferredVenue;
        $previewTicket->alternate_venue = $this->alternativeVenue;
        $previewTicket->special_requirements = $this->specialRequirements;
        $previewTicket->estimated_budget = $this->totalBudget;
        $previewTicket->budget_breakdown = $this->budgetBreakdown;
        $previewTicket->igp_requested = $this->igp_requested === 'true';
        $previewTicket->igp_details = $this->igp_details;
        $previewTicket->additional_notes = $this->additionalNotes;

        $previewTicket->setRelation('eventType', Event_Type::find($this->eventType));
        $previewTicket->setRelation('fundSource', Fund_Sources::find($this->fundingSource));

        return $previewTicket;
    }

    public function updateTicket()
    {
        $this->validate();

        try {
            $this->ticket->update([
                'title' => $this->eventTitle,
                'description' => $this->eventDescription,
                'proponent_contact' => $this->proponent_contact,
                'adviser_contact' => $this->adviser_contact,
                'plv_participants' => $this->expectedPLVParticipants,
                'external_participants' => $this->expectedNonPLVParticipants,
                'total_participants' => $this->expectedPLVParticipants + $this->expectedNonPLVParticipants,
                'event_type_id' => $this->eventType,
                'date_from' => $this->eventStartDate,
                'date_to' => $this->eventEndDate,
                'time_from' => $this->eventStartTime,
                'time_to' => $this->eventEndTime,
                'venue_requested' => $this->preferredVenue,
                'alternate_venue' => $this->alternativeVenue,
                'special_requirements' => $this->specialRequirements,
                'estimated_budget' => $this->totalBudget,
                'budget_breakdown' => $this->budgetBreakdown,
                'fund_source_id' => $this->fundingSource,
                'igp_requested' => $this->igp_requested === 'true',
                'igp_details' => $this->igp_details,
                'oc_accommodation' => $this->oc_accommodation,
                'oc_tsp' => $this->oc_tsp === '' ? null : $this->oc_tsp,
                'oc_driver_name' => $this->oc_driver_name,
                'oc_vehicle_type' => $this->oc_vehicle_type,
                'oc_vehicle_plate_number' => $this->oc_vehicle_plate_number,
                'oc_driver_contact_number' => $this->oc_driver_contact_number,
                'additional_notes' => $this->additionalNotes,
                'status' => 'received', // Reset to received after revision
            ]);

            // Handle new attachments if any
            if ($this->newAttachments) {
                foreach ($this->newAttachments as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time() . '_' . uniqid() . '_' . $originalName;
                    $path = $file->storeAs(
                        "tickets/{$this->ticket->ticket_id}/attachments",
                        $filename
                    );

                    Attachment::create([
                        'ticket_id' => $this->ticket->ticket_id,
                        'file_name' => $originalName,
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                    ]);
                }
            }

            $this->toast(
                type: 'success',
                title: 'Ticket Updated!',
                description: 'Your ticket has been resubmitted for review.',
                position: 'toast-top toast-end',
                timeout: 3000
            );

            $this->dispatch('ticket-updated');
            $this->dispatch('close-edit-drawer');

        } catch (Exception $e) {
            $this->toast(
                type: 'error',
                title: 'Update Failed',
                description: $e->getMessage(),
                position: 'toast-top toast-end',
                timeout: 3000
            );
        }
    }

    public function render()
    {
        return view('livewire.student-org.edit-ticket', [
            'eventTypes' => Event_Type::all(),
            'fundSources' => Fund_Sources::all(),
        ]);
    }
}
