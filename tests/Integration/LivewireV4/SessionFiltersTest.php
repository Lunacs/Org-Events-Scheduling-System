<?php

use App\Livewire\Superadmin\Reports\Index as SuperadminReportsIndex;
use App\Models\Roles;
use App\Models\User;
use Livewire\Attributes\Session;
use Livewire\Livewire;

beforeEach(function () {
    $role = Roles::firstOrCreate(['role_name' => 'superadmin']);
    $this->superadmin = User::factory()->create(['role_id' => $role->role_id]);
});

it('renders the reports page without errors', function () {
    Livewire::actingAs($this->superadmin)
        ->test(SuperadminReportsIndex::class)
        ->assertSuccessful();
});

it('has Session attribute on selectedOffices property', function () {
    $reflection = new ReflectionProperty(SuperadminReportsIndex::class, 'selectedOffices');
    $attributes = $reflection->getAttributes(Session::class);

    expect($attributes)->not->toBeEmpty();
});

it('has Session attribute on selectedEventTypes property', function () {
    $reflection = new ReflectionProperty(SuperadminReportsIndex::class, 'selectedEventTypes');
    $attributes = $reflection->getAttributes(Session::class);

    expect($attributes)->not->toBeEmpty();
});

it('initializes filter arrays as empty', function () {
    Livewire::actingAs($this->superadmin)
        ->test(SuperadminReportsIndex::class)
        ->assertSet('selectedOffices', [])
        ->assertSet('selectedEventTypes', []);
});
