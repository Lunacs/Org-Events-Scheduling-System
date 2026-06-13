<?php

use App\Models\User;
use App\Models\Ticket;
use App\Models\Student_Organization;
use App\Services\Cache\DashboardCacheService;
use App\Services\Cache\LayoutCacheService;
use Illuminate\Support\Facades\Cache;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    Cache::flush();
});

it('caches dashboard widgets and prevents database queries on subsequent calls', function () {
    $user = User::factory()->create(['role_id' => User::getRoleId('osa')]);
    
    // First call should hit the database and store in cache
    $stats1 = DashboardCacheService::getDashboardWidget('osa', 'stats', function () {
        return ['count' => 10];
    }, $user->user_id);
    
    expect($stats1)->toBe(['count' => 10]);
    
    // Modify the closure to return different data - if cache works, we should still get 10
    $stats2 = DashboardCacheService::getDashboardWidget('osa', 'stats', function () {
        return ['count' => 99]; // Should not be executed
    }, $user->user_id);
    
    expect($stats2)->toBe(['count' => 10]);
});

it('invalidates caches when instructed', function () {
    $user = User::factory()->create(['role_id' => User::getRoleId('osa')]);
    
    DashboardCacheService::getDashboardWidget('osa', 'stats', function () {
        return ['count' => 10];
    }, $user->user_id);
    
    DashboardCacheService::clearAllDashboards();
    
    // Now it should hit the new closure
    $stats2 = DashboardCacheService::getDashboardWidget('osa', 'stats', function () {
        return ['count' => 99];
    }, $user->user_id);
    
    expect($stats2)->toBe(['count' => 99]);
});

it('caches notification counts per user using LayoutCacheService', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $count1 = LayoutCacheService::getUnreadNotificationCount($user1->user_id, function () { return 5; });
    $count2 = LayoutCacheService::getUnreadNotificationCount($user2->user_id, function () { return 3; });
    
    expect($count1)->toBe(5)
        ->and($count2)->toBe(3);
        
    // Verify it hits cache
    $count1_cached = LayoutCacheService::getUnreadNotificationCount($user1->user_id, function () { return 99; });
    expect($count1_cached)->toBe(5);
});
