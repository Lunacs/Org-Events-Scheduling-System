<?php

namespace App\Livewire\StudentOrg;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketSubmittedNotification;
use Carbon\Carbon;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class Reschedule extends Component
{
    use WithFileUploads, Toast;

    #[Title('Reschedule Request - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]

    public $selectedEventId = '';
    public $changeDate = false;
    public $changeTime = false;
    public $changeVenue = false;
    public $newStartDate = '';
    public $newEndDate = '';
    public $newStartTime = '';
    public $newEndTime = '';
    public $newVenue = '';
    public $alternativeVenue = '';
    public $agreeToTerms = false;
    public $showPreviewModal = false;

    #[Validate('nullable|array')]
    public $supportingDocuments = [];

    public function mount()
    {
        if (!auth()->check()) {
            abort(401);
        }

        if (request()->has('ticket')) {
            $ticketNumber = request()->get('ticket');
            $ticket = auth()->user()->tickets()
                ->where('ticket_number', $ticketNumber)
                ->whereIn('status', ['approved', 'for_rescheduling'])
                ->first();

            if ($ticket) {
                $this->selectedEventId = $ticket->ticket_id;
                $this->changeDate = true;
            }
        }
    }

    public function updatedSupportingDocuments()
    {
        $this->validate([
            'supportingDocuments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx'
        ]);
    }

    public function removeAttachment($index)
    {
        array_splice($this->supportingDocuments, $index, 1);
    }

    public function openPreviewModal()
    {
        $this->validate(['selectedEventId' => 'required']);
        $this->showPreviewModal = true;
    }

    public function closePreviewModal()
    {
        $this->showPreviewModal = false;
    }

    public function getPreviewTicketProperty()
    {
        if (!$this->selectedEventId) {
            return null;
        }

        $ticket = auth()->user()->tickets()->find($this->selectedEventId);
        if (!$ticket) {
            return null;
        }

        $previewTicket = clone $ticket;

        // Apply proposed changes
        if ($this->changeDate) {
            $previewTicket->date_from = $this->newStartDate;
            $previewTicket->date_to = $this->newEndDate;
        }

        if ($this->changeTime) {
            $previewTicket->time_from = $this->newStartTime;
            $previewTicket->time_to = $this->newEndTime;
        }

        if ($this->changeVenue) {
            $previewTicket->venue_requested = $this->newVenue;
            $previewTicket->alternate_venue = $this->alternativeVenue;
        }

        // Preview new attachments
        if (!empty($this->supportingDocuments)) {
            $previewAttachments = collect($this->supportingDocuments)->map(function ($file) {
                $attachment = new Attachment();
                $attachment->file_name = $file->getClientOriginalName();
                $attachment->file_type = $file->getMimeType();
                $attachment->file_path = null;
                return $attachment;
            });

            $existingAttachments = $ticket->attachments ?? collect();
            $previewTicket->setRelation('attachments', $existingAttachments->merge($previewAttachments));
        }

        return $previewTicket;
    }

    public function submitReschedule()
    {
        $rules = [
            'selectedEventId' => 'required',
            'agreeToTerms' => 'accepted',
            'supportingDocuments' => 'nullable|array',
            'supportingDocuments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx',
        ];

        if ($this->changeDate) {
            $rules['newStartDate'] = 'required|date|after:today';
            $rules['newEndDate'] = 'required|date|after_or_equal:newStartDate';
        }

        if ($this->changeTime) {
            $rules['newStartTime'] = 'required|date_format:H:i|after_or_equal:08:00';
            $rules['newEndTime'] = 'required|date_format:H:i|after:newStartTime|before_or_equal:21:00';
        }

        if ($this->changeVenue) {
            $rules['newVenue'] = 'required|string|max:255';
            $rules['alternativeVenue'] = 'nullable|string|max:255';
        }

        $this->validate($rules);

        try {
            $ticket = auth()->user()->tickets()->findOrFail($this->selectedEventId);

            // Check 2-day minimum requirement
            $daysUntilEvent = Carbon::now()->diffInDays(Carbon::parse($ticket->date_from), false);
            if ($daysUntilEvent < 2) {
                $this->toast(
                    type: 'error',
                    title: 'Update Failed',
                    description: 'Reschedule requests must be submitted at least 2 days before the event date.',
                    position: 'toast-top toast-end',
                    timeout: 3000
                );
                return;
            }

            // Update ticket fields
            if ($this->changeDate) {
                $ticket->date_from = $this->newStartDate;
                $ticket->date_to = $this->newEndDate;
            }

            if ($this->changeTime) {
                $ticket->time_from = $this->newStartTime;
                $ticket->time_to = $this->newEndTime;
            }

            if ($this->changeVenue) {
                $ticket->venue_requested = $this->newVenue;
                $ticket->alternate_venue = $this->alternativeVenue;
            }

            $ticket->status = 'rescheduled';
            $ticket->save();

            // Save attachments
            if (!empty($this->supportingDocuments)) {
                foreach ($this->supportingDocuments as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time() . '_' . uniqid() . '_' . $originalName;
                    $path = $file->storeAs("tickets/{$ticket->ticket_id}/attachments", $filename);

                    Attachment::create([
                        'ticket_id' => $ticket->ticket_id,
                        'file_name' => $originalName,
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                    ]);
                }
            }

            // Notify OSA admins
            $osaUsers = User::where('role_id', User::ROLE_OSA)->get();
            foreach ($osaUsers as $osaUser) {
                $osaUser->notify(new TicketSubmittedNotification($ticket));
            }

            $this->toast(
                type: 'success',
                title: 'Ticket Updated!',
                description: 'Your ticket has been resubmitted for review.',
                position: 'toast-top toast-end',
                timeout: 3000
            );

            return redirect()->route('student-org.my-tickets');
        } catch (\Exception $e) {
            $this->toast(
                type: 'error',
                title: 'Update Failed',
                description: $e->getMessage(),
                position: 'toast-top toast-end',
                timeout: 3000
            );
        }
    }

    public function saveDraft()
    {
        $this->toast(
            type: 'info',
            title: 'Draft saved',
            description: 'Your changes have been saved.',
            position: 'toast-top toast-end',
            timeout: 3000
        );
    }

    public function render()
    {
        $approvedTickets = auth()->user()->tickets()
            ->whereIn('status', ['approved', 'for_rescheduling'])
            ->get();

        $approved = $approvedTickets->map(function ($ticket) {
            $status = match ($ticket->status) {
                'approved' => 'Approved',
                'for_rescheduling' => 'For Rescheduling',
                default => 'Unknown',
            };

            return [
                'id' => $ticket->ticket_id,
                'name' => $ticket->title . ' - ' .
                    Carbon::parse($ticket->date_from)->format('M d, Y') . ' to ' .
                    Carbon::parse($ticket->date_to)->format('M d, Y') .
                    ' (' . $ticket->ticket_number . ') [' . $status . ']',
            ];
        });

        $selected = $this->selectedEventId
            ? $approvedTickets->firstWhere('ticket_id', $this->selectedEventId)
            : null;

        return view('livewire.student-org.reschedule', [
            'approvedEvents' => $approved,
            'selectedEvent' => $selected,
        ]);
    }
}
