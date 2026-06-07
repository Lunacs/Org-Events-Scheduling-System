<?php

use App\Livewire\Components\EventCalendar;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Cache::flush();

    $role = Roles::firstOrCreate(['role_name' => 'osa']);
    $this->osaUser = User::factory()->create(['role_id' => $role->role_id]);
});

it('caches calendar events and unique count under filter-specific keys', function () {
    $component = Livewire::actingAs($this->osaUser)->test(EventCalendar::class);

    $cacheKeyMethod = new ReflectionMethod(EventCalendar::class, 'calendarFiltersCacheKey');
    $cacheKeyMethod->setAccessible(true);
    $eventsKey = $cacheKeyMethod->invoke($component->instance(), 'events');
    $countKey = $cacheKeyMethod->invoke($component->instance(), 'unique_count');

    expect(Cache::has($eventsKey))->toBeFalse()
        ->and(Cache::has($countKey))->toBeFalse();

    $component->get('eventsForCalendar');
    $component->get('uniqueEventsCount');

    expect(Cache::has($eventsKey))->toBeTrue()
        ->and(Cache::has($countKey))->toBeTrue();
});
