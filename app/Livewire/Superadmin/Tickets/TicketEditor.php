<?php

namespace App\Livewire\Superadmin\Tickets;

use App\Models\Event_Type;
use App\Models\Fund_Sources;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Venue;
use App\Services\TransactionLogService;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class TicketEditor extends Component
{
    use Toast;

    private const VALID_STATUSES = [
        'received',
        'gso_review',
        'pending_osa_approval',
        'for_revision',
        'approved',
        'amended',
        'completed',
    ];

    public $ticketId = null;

    public $isEditing = false;

    // Form fields
    public $user_id = '';

    public $event_type_id = '';

    public $title = '';

    public $description = '';

    public $date_from = '';

    public $date_to = '';

    public $time_from = '';

    public $time_to = '';

    public $venue_requested = '';

    public $venue_other = '';

    public $alternate_venue = '';

    public $alternate_venue_other = '';

    public $plv_participants = '';

    public $external_participants = '';

    public $estimated_budget = '';

    public $fund_source_id = '';

    public $budget_breakdown = '';

    public $special_requirements = '';

    public $additional_notes = '';

    public $status = 'received';

    #[Title('Ticket Editor')]
    #[Layout('components.layouts.superadmin')]
    public function mount($id = null): void
    {
        if ($id) {
            $this->fillFromTicket(Ticket::findOrFail($id));
        }
    }

    protected function rules(): array
    {
        $rules = [
            'user_id' => 'required|exists:users,user_id',
            'event_type_id' => 'required|exists:event__types,event_type_id',
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20|max:2000',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'time_from' => 'required',
            'time_to' => 'required',
            'plv_participants' => 'required|integer|min:1',
            'external_participants' => 'nullable|integer|min:0',
            'estimated_budget' => 'nullable|numeric|min:0|max:999999999.99',
            'fund_source_id' => 'nullable',
            'budget_breakdown' => 'nullable|string|max:2000',
            'special_requirements' => 'nullable|string|max:2000',
            'additional_notes' => 'nullable|string|max:2000',
            'status' => 'required|in:'.implode(',', self::VALID_STATUSES),
        ];

        if ($this->venue_requested === 'other') {
            $rules['venue_other'] = 'required|string|max:255|min:3';
        } else {
            $rules['venue_requested'] = 'required|exists:venues,venue_id';
        }

        if ($this->alternate_venue === 'other') {
            $rules['alternate_venue_other'] = 'required|string|max:255|min:3';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'user_id.required' => 'Please select an organization user.',
            'event_type_id.required' => 'Event type is required.',
            'title.required' => 'Event title is required.',
            'title.min' => 'Title must be at least 5 characters.',
            'description.required' => 'Description is required.',
            'description.min' => 'Description must be at least 20 characters.',
            'date_from.required' => 'Start date is required.',
            'date_to.required' => 'End date is required.',
            'date_to.after_or_equal' => 'End date must be on or after start date.',
            'time_from.required' => 'Start time is required.',
            'time_to.required' => 'End time is required.',
            'venue_requested.required' => 'Venue is required.',
            'venue_other.required' => 'Please specify the venue name.',
            'plv_participants.required' => 'PLV participants count is required.',
            'plv_participants.min' => 'Must have at least 1 PLV participant.',
            'status.required' => 'Status is required.',
        ];
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $plv = (int) $this->plv_participants;
            $ext = (int) ($this->external_participants ?: 0);

            $data = [
                'user_id' => $this->user_id,
                'event_type_id' => (int) $this->event_type_id,
                'title' => $this->title,
                'description' => $this->description,
                'date_from' => $this->date_from,
                'date_to' => $this->date_to,
                'time_from' => $this->time_from,
                'time_to' => $this->time_to,
                'venue_requested' => $this->venue_requested === 'other' ? null : $this->toNullableInt($this->venue_requested),
                'venue_other' => $this->venue_requested === 'other' ? $this->venue_other : $this->toNullableValue($this->venue_other),
                'alternate_venue' => $this->alternate_venue === 'other' ? null : $this->toNullableInt($this->alternate_venue),
                'alternate_venue_other' => $this->alternate_venue === 'other' ? $this->alternate_venue_other : $this->toNullableValue($this->alternate_venue_other),
                'plv_participants' => $plv,
                'external_participants' => $this->toNullableInt($this->external_participants),
                'total_participants' => $plv + $ext,
                'estimated_budget' => $this->estimated_budget ? (float) $this->estimated_budget : null,
                'fund_source_id' => $this->toNullableInt($this->fund_source_id),
                'budget_breakdown' => $this->toNullableValue($this->budget_breakdown),
                'special_requirements' => $this->toNullableValue($this->special_requirements),
                'additional_notes' => $this->toNullableValue($this->additional_notes),
                'status' => $this->status,
            ];

            if ($this->isEditing) {
                $ticket = Ticket::findOrFail($this->ticketId);
                $changes = [];

                if ($ticket->title !== $this->title) {
                    $changes[] = "Title: {$ticket->title} -> {$this->title}";
                }
                if ((int) $ticket->user_id !== (int) $this->user_id) {
                    $changes[] = 'Organization user changed';
                }
                if ((int) $ticket->event_type_id !== (int) $this->event_type_id) {
                    $changes[] = 'Event type changed';
                }
                if ($ticket->status !== $this->status) {
                    $changes[] = "Status: {$ticket->status} -> {$this->status}";
                }
                if ($ticket->date_from !== $this->date_from) {
                    $changes[] = 'Start date changed';
                }
                if ($ticket->date_to !== $this->date_to) {
                    $changes[] = 'End date changed';
                }

                $ticket->update($data);

                TransactionLogService::logTicketOperation('updated', $ticket, $changes);

                DB::commit();
                $this->success('Ticket updated successfully!', position: 'toast-top');
            } else {
                $user = User::with('studentOrganization')->findOrFail($this->user_id);
                $orgCode = $user->studentOrganization?->org_code ?? 'ADM';

                $lastTicket = Ticket::lockForUpdate()
                    ->where('ticket_number', 'LIKE', "TKT-{$orgCode}-%")
                    ->orderByRaw('CAST(SUBSTRING(ticket_number, LOCATE(\'-\', ticket_number, 5) + 1) AS UNSIGNED) DESC')
                    ->first();

                $nextNumber = $lastTicket
                    ? ((int) substr(strrchr($lastTicket->ticket_number, '-'), 1)) + 1
                    : 1;

                $data['ticket_number'] = "TKT-{$orgCode}-".str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                $ticket = Ticket::create($data);

                TransactionLogService::logTicketOperation('created', $ticket);

                DB::commit();
                $this->success('Ticket created successfully!', position: 'toast-top');
            }

            return $this->redirect(route('superadmin.tickets'), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Ticket save failed', ['error' => $e->getMessage()]);
            $this->error('Failed to save ticket: '.$e->getMessage(), position: 'toast-top');
        }
    }

    public function cancel()
    {
        return $this->redirect(route('superadmin.tickets'), navigate: true);
    }

    private function fillFromTicket(Ticket $ticket): void
    {
        $this->ticketId = $ticket->ticket_id;
        $this->isEditing = true;

        $this->user_id = $ticket->user_id;
        $this->event_type_id = $ticket->event_type_id;
        $this->title = $ticket->title;
        $this->description = $ticket->description ?? '';
        $this->date_from = $ticket->date_from;
        $this->date_to = $ticket->date_to;
        $this->time_from = $ticket->time_from ? substr($ticket->time_from, 0, 5) : '';
        $this->time_to = $ticket->time_to ? substr($ticket->time_to, 0, 5) : '';
        $this->venue_requested = $ticket->venue_requested ?? ($ticket->venue_other ? 'other' : '');
        $this->venue_other = $ticket->venue_other ?? '';
        $this->alternate_venue = $ticket->alternate_venue ?? ($ticket->alternate_venue_other ? 'other' : '');
        $this->alternate_venue_other = $ticket->alternate_venue_other ?? '';
        $this->plv_participants = $ticket->plv_participants ?? '';
        $this->external_participants = $ticket->external_participants ?? '';
        $this->estimated_budget = $ticket->estimated_budget ?? '';
        $this->fund_source_id = $ticket->fund_source_id ?? '';
        $this->budget_breakdown = $ticket->budget_breakdown ?? '';
        $this->special_requirements = $ticket->special_requirements ?? '';
        $this->additional_notes = $ticket->additional_notes ?? '';
        $this->status = $ticket->status ?? 'received';
    }

    private function toNullableValue(mixed $value): mixed
    {
        return $value === '' || $value === null ? null : $value;
    }

    private function toNullableInt(mixed $value): ?int
    {
        return $value === '' || $value === null ? null : (int) $value;
    }

    // ── Computed Dropdown Data ───────────────────────────────────────

    #[Computed(persist: true, seconds: 1800)]
    public function orgUsers()
    {
        return User::select(['user_id', 'name', 'org_id'])
            ->whereHas('role', fn ($q) => $q->where('role_name', 'student-org'))
            ->with('studentOrganization:org_id,org_name,org_code')
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->user_id,
                'name' => $u->name.' ('.($u->studentOrganization?->org_name ?? 'N/A').')',
            ]);
    }

    #[Computed(persist: true, seconds: 1800)]
    public function eventTypes()
    {
        return Event_Type::select(['event_type_id', 'type_name'])
            ->orderBy('type_name')
            ->get();
    }

    #[Computed(persist: true, seconds: 1800)]
    public function venuesList()
    {
        return Venue::select(['venue_id', 'venue_name'])
            ->where('is_active', true)
            ->orderBy('venue_name')
            ->get();
    }

    #[Computed(persist: true, seconds: 1800)]
    public function fundSources()
    {
        return Fund_Sources::select(['source_id', 'source_name'])
            ->orderBy('source_name')
            ->get();
    }

    public function render()
    {
        return view('livewire.superadmin.tickets.ticket-editor');
    }
}
