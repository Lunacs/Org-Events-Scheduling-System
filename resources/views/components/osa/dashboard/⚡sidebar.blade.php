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
        <div class="col-span-1 space-y-4">
            <div class="bg-white dark:bg-base-200 rounded-2xl shadow-sm p-6 animate-pulse">
                <div class="h-5 bg-base-300 rounded w-1/2 mb-4"></div>
                <div class="space-y-3">
                    <div class="h-16 bg-base-300 rounded"></div>
                    <div class="h-16 bg-base-300 rounded"></div>
                </div>
            </div>
        </div>
        HTML;
    }
};
?>

<div class="col-span-1">
    @php
        $upcomingEvents = $this->upcomingEvents;
        $todaysSummary = $this->todaysSummary;
    @endphp

    <x-ui.card class="shadow-md md:sticky top-6" title="Upcoming Events" subtitle="Next 30 days">
        <x-slot:menu>
            <x-ui.button icon="o-calendar" link="/admin/calendar" class="btn-sm btn-ghost" wire:navigate />
        </x-slot:menu>

        @if (count($upcomingEvents) > 0)
            <div class="space-y-3">
                @foreach ($upcomingEvents as $index => $event)
                    <div class="group p-3 bg-base-200/60 rounded-lg border border-base-300 hover:border-primary/40 transition-colors"
                        wire:key="upcoming-{{ $index }}">
                        <div class="flex items-start gap-3">
                            <div class="text-center bg-primary text-primary-content rounded-lg p-2 min-w-[48px]">
                                <div class="text-xs font-medium">
                                    {{ \Carbon\Carbon::parse($event['date'])->format('M') }}
                                </div>
                                <div class="text-xl font-bold">
                                    {{ \Carbon\Carbon::parse($event['date'])->format('d') }}
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-sm text-base-content mb-1 truncate">
                                    {{ $event['title'] }}
                                </h4>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1 text-xs text-base-content/60">
                                        <x-ui.icon name="o-user-group" class="w-3 h-3" />
                                        <span class="truncate">{{ $event['organization'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-xs text-base-content/60">
                                        <x-ui.icon name="o-map-pin" class="w-3 h-3" />
                                        <span class="truncate">{{ $event['venue'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t">
                <x-ui.button label="View Full Calendar" icon-right="o-arrow-right"
                    class="btn-sm btn-block btn-outline" link="/admin/calendar" wire:navigate />
            </div>
        @else
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-base-200 mb-3">
                    <x-ui.icon name="o-calendar-days" class="w-8 h-8 text-base-content/40" />
                </div>
                <p class="text-sm text-base-content/60 font-medium">No upcoming events</p>
                <p class="text-xs text-base-content/40 mt-1">Approved events will appear here</p>
            </div>
        @endif
    </x-ui.card>

    <div class="bg-base-100 border border-base-300 rounded-2xl shadow-sm p-5 mt-4">
        <h3 class="text-md font-bold text-base-content mb-4">Today's Summary</h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-base-content/60">New Requests</span>
                <span class="font-bold text-base-content">{{ $todaysSummary['newRequests'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-base-content/60">Processed</span>
                <span class="font-bold text-success">{{ $todaysSummary['processed'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-base-content/60">Pending Review</span>
                <span class="font-bold text-warning">{{ $todaysSummary['pending'] }}</span>
            </div>
        </div>
    </div>
</div>
