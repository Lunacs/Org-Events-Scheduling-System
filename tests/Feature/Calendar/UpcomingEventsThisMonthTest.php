<?php

use App\Livewire\Components\EventCalendar;
use App\Models\Event;
use App\Models\Event_Schedule;
use App\Models\Roles;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
    Carbon::setTestNow(Carbon::parse('2026-07-19'));

    $role = Roles::firstOrCreate(['role_name' => 'osa']);
    $this->osaUser = User::factory()->create(['role_id' => $role->role_id]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function createScheduledEvent(string $ticketStatus, string $scheduleStatus, string $startDate): Event_Schedule
{
    $ticket = Ticket::factory()->create(['status' => $ticketStatus]);
    $event = Event::factory()->create(['ticket_id' => $ticket->ticket_id]);

    return Event_Schedule::create([
        'event_id' => $event->event_id,
        'status' => $scheduleStatus,
        'start_date' => $startDate,
        'end_date' => $startDate,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'venue' => 'Test Venue',
    ]);
}

it('shows completed events in the calendar and exposes them via completedEventsThisMonth', function () {
    $schedule = createScheduledEvent('completed', 'approved', '2026-07-10');

    $component = Livewire::actingAs($this->osaUser)->test(EventCalendar::class)
        ->set('currentDate', Carbon::parse('2026-07-19'));

    $calendarEventIds = collect($component->get('eventsForCalendar'))->pluck('id');
    $upcomingEventIds = collect($component->get('upcomingEventsThisMonth'))->pluck('event_id');
    $completedEventIds = collect($component->get('completedEventsThisMonth'))->pluck('event_id');

    // Calendar already shows the completed event.
    expect($calendarEventIds)->toContain($schedule->event_id);

    // Bug fix target: completed event belongs in the new completed group,
    // not the "upcoming" group, but must be visible somewhere in the section.
    expect($completedEventIds)->toContain($schedule->event_id);
    expect($upcomingEventIds)->not->toContain($schedule->event_id);
});

it('keeps approved future events in the upcoming events list', function () {
    $schedule = createScheduledEvent('approved', 'approved', '2026-07-25');

    $component = Livewire::actingAs($this->osaUser)->test(EventCalendar::class)
        ->set('currentDate', Carbon::parse('2026-07-19'));

    $upcomingEventIds = collect($component->get('upcomingEventsThisMonth'))->pluck('event_id');

    expect($upcomingEventIds)->toContain($schedule->event_id);
});

it('excludes completed events from a different month', function () {
    createScheduledEvent('completed', 'approved', '2026-06-10');

    $component = Livewire::actingAs($this->osaUser)->test(EventCalendar::class)
        ->set('currentDate', Carbon::parse('2026-07-19'));

    expect($component->get('completedEventsThisMonth'))->toBeEmpty();
});

it('recomputes upcoming and completed lists after clearFilters resets the computed memo', function () {
    $completed = createScheduledEvent('completed', 'approved', '2026-07-10');
    $upcoming = createScheduledEvent('approved', 'approved', '2026-07-25');

    $component = Livewire::actingAs($this->osaUser)->test(EventCalendar::class)
        ->set('currentDate', Carbon::parse('2026-07-19'))
        ->set('statusFilter', 'approved');

    $component->call('clearFilters');

    $upcomingEventIds = collect($component->get('upcomingEventsThisMonth'))->pluck('event_id');
    $completedEventIds = collect($component->get('completedEventsThisMonth'))->pluck('event_id');

    expect($component->get('statusFilter'))->toBe('all')
        ->and($upcomingEventIds)->toContain($upcoming->event_id)
        ->and($completedEventIds)->toContain($completed->event_id);
});
