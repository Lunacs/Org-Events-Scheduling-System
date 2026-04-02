<?php

use App\Livewire\Components\EventCalendar;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $role = Roles::firstOrCreate(['role_name' => 'osa']);
    $this->osaUser = User::factory()->create(['role_id' => $role->role_id]);
});

it('dispatches calendar-prev event from previousPeriod', function () {
    Livewire::actingAs($this->osaUser)
        ->test(EventCalendar::class)
        ->call('previousPeriod')
        ->assertDispatched('calendar-prev');
});

it('dispatches calendar-next event from nextPeriod', function () {
    Livewire::actingAs($this->osaUser)
        ->test(EventCalendar::class)
        ->call('nextPeriod')
        ->assertDispatched('calendar-next');
});

it('dispatches calendar-today event from today', function () {
    Livewire::actingAs($this->osaUser)
        ->test(EventCalendar::class)
        ->call('today')
        ->assertDispatched('calendar-today');
});
