<?php

namespace App\Livewire\StudentOrg;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketSubmittedNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class Reschedule extends Component
{
    use WithFileUploads, Toast, AuthorizesRequests;

    #[Title('Reschedule Request - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]

    // Step tracking
    public $currentStep = 1;
    public $totalSteps = 4;
    public $isProcessing = false;

    // Form fields
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

    #[Validate('nullable|array|max:10')]
    public $supportingDocuments = [];

    // Constants
    private const MIN_RESCHEDULE_DAYS = 1;
    private const MAX_FILE_SIZE = 10240; // 10MB in KB
    private const ALLOWED_MIMES = 'pdf,doc,docx,jpg,jpeg,png,xls,xlsx';
    private const BUSINESS_HOURS_START = '08:00';
    private const BUSINESS_HOURS_END = '21:00';

    public function mount()
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            abort(401, 'Unauthorized access');
        }

        // Pre-fill from query parameter
        if (request()->has('ticket')) {
            $this->loadTicketFromRequest();
        }
    }

    private function loadTicketFromRequest(): void
    {
        $ticketNumber = request()->get('ticket');

        if (!preg_match('/^TKT-[A-Z]+-\d{4}$/', $ticketNumber)) {
            return;
        }

        $ticket = $this->getBaseTicketsQuery()
            ->where('ticket_number', $ticketNumber)
            ->whereIn('status', ['approved', 'for_rescheduling'])
            ->first();

        if ($ticket) {
            $this->selectedEventId = $ticket->ticket_id;
            $this->changeDate = true;
        }
    }

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
        } catch (ValidationException $e) {
            $this->isProcessing = false;
            throw $e;
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
        // Validate step number
        if (!is_numeric($step) || $step < 1 || $step > $this->totalSteps) {
            return;
        }

        $targetStep = (int) $step;

        // Don't allow jumping ahead without validation
        if ($targetStep > $this->currentStep) {
            try {
                // Validate current step before moving forward
                $this->validateCurrentStep();
                $this->currentStep = $targetStep;
                $this->dispatch('step-changed');
            } catch (ValidationException $e) {
                // Validation failed, stay on current step
                throw $e;
            }
        } else {
            // Allow going back without validation
            $this->currentStep = $targetStep;
            $this->dispatch('step-changed');
        }
    }


    protected function getCurrentStepRules(): array
    {
        return match ($this->currentStep) {
            1 => [
                'selectedEventId' => 'required|integer|exists:tickets,ticket_id',
                'changeDate' => 'required_without_all:changeTime,changeVenue|boolean',
                'changeTime' => 'required_without_all:changeDate,changeVenue|boolean',
                'changeVenue' => 'required_without_all:changeDate,changeTime|boolean',
            ],
            2 => $this->getScheduleValidationRules(),
            3 => [
                'supportingDocuments' => 'nullable|array|max:10',
                'supportingDocuments.*' => [
                    'file',
                    'max:' . self::MAX_FILE_SIZE,
                    'mimes:' . self::ALLOWED_MIMES,
                ],
            ],
            4 => [
                'agreeToTerms' => 'required|accepted',
            ],
            default => [],
        };
    }

    private function getScheduleValidationRules(): array
    {
        $rules = [];

        if ($this->changeDate) {
            $minDate = now()->addDays(self::MIN_RESCHEDULE_DAYS)->format('Y-m-d');
            $rules['newStartDate'] = "required|date|after_or_equal:{$minDate}";
            $rules['newEndDate'] = 'required|date|after_or_equal:newStartDate';
        }

        if ($this->changeTime) {
            $rules['newStartTime'] = [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $time = Carbon::createFromFormat('H:i', $value);
                    $start = Carbon::createFromFormat('H:i', self::BUSINESS_HOURS_START);
                    $end = Carbon::createFromFormat('H:i', self::BUSINESS_HOURS_END);

                    if ($time->lt($start) || $time->gte($end)) {
                        $fail('Event must be scheduled between ' . self::BUSINESS_HOURS_START . ' and ' . self::BUSINESS_HOURS_END);
                    }
                },
            ];
            $rules['newEndTime'] = [
                'required',
                'date_format:H:i',
                'after:newStartTime',
                function ($attribute, $value, $fail) {
                    $time = Carbon::createFromFormat('H:i', $value);
                    $end = Carbon::createFromFormat('H:i', self::BUSINESS_HOURS_END);

                    if ($time->gt($end)) {
                        $fail('Event must end by ' . self::BUSINESS_HOURS_END);
                    }
                },
            ];
        }

        if ($this->changeVenue) {
            $rules['newVenue'] = 'required|string|max:255|min:3';
            $rules['alternativeVenue'] = 'nullable|string|max:255|min:3|different:newVenue';
        }

        return $rules;
    }

    protected function validateCurrentStep()
    {
        $rules = $this->getCurrentStepRules();

        if (!empty($rules)) {
            $this->validate($rules);
        }

        // Additional authorization check for step 1
        if ($this->currentStep === 1 && $this->selectedEventId) {
            $this->authorizeSelectedTicket();
        }
    }

    private function authorizeSelectedTicket(): void
    {
        $user = auth()->user();
        $ticket = \App\Models\Ticket::find($this->selectedEventId);

        if (!$ticket) {
            throw ValidationException::withMessages([
                'selectedEventId' => 'Ticket not found.',
            ]);
        }

        // President can only reschedule their own tickets
        if ($user->position->position_name === 'President' && $ticket->user_id !== $user->user_id) {
            throw ValidationException::withMessages([
                'selectedEventId' => 'You are not authorized to reschedule this event.',
            ]);
        }

        // Chairperson/Adviser can reschedule any ticket in their org
        if (in_array($user->position->position_name, ['Chairperson', 'Adviser'])) {
            $ticketOrgId = $ticket->user->org_id ?? null;
            if ($ticketOrgId !== $user->org_id) {
                throw ValidationException::withMessages([
                    'selectedEventId' => 'You are not authorized to reschedule this event.',
                ]);
            }
        }

        if (!in_array($ticket->status, ['approved', 'for_rescheduling'])) {
            throw ValidationException::withMessages([
                'selectedEventId' => 'This event cannot be rescheduled in its current status.',
            ]);
        }
    }

    public function updatedSupportingDocuments()
    {
        // Handle single file upload (S3 driver doesn't support multiple)
        // Wrap single file in array for consistent processing
        $files = $this->supportingDocuments;
        if (!is_array($files)) {
            $files = $files ? [$files] : [];
        }

        // If no files, return early
        if (empty($files)) {
            return;
        }

        // Get existing documents that are already validated
        $existingDocs = collect($this->supportingDocuments)->filter(fn($doc) => is_object($doc) && $doc !== $files[0] ?? null)->values()->all();

        // Temporarily set as array for validation
        $this->supportingDocuments = $files;

        $this->validate([
            'supportingDocuments' => 'array|max:10',
            'supportingDocuments.*' => [
                'file',
                'max:' . self::MAX_FILE_SIZE,
                'mimes:' . self::ALLOWED_MIMES,
            ],
        ]);

        // Append new files to existing documents
        $this->supportingDocuments = array_merge($existingDocs, $files);
    }

    public function removeAttachment($index)
    {
        if (!is_numeric($index) || $index < 0 || $index >= count($this->supportingDocuments)) {
            return;
        }

        array_splice($this->supportingDocuments, (int) $index, 1);
    }

    public function getPreviewTicketProperty()
    {
        if (!$this->selectedEventId) {
            return null;
        }

        $ticket = $this->getBaseTicketsQuery()->find($this->selectedEventId);
        if (!$ticket) {
            return null;
        }

        // Clone to avoid modifying original
        $previewTicket = clone $ticket;

        // Apply changes
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

        // Merge supporting documents
        if (!empty($this->supportingDocuments)) {
            $previewAttachments = $this->createPreviewAttachments();
            $existingAttachments = $ticket->attachments ?? collect();
            $previewTicket->setRelation('attachments', $existingAttachments->merge($previewAttachments));
        }

        return $previewTicket;
    }

    private function createPreviewAttachments()
    {
        return collect($this->supportingDocuments)->map(function ($file) {
            $attachment = new Attachment();
            $attachment->file_name = $file->getClientOriginalName();
            $attachment->file_type = $file->getMimeType();
            $attachment->file_path = null;
            return $attachment;
        });
    }

    public function submitReschedule()
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        DB::beginTransaction();

        try {
            // Final validation
            $this->validateCurrentStep();

            // Load and authorize ticket
            $ticket = auth()->user()->tickets()->lockForUpdate()->findOrFail($this->selectedEventId);
            $this->authorize('update', $ticket);

            // Validate timing constraints
            $this->validateRescheduleTiming($ticket);

            // Apply changes
            $changes = $this->applyScheduleChanges($ticket);

            // Store supporting documents
            if (!empty($this->supportingDocuments)) {
                $this->storeSupportingDocuments($ticket);
            }

            // Update ticket status
            $ticket->status = 'amended';
            $ticket->save();

            // Notify OSA admins
            $this->notifyOSAAdmins($ticket);

            DB::commit();

            $this->toast(
                type: 'success',
                title: 'Reschedule Request Submitted',
                description: "Your reschedule request for {$ticket->ticket_number} has been submitted successfully.",
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                timeout: 3000,
                redirectTo: route('student-org.dashboard')
            );
        } catch (ValidationException $e) {
            DB::rollBack();
            $this->isProcessing = false;
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Reschedule submission failed', [
                'user_id' => auth()->id(),
                'ticket_id' => $this->selectedEventId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toast(
                type: 'error',
                title: 'Submission Failed',
                description: 'An error occurred while submitting your reschedule request. Please try again.',
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                timeout: 5000
            );
        } finally {
            $this->isProcessing = false;
        }
    }

    private function validateRescheduleTiming(Ticket $ticket): void
    {
        $daysUntilEvent = now()->diffInDays(Carbon::parse($ticket->date_from), false);

        if ($daysUntilEvent < self::MIN_RESCHEDULE_DAYS) {
            throw ValidationException::withMessages([
                'general' => 'Reschedule requests must be submitted at least ' . self::MIN_RESCHEDULE_DAYS . ' days before the event date.',
            ]);
        }
    }

    private function applyScheduleChanges(Ticket $ticket): array
    {
        $changes = [];

        if ($this->changeDate) {
            $changes['date_from'] = ['old' => $ticket->date_from, 'new' => $this->newStartDate];
            $changes['date_to'] = ['old' => $ticket->date_to, 'new' => $this->newEndDate];
            $ticket->date_from = $this->newStartDate;
            $ticket->date_to = $this->newEndDate;
        }

        if ($this->changeTime) {
            $changes['time_from'] = ['old' => $ticket->time_from, 'new' => $this->newStartTime];
            $changes['time_to'] = ['old' => $ticket->time_to, 'new' => $this->newEndTime];
            $ticket->time_from = $this->newStartTime;
            $ticket->time_to = $this->newEndTime;
        }

        if ($this->changeVenue) {
            $changes['venue_requested'] = ['old' => $ticket->venue_requested, 'new' => $this->newVenue];
            $ticket->venue_requested = $this->newVenue;

            if ($this->alternativeVenue) {
                $changes['alternate_venue'] = ['old' => $ticket->alternate_venue, 'new' => $this->alternativeVenue];
                $ticket->alternate_venue = $this->alternativeVenue;
            }
        }

        // Log changes for audit trail
        Log::info('Ticket rescheduled', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => auth()->id(),
            'changes' => $changes,
        ]);

        return $changes;
    }

    private function storeSupportingDocuments(Ticket $ticket): void
    {
        foreach ($this->supportingDocuments as $file) {
            $originalName = $file->getClientOriginalName();

            // Sanitize filename
            $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);
            $filename = time() . '_' . uniqid() . '_' . $safeName;

            // Store file securely using configured disk (R2/S3 in production)
            $path = $file->storeAs(
                "tickets/{$ticket->ticket_id}/attachments",
                $filename,
                config('filesystems.default')
            );

            // Create attachment record
            Attachment::create([
                'ticket_id' => $ticket->ticket_id,
                'file_name' => $originalName,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }

    private function notifyOSAAdmins(Ticket $ticket): void
    {
        $osaUsers = User::where('role_id', User::getRoleId('osa'))
            ->get();

        foreach ($osaUsers as $osaUser) {
            try {
                $osaUser->notify(new TicketSubmittedNotification($ticket));
            } catch (\Exception $e) {
                Log::error('Failed to notify OSA admin', [
                    'admin_id' => $osaUser->user_id,
                    'ticket_id' => $ticket->ticket_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function getBaseTicketsQuery()
    {
        $user = auth()->user();
        $query = \App\Models\Ticket::query();

        if ($user->position->position_name === 'President') {
            $query->where('user_id', $user->user_id);
        } elseif (in_array($user->position->position_name, ['Chairperson', 'Adviser'])) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->withTrashed()->where('org_id', $user->org_id);
            });
        }

        return $query;
    }

    public function render()
    {
        $approvedEvents = $this->getBaseTicketsQuery()
            ->whereIn('status', ['approved', 'for_rescheduling'])
            ->where('date_from', '>=', now()->addDays(self::MIN_RESCHEDULE_DAYS))
            ->get()
            ->map(fn($ticket) => [
                'id' => $ticket->ticket_id,
                'name' => "{$ticket->ticket_number} - {$ticket->title} (" .
                    Carbon::parse($ticket->date_from)->format('M d, Y') . ")",
            ]);

        $selectedEvent = $this->selectedEventId
            ? $this->getBaseTicketsQuery()->find($this->selectedEventId)
            : null;

        return view('livewire.student-org.reschedule', [
            'approvedEvents' => $approvedEvents,
            'selectedEvent' => $selectedEvent,
        ]);
    }
}
