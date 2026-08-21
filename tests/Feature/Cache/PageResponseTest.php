<?php

use App\Livewire\Osa\Dashboard;
use App\Livewire\Osa\TicketReview\Show;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Cache\DashboardCacheService;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
});

it('loads osa dashboard with cached stats', function () {
    $user = User::factory()->create(['role_id' => User::getRoleId('osa')]);

    // Test that the Livewire component can render successfully and uses the cache
    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->call('warmCache') // This triggers stats, recentTickets, pendingApprovals
        ->assertHasNoErrors();

    // Verify cache was populated
    $hasCache = Cache::tags(['dashboard', 'role:osa'])->has('dashboard:osa:widget:stats');
    expect($hasCache)->toBeTrue();
});

it('clears dashboard cache after ticket approval', function () {
    $user = User::factory()->create(['role_id' => User::getRoleId('osa')]);
    $ticket = Ticket::factory()->create(['status' => 'pending_osa_approval']);

    // Prime the cache
    DashboardCacheService::getDashboardWidget('osa', 'stats', function () {
        return ['count' => 10];
    });

    expect(Cache::tags(['dashboard', 'role:osa'])->has('dashboard:osa:widget:stats'))->toBeTrue();

    // Simulate approval action
    Livewire::actingAs($user)
        ->test(Show::class, ['ticketNumber' => $ticket->ticket_number])
        ->set('approvalRemarks', 'Looks good')
        ->call('approveTicket');

    // Verify cache was cleared
    expect(Cache::tags(['dashboard', 'role:osa'])->has('dashboard:osa:widget:stats'))->toBeFalse();
});
