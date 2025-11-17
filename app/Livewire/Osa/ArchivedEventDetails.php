<?php

namespace App\Livewire\Osa;

use App\Models\Event;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class ArchivedEventDetails extends Component
{
    public int $eventId;

    public ?Event $event = null;

    public function mount(int $eventId): void
    {
        $this->eventId = $eventId;

        $this->event = Event::select(['event_id', 'ticket_id', 'event__type_id', 'notes', 'created_at'])
            ->with([
                'ticket' => fn ($q) => $q
                    ->select(['ticket_id', 'ticket_number', 'title', 'description', 'status', 'user_id', 'venue_requested', 'total_participants', 'created_at', 'updated_at'])
                    ->with([
                        'user' => fn ($q) => $q->select(['user_id', 'org_id'])->with('studentOrganization:org_id,org_name,logo'),
                        'latestOsaApproval:osa_approval_id,user_id,decision,remarks,created_at',
                        'attachments:attachment_id,ticket_id,file_path,file_name,file_type',
                    ]),
                'eventSchedules:schedule_id,event_id,start_date,end_date,start_time,end_time,venue',
                'eventType:event_type_id,type_name',
            ])
            ->find($this->eventId);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="space-y-3 animate-pulse">
            <div class="h-6 bg-base-200 rounded"></div>
            <div class="h-4 bg-base-200 rounded"></div>
            <div class="h-4 bg-base-200 rounded w-3/4"></div>
            <div class="h-4 bg-base-200 rounded w-5/6"></div>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.osa.archived-event-details');
    }
}
