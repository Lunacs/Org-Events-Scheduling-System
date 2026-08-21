<?php

use App\Livewire\Components\EventCalendar;
use App\Models\Event;
use App\Models\Event_Schedule;
use App\Models\Roles;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();

    $role = Roles::firstOrCreate(['role_name' => 'osa']);
    $this->osaUser = User::factory()->create(['role_id' => $role->role_id]);
});

it('invalidates the cached calendar events when a ticket is approved (database cache driver)', function () {
    expect(config('cache.default'))->toBe('database');

    // Ticket starts out not approved, so it is excluded from the calendar query.
    $ticket = Ticket::factory()->create(['status' => 'received']);
    $event = Event::factory()->create(['ticket_id' => $ticket->ticket_id]);
    Event_Schedule::create([
        'event_id' => $event->event_id,
        'status' => 'approved',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addDay()->format('Y-m-d'),
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'venue' => 'Test Venue',
    ]);

    $component = Livewire::actingAs($this->osaUser)->test(EventCalendar::class);

    // First read populates the cache without the event (ticket not approved yet).
    $before = collect($component->get('eventsForCalendar'))->pluck('id');
    expect($before)->not->toContain($event->event_id);

    // Approve the ticket — TicketObserver::updated() should bust the stale cache.
    $ticket->update(['status' => 'approved']);

    // A fresh component instance simulates the next page load / re-render.
    $component2 = Livewire::actingAs($this->osaUser)->test(EventCalendar::class);
    $after = collect($component2->get('eventsForCalendar'))->pluck('id');

    expect($after)->toContain($event->event_id);
});
