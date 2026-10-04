<?php

use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    #[Computed]
    public function upcomingEvents()
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

        return $query
            ->with(['user' => fn ($q) => $q->withTrashed()])
            ->where('status', 'approved')
            ->whereBetween('date_from', [now(), now()->addDays(30)])
            ->orderBy('date_from', 'asc')
            ->get();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <x-ui.card title="Upcoming events" subtitle="Your approved events in the next 30 days.">
            <div class="animate-pulse space-y-3" aria-hidden="true">
                <div class="h-11 rounded-field bg-base-200"></div>
                <div class="h-11 rounded-field bg-base-200"></div>
            </div>
        </x-ui.card>
        HTML;
    }
};
?>

<x-ui.card title="Upcoming events" subtitle="Your approved events in the next 30 days.">
    <x-slot:menu>
        <x-ui.button label="Calendar" link="{{ route('student-org.calendar') }}" class="btn-sm btn-ghost" />
    </x-slot:menu>

    @if ($this->upcomingEvents->isEmpty())
        <x-ui.empty-state compact icon="o-calendar-days" title="Nothing scheduled yet"
            description="Approved events in the next 30 days appear here." />
    @else
        <ul class="space-y-3">
            @foreach ($this->upcomingEvents as $event)
                <li wire:key="upcoming-{{ $event->ticket_id }}"><x-tickets.upc-events-card :ticket="$event" /></li>
            @endforeach
        </ul>
    @endif
</x-ui.card>
