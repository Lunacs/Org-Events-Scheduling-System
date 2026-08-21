<?php

namespace App\Livewire\StudentOrg;

use App\Models\Ticket;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class TicketDetails extends Component
{
    use Toast;

    #[Title('Ticket Details - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]
    public Ticket $ticket;

    public $showEditDrawer = false;

    public function mount($ticketNumber)
    {
        $user = auth()->user();

        $query = Ticket::select([
            'ticket_id',
            'ticket_number',
            'title',
            'description',
            'status',
            'date_from',
            'date_to',
            'time_from',
            'time_to',
            'venue_requested',
            'venue_other',
            'alternate_venue',
            'alternate_venue_other',
            'special_requirements',
            'plv_participants',
            'external_participants',
            'total_participants',
            'estimated_budget',
            'budget_breakdown',
            'igp_requested',
            'igp_details',
            'oc_accommodation',
            'oc_tsp',
            'oc_driver_name',
            'oc_driver_contact_number',
            'oc_transportation_type',
            'oc_vehicle_plate_number',
            'additional_notes',
            'proponent_contact',
            'adviser_contact',
            'user_id',
            'event_type_id',
            'fund_source_id',
            'created_at',
            'updated_at',
        ])->with([
            'user' => fn ($q) => $q->withTrashed()
                ->select(['user_id', 'name', 'email', 'role_id', 'org_id', 'position_id', 'avatar_style', 'avatar_seed']),
            'user.role:role_id,role_name',
            'user.studentOrganization:org_id,org_name,org_code,course_id,adviser_name,logo',
            'user.studentOrganization.course:course_id,course_name',
            'user.position:position_id,position_name',
            'events:event_id,ticket_id,event__type_id,notes',
            'events.eventSchedules:schedule_id,event_id,start_date,end_date,start_time,end_time,venue,status',
            'attachments:attachment_id,ticket_id,file_path,file_name,file_type',
            'eventType:event_type_id,type_name',
            'fundSource:source_id,source_name',
            'comments:id,ticket_id,user_id,content,created_at',
            'comments.user' => fn ($q) => $q->withTrashed()
                ->select(['user_id', 'name', 'role_id', 'avatar_style', 'avatar_seed']),
            'comments.user.role:role_id,role_name',
            'osaApprovals:osa_approval_id,ticket_id,user_id,decision,remarks,created_at',
            'osaApprovals.user' => fn ($q) => $q->withTrashed()
                ->select(['user_id', 'name', 'role_id', 'avatar_style', 'avatar_seed']),
            'osaApprovals.user.role:role_id,role_name',
            'officeApprovals:id,ticket_id,office_id,user_id,decision,remarks,created_at',
            'officeApprovals.office:office_id,office_name',
            'officeApprovals.user' => fn ($q) => $q->withTrashed()
                ->select(['user_id', 'name', 'role_id', 'avatar_style', 'avatar_seed']),
            'officeApprovals.user.role:role_id,role_name',
        ])->where('ticket_number', $ticketNumber);

        // Apply visibility based on user's position
        if ($user->position->position_name === 'President') {
            $query->where('user_id', $user->user_id);
        } elseif (in_array($user->position->position_name, ['Chairperson', 'Adviser'])) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->withTrashed()->where('org_id', $user->org_id);
            });
        }

        $this->ticket = $query->firstOrFail();
    }

    public function openEditDrawer()
    {
        $this->showEditDrawer = true;
    }

    public function closeEditDrawer()
    {
        $this->showEditDrawer = false;
    }

    /**
     * Generate a temporary URL and open in a new tab for preview.
     */
    public function previewAttachment(int $attachmentId): void
    {
        $attachment = $this->ticket->attachments->firstWhere('attachment_id', $attachmentId);

        if (! $attachment) {
            $this->warning('Attachment not found.');

            return;
        }

        $url = $this->makeTemporaryUrl($attachment->attachment_id, false);
        $this->dispatch('open-attachment-preview', url: $url);
    }

    /**
     * Generate a temporary URL that forces download and dispatch it for JavaScript handling.
     */
    public function downloadAttachment(int $attachmentId): void
    {
        $attachment = $this->ticket->attachments->firstWhere('attachment_id', $attachmentId);

        if (! $attachment) {
            $this->warning('Attachment not found.');

            return;
        }

        $url = $this->makeTemporaryUrl($attachment->attachment_id, true);
        $this->dispatch('download-attachment', url: $url, filename: $attachment->file_name);
    }

    /**
     * Build a temporary URL from the configured filesystem.
     */
    private function makeTemporaryUrl(int $attachmentId, bool $forceDownload = false): string
    {
        $routeName = $forceDownload ? 'attachments.download' : 'attachments.preview';

        return URL::temporarySignedRoute(
            $routeName,
            now()->addMinutes(5),
            ['attachment' => $attachmentId]
        );
    }

    public function render()
    {
        return view('livewire.student-org.ticket-details');
    }
}
