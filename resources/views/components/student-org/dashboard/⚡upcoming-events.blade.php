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
        <x-mary-card title="Upcoming Approved Events" subtitle="Events scheduled for the next 30 days">
            <div class="animate-pulse space-y-4">
                <div class="h-20 bg-base-300 rounded"></div>
                <div class="h-20 bg-base-300 rounded"></div>
            </div>
        </x-mary-card>
        HTML;
    }
};
?>

<x-mary-card title="Upcoming Approved Events" subtitle="Events scheduled for the next 30 days">
    <x-slot:menu>
        <x-mary-button label="View Calendar" link="/student-org/calendar" icon="s-calendar"
            class="btn-sm btn-ghost" wire:navigate />
    </x-slot:menu>

    <div class="space-y-4">
        @forelse($this->upcomingEvents as $event)
            <x-tickets.upc-events-card :ticket="$event" />
        @empty
            <x-ui.empty-state title="No upcoming approved events"
                description="Approved events within the next 30 days will be listed here."
                icon="o-calendar-days" tone="info" iconColor="text-info" actionLabel="Open Calendar"
                actionLink="/student-org/calendar" />
        @endforelse
    </div>
</x-mary-card>
