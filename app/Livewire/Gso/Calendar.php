<?php

namespace App\Livewire\Gso;

use App\Models\Office_Approval;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Calendar extends Component
{
    #[Title('Event Calendar - GSO')]
    #[Layout('components.layouts.gso-layout')]
    public function render()
    {
        $user = Auth::user();
        $officeId = $user?->office_id;

        $events = Office_Approval::query()
            ->with(['ticket.eventType', 'ticket.user.studentOrganization'])
            ->when($officeId, fn(Builder $query) => $query->where('office_id', $officeId))
            ->where('decision', 'approved')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn(Office_Approval $approval) => $this->mapApprovalToEvent($approval, $officeId))
            ->filter()
            ->values()
            ->all();

        return view('livewire.gso.calendar', [
            'events' => $events,
        ]);
    }

    protected function mapApprovalToEvent(Office_Approval $approval, ?int $userOfficeId): ?array
    {
        $ticket = $approval->ticket;

        if (! $ticket) {
            return null;
        }

        $eventDate = $this->parseDate($ticket->getAttribute('date_from'));
        $timeFrom = $this->parseTime($ticket->getAttribute('time_from'));
        $timeTo = $this->parseTime($ticket->getAttribute('time_to'));

        $requirements = collect(preg_split('/[\,\n]+/', (string) ($ticket->special_requirements ?? '')))
            ->map(fn(string $item) => trim($item))
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $approval->id,
            'title' => $ticket->title ?? 'Untitled Event',
            'organization' => $ticket->user?->studentOrganization?->org_name
                ?? $ticket->user?->name
                ?? 'N/A',
            'date' => $eventDate?->format('Y-m-d') ?? optional($ticket->created_at)->format('Y-m-d') ?? Carbon::today()->format('Y-m-d'),
            'time' => $this->formatTimeRange($timeFrom, $timeTo),
            'start_time' => $timeFrom?->format('H:i'),
            'end_time' => $timeTo?->format('H:i'),
            'start_minutes' => $this->timeToMinutes($timeFrom),
            'end_minutes' => $this->timeToMinutes($timeTo),
            'venue' => $ticket->venue_requested ?? 'TBD',
            'status' => 'approved',
            'attendees' => $ticket->total_participants ? (string) $ticket->total_participants : '0',
            'description' => $ticket->description ?? 'No description provided.',
            'gso_requirements' => $requirements,
            'office_involved' => $userOfficeId ? $approval->office_id === $userOfficeId : false,
        ];
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseTime(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', $value);
        } catch (\Throwable) {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                return null;
            }
        }
    }

    protected function formatTimeRange(?Carbon $from, ?Carbon $to): string
    {
        if (! $from && ! $to) {
            return '—';
        }

        if ($from && $to) {
            return $from->format('H:i') . ' - ' . $to->format('H:i');
        }

        $time = $from ?? $to;

        return $time?->format('H:i') ?? '—';
    }

    protected function timeToMinutes(?Carbon $time): ?int
    {
        return $time ? ($time->hour * 60 + $time->minute) : null;
    }
}
