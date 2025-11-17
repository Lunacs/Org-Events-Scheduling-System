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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class EditTicket extends Component
{
    use WithFileUploads, Toast, AuthorizesRequests;

    public $ticketId;
    public $ticket;
    public $showPreviewModal = false;

    // Organization Info (read-only)
    public $organizationName = '';
    public $organizationCourse = '';
    public $proponentName = '';
    public $proponentPosition = '';
    public $contactEmail = '';
    public $adviser = '';

    // Editable fields
    public $proponent_contact = '';
    public $adviser_contact = '';
    public $eventTitle = '';
    public $eventDescription = '';
    public $eventType = '';
    public $expectedPLVParticipants = 0;
    public $expectedNonPLVParticipants = 0;
    public $eventStartDate = '';
    public $eventEndDate = '';
    public $eventStartTime = '';
    public $eventEndTime = '';
    public $preferredVenue = '';
    public $alternativeVenue = '';
    public $specialRequirements = '';
    public $totalBudget = 0.0;
    public $budgetBreakdown = '';
    public $fundingSource = '';
    public $igp_requested = 'false';
    public $igp_details = '';
    public $oc_accommodation = '';
    public $oc_tsp = '';
    public $oc_driver_name = '';
    public $oc_vehicle_type = '';
    public $oc_vehicle_plate_number = '';
    public $oc_driver_contact_number = '';
    public $additionalNotes = '';

    // Attachments
    public $attachments = [];
    public $newAttachments = [];
    public $removedAttachmentIds = [];

    protected function rules()
    {
        return [
            'eventTitle' => 'required|string|max:255',
            'eventDescription' => 'required|string|max:5000',
            'proponent_contact' => 'required|string|max:255',
            'adviser_contact' => 'nullable|string|max:255',
            'expectedPLVParticipants' => 'required|integer|min:0|max:100000',
            'expectedNonPLVParticipants' => 'required|integer|min:0|max:100000',
            'eventType' => 'required|exists:event__types,event_type_id',
            'eventStartDate' => 'required|date|after_or_equal:today',
            'eventEndDate' => 'required|date|after_or_equal:eventStartDate',
            'eventStartTime' => 'required|date_format:H:i',
            'eventEndTime' => 'required|date_format:H:i|after:eventStartTime',
            'preferredVenue' => 'required|string|max:500',
            'alternativeVenue' => 'nullable|string|max:500',
            'specialRequirements' => 'nullable|string|max:2000',
            'totalBudget' => 'required|numeric|min:0|max:99999999.99',
            'budgetBreakdown' => 'required|string|max:5000',
            'fundingSource' => 'required|exists:fund__sources,source_id',
            'igp_requested' => 'required|in:true,false',
            'igp_details' => 'nullable|required_if:igp_requested,true|string|max:2000',
            'oc_accommodation' => 'nullable|string|max:2000',
            'oc_tsp' => 'nullable|in:in-house,outsourced',
            'oc_driver_name' => 'nullable|required_with:oc_tsp|string|max:255',
            'oc_vehicle_type' => 'nullable|required_with:oc_tsp|string|max:255',
            'oc_vehicle_plate_number' => 'nullable|required_with:oc_tsp|string|max:50',
            'oc_driver_contact_number' => 'nullable|required_with:oc_tsp|string|max:255',
            'additionalNotes' => 'nullable|string|max:2000',
            'newAttachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx',
        ];
    }

    protected $messages = [
        'eventStartDate.after_or_equal' => 'Event start date must be today or a future date.',
        'eventEndDate.after_or_equal' => 'Event end date must be on or after the start date.',
        'eventEndTime.after' => 'Event end time must be after the start time.',
        'igp_details.required_if' => 'IGP details are required when IGP is requested.',
        'oc_driver_name.required_with' => 'Driver name is required when TSP is specified.',
        'newAttachments.*.max' => 'Each file must not exceed 10MB.',
        'newAttachments.*.mimes' => 'Only PDF, DOC, DOCX, JPG, PNG, XLS, XLSX files are allowed.',
    ];

    public function mount($ticketId = null)
    {
        if ($ticketId) {
            $this->loadTicket($ticketId);
        }
    }

    #[On('load-ticket-for-edit')]
    public function loadTicket($ticketId)
    {
        try {
            $this->ticketId = $ticketId;

            $this->ticket = Ticket::with([
                'eventType',
                'fundSource',
                'attachments',
                'user.studentOrganization.course',
                'user.position'
            ])->findOrFail($ticketId);

            // Security: Verify user owns this ticket
            $this->authorize('update', $this->ticket);

            // Security: Only allow editing tickets with 'needs_revision' status
            if (strtolower($this->ticket->status) !== 'needs_revision') {
                $this->error(
                    'Access Denied',
                    'Only tickets marked for revision can be edited.',
                    position: 'toast-top toast-end'
                );
                $this->dispatch('close-edit-drawer');
                return;
            }

            $this->populateFormFields();

        } catch (Exception $e) {
            Log::error('Failed to load ticket for editing', [
                'ticket_id' => $ticketId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            $this->error(
                'Error',
                'Unable to load ticket. Please try again.',
                position: 'toast-top toast-end'
            );
            $this->dispatch('close-edit-drawer');
        }
    }

    private function populateFormFields()
    {
        $userInfo = $this->ticket->user->studentOrganization;

        // Organization Info (read-only)
        $this->organizationName = $userInfo->org_code ?? '';
        $this->organizationCourse = $userInfo->course->course_name ?? '';
        $this->proponentName = $this->ticket->user->name ?? '';
        $this->proponentPosition = $this->ticket->user->position->position_name ?? '';
        $this->contactEmail = $this->ticket->user->email ?? '';
        $this->adviser = $userInfo->adviser_name ?? '';

        // Editable fields
        $this->proponent_contact = $this->ticket->proponent_contact ?? '';
        $this->adviser_contact = $this->ticket->adviser_contact ?? '';
        $this->eventTitle = $this->ticket->title;
        $this->eventDescription = $this->ticket->description;
        $this->eventType = $this->ticket->event_type_id;
        $this->expectedPLVParticipants = $this->ticket->plv_participants ?? 0;
        $this->expectedNonPLVParticipants = $this->ticket->external_participants ?? 0;
        $this->eventStartDate = $this->ticket->date_from;
        $this->eventEndDate = $this->ticket->date_to;
        $this->eventStartTime = $this->ticket->time_from;
        $this->eventEndTime = $this->ticket->time_to;
        $this->preferredVenue = $this->ticket->venue_requested;
        $this->alternativeVenue = $this->ticket->alternate_venue ?? '';
        $this->specialRequirements = $this->ticket->special_requirements ?? '';
        $this->totalBudget = $this->ticket->estimated_budget ?? 0;
        $this->budgetBreakdown = $this->ticket->budget_breakdown;
        $this->fundingSource = $this->ticket->fund_source_id;
        $this->igp_requested = $this->ticket->igp_requested ? 'true' : 'false';
        $this->igp_details = $this->ticket->igp_details ?? '';
        $this->oc_accommodation = $this->ticket->oc_accommodation ?? '';
        $this->oc_tsp = $this->ticket->oc_tsp ?? '';
        $this->oc_driver_name = $this->ticket->oc_driver_name ?? '';
        $this->oc_vehicle_type = $this->ticket->oc_vehicle_type ?? '';
        $this->oc_vehicle_plate_number = $this->ticket->oc_vehicle_plate_number ?? '';
        $this->oc_driver_contact_number = $this->ticket->oc_driver_contact_number ?? '';
        $this->additionalNotes = $this->ticket->additional_notes ?? '';

        $this->attachments = $this->ticket->attachments->toArray();
    }

    public function getExpectedParticipantsProperty()
    {
        return (int)$this->expectedPLVParticipants + (int)$this->expectedNonPLVParticipants;
    }

    public function getRequiredDocuments()
    {
        return config("event_requirements.documents.{$this->eventType}", [
            'Pick an event type to see needed attachments.'
        ]);
    }

    public function isFieldEditable($field)
    {
        // All fields except organization info are editable for revision
        $readOnlyFields = [
            'organizationName',
            'organizationCourse',
            'proponentName',
            'proponentPosition',
            'contactEmail',
            'adviser'
        ];

        return !in_array($field, $readOnlyFields);
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
        $previewTicket = clone $this->ticket;

        // Update with form values
        $previewTicket->fill([
            'title' => $this->eventTitle,
            'description' => $this->eventDescription,
            'proponent_contact' => $this->proponent_contact,
            'adviser_contact' => $this->adviser_contact,
            'plv_participants' => $this->expectedPLVParticipants,
            'external_participants' => $this->expectedNonPLVParticipants,
            'total_participants' => $this->expectedParticipants,
            'date_from' => $this->eventStartDate,
            'date_to' => $this->eventEndDate,
            'time_from' => $this->eventStartTime,
            'time_to' => $this->eventEndTime,
            'venue_requested' => $this->preferredVenue,
            'alternate_venue' => $this->alternativeVenue,
            'special_requirements' => $this->specialRequirements,
            'estimated_budget' => $this->totalBudget,
            'budget_breakdown' => $this->budgetBreakdown,
            'igp_requested' => $this->igp_requested === 'true',
            'igp_details' => $this->igp_details,
            'oc_accommodation' => $this->oc_accommodation,
            'oc_tsp' => $this->oc_tsp ?: null,
            'oc_driver_name' => $this->oc_driver_name,
            'oc_vehicle_type' => $this->oc_vehicle_type,
            'oc_vehicle_plate_number' => $this->oc_vehicle_plate_number,
            'oc_driver_contact_number' => $this->oc_driver_contact_number,
            'additional_notes' => $this->additionalNotes,
        ]);

        $previewTicket->setRelation('eventType', Event_Type::find($this->eventType));
        $previewTicket->setRelation('fundSource', Fund_Sources::find($this->fundingSource));

        return $previewTicket;
    }

    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index]['attachment_id'])) {
            $this->removedAttachmentIds[] = $this->attachments[$index]['attachment_id'];
        }

        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function removeNewAttachment($index)
    {
        unset($this->newAttachments[$index]);
        $this->newAttachments = array_values($this->newAttachments);
    }

    public function updatedNewAttachments()
    {
        $this->validate([
            'newAttachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx',
        ]);
    }

    public function updateTicket()
    {
        // Validate all fields
        $this->validate();

        // Security: Re-verify ownership
        $this->authorize('update', $this->ticket);

        // Security: Re-verify status
        if (strtolower($this->ticket->status) !== 'needs_revision') {
            $this->error(
                'Invalid Action',
                'This ticket can no longer be edited.',
                position: 'toast-top toast-end'
            );
            return;
        }

        DB::beginTransaction();

        try {
            // Delete removed attachments
            if (! empty($this->removedAttachmentIds)) {
                foreach ($this->removedAttachmentIds as $attachmentId) {
                    $attachment = Attachment::where('attachment_id', $attachmentId)
                        ->where('ticket_id', $this->ticket->ticket_id)
                        ->first();

                    if ($attachment) {
                        if (Storage::exists($attachment->file_path)) {
                            Storage::delete($attachment->file_path);
                        }
                        $attachment->delete();
                    }
                }
            }

            // Update ticket
            $this->ticket->update([
                'title' => $this->eventTitle,
                'description' => $this->eventDescription,
                'proponent_contact' => $this->proponent_contact,
                'adviser_contact' => $this->adviser_contact,
                'plv_participants' => $this->expectedPLVParticipants,
                'external_participants' => $this->expectedNonPLVParticipants,
                'total_participants' => $this->expectedParticipants,
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
                'oc_tsp' => $this->oc_tsp ?: null,
                'oc_driver_name' => $this->oc_driver_name,
                'oc_vehicle_type' => $this->oc_vehicle_type,
                'oc_vehicle_plate_number' => $this->oc_vehicle_plate_number,
                'oc_driver_contact_number' => $this->oc_driver_contact_number,
                'additional_notes' => $this->additionalNotes,
                'status' => 'amended',
            ]);

            // Refresh the model instance after update
            $this->ticket->refresh();

            // Log transaction
            TransactionLogService::logTicketOperation('amended', $this->ticket);

            // Handle new attachments if any
            if ($this->newAttachments) {
                foreach ($this->newAttachments as $file) {
                    $originalName = $file->getClientOriginalName();
                    $sanitizedName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
                    $filename = time() . '_' . uniqid() . '_' . $sanitizedName;

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

            // Notify OSA admins - now using the refreshed model
            $osaUsers = User::where('role_id', User::getRoleId('osa'))->get();
            foreach ($osaUsers as $osaUser) {
                $osaUser->notify(new TicketSubmittedNotification($this->ticket));
            }

            DB::commit();

            Log::info('Ticket updated successfully', [
                'ticket_id' => $this->ticket->ticket_id,
                'user_id' => auth()->id()
            ]);

            $this->success(
                'Ticket Updated!',
                'Your ticket has been resubmitted for review.',
                position: 'toast-top toast-end'
            );

            // Reset state
            $this->reset(['newAttachments', 'removedAttachmentIds']);

            // Close drawer and refresh parent
            $this->dispatch('close-edit-drawer');
            $this->dispatch('ticket-updated')->to(MyTicket::class);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Ticket update failed', [
                'ticket_id' => $this->ticket->ticket_id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error(
                'Update Failed',
                'An error occurred while updating your ticket. Please try again.',
                position: 'toast-top toast-end'
            );
        }
    }

    public function render()
    {
        return view('livewire.student-org.edit-ticket', [
            'eventTypes' => Event_Type::orderBy('type_name')->get(),
            'fundSources' => Fund_Sources::orderBy('source_name')->get(),
        ]);
    }
}
