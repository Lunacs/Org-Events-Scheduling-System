<?php

use App\Models\Ticket;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    protected array $osaActionStatuses = ['received', 'amended', 'pending_osa_approval'];

    #[Computed(persist: true, seconds: 600)]
    public function upcomingEvents(): array
    {
        return Cache::remember('osa_dashboard_upcoming_events', 300, function () {
            return Ticket::select(['ticket_id', 'title', 'date_from', 'venue_requested', 'venue_other', 'user_id'])
                ->with([
                    'user' => fn ($q) => $q->withTrashed()->select(['user_id', 'org_id'])
                        ->with('studentOrganization:org_id,org_name,logo'),
                ])
                ->where('status', 'approved')
                ->where('date_from', '>=', now())
                ->orderBy('date_from', 'asc')
                ->limit(5)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'title' => $ticket->title,
                        'organization' => $ticket->user?->studentOrganization?->org_name ?? 'N/A',
                        'date' => $ticket->date_from
                            ? \Carbon\Carbon::parse($ticket->date_from)->format('M d, Y')
                            : 'TBD',
                        'venue' => $ticket->venue_display_name ?? 'TBD',
                    ];
                })
                ->toArray();
        });
    }

    #[Computed(persist: true, seconds: 600)]
    public function todaysSummary(): array
    {
        return Cache::remember('osa_dashboard_todays_summary', 300, function () {
            $today = now()->startOfDay();

            return [
                'newRequests' => Ticket::whereDate('created_at', $today)->count(),
                'processed' => Ticket::whereIn('status', ['approved', 'for_revision', 'gso_review'])
                    ->whereDate('updated_at', $today)
                    ->count(),
                'pending' => Ticket::whereIn('status', $this->osaActionStatuses)->count(),
            ];
        });
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="space-y-6" aria-hidden="true">
            <div class="animate-pulse rounded-box border border-base-300 bg-base-100 p-5">
                <div class="mb-4 h-4 w-1/2 rounded bg-base-200"></div>
                <div class="space-y-3">
                    <div class="h-11 rounded-field bg-base-200"></div>
                    <div class="h-11 rounded-field bg-base-200"></div>
                    <div class="h-11 rounded-field bg-base-200"></div>
                </div>
            </div>
        </div>
        HTML;
    }
};
?>

<div class="space-y-6">
    @php
        $upcomingEvents = $this->upcomingEvents;
        $todaysSummary = $this->todaysSummary;
    @endphp

    <x-ui.card title="Upcoming events" subtitle="Approved events in the next 30 days.">
        <x-slot:menu>
            <x-ui.button label="Calendar" link="{{ route('admin.calendar') }}" class="btn-sm btn-ghost" />
        </x-slot:menu>

        @if (count($upcomingEvents) > 0)
            <ul class="space-y-3">
                @foreach ($upcomingEvents as $index => $event)
                    @php $date = \Carbon\Carbon::parse($event['date']); @endphp
                    <li class="flex items-center gap-3" wire:key="upcoming-{{ $index }}">
                        <div class="flex w-12 shrink-0 flex-col items-center rounded-field border border-base-300 py-1 leading-none"
                            aria-hidden="true">
                            <span class="text-[0.6875rem] font-medium text-base-content/70">{{ $date->format('M') }}</span>
                            <span class="mt-0.5 text-lg font-semibold tabular-nums text-base-content">{{ $date->format('j') }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-base-content">
                                <span class="sr-only">{{ $date->format('F j') }}: </span>{{ $event['title'] }}
                            </p>
                            <p class="truncate text-xs text-base-content/70">{{ $event['organization'] }}</p>
                            <p class="truncate text-xs text-base-content/70">{{ $event['venue'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <x-ui.empty-state compact icon="o-calendar-days" title="No upcoming events"
                description="Approved events in the next 30 days appear here." />
        @endif
    </x-ui.card>

    <x-ui.card title="Today">
        <dl class="-my-1 divide-y divide-base-300 text-sm">
            <div class="flex items-center justify-between py-2">
                <dt class="text-base-content/70">New requests</dt>
                <dd class="font-semibold tabular-nums text-base-content">{{ $todaysSummary['newRequests'] }}</dd>
            </div>
            <div class="flex items-center justify-between py-2">
                <dt class="text-base-content/70">Processed</dt>
                <dd class="font-semibold tabular-nums text-base-content">{{ $todaysSummary['processed'] }}</dd>
            </div>
            <div class="flex items-center justify-between py-2">
                <dt class="text-base-content/70">Still in your queue</dt>
                <dd class="font-semibold tabular-nums text-base-content">{{ $todaysSummary['pending'] }}</dd>
            </div>
        </dl>
    </x-ui.card>
</div>
