<?php

use App\Models\User;
use App\Models\Student_Organization;
use App\Services\Cache\DashboardCacheService;
use App\Services\Cache\ReportCacheService;
use Illuminate\Support\Facades\Cache;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    Cache::flush();
});

it('isolates student organization dashboard widgets by user id', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    // Simulate user 1 logging in and caching tickets
    actingAs($user1);
    $tickets1 = DashboardCacheService::getDashboardWidget('student_org', 'tickets', function () {
        return ['ticket_id' => 1];
    }, $user1->user_id);
    
    // Simulate user 2 logging in and caching tickets
    actingAs($user2);
    $tickets2 = DashboardCacheService::getDashboardWidget('student_org', 'tickets', function () {
        return ['ticket_id' => 2];
    }, $user2->user_id);
    
    // Verify user 2 gets their own data, not user 1's
    expect($tickets1)->toBe(['ticket_id' => 1])
        ->and($tickets2)->toBe(['ticket_id' => 2]);
        
    // Switch back to user 1 and verify cache still works independently
    actingAs($user1);
    $tickets1Cached = DashboardCacheService::getDashboardWidget('student_org', 'tickets', function () {
        return ['ticket_id' => 99]; // Should not run
    }, $user1->user_id);
    
    expect($tickets1Cached)->toBe(['ticket_id' => 1]);
});

it('isolates report data by report type and filters', function () {
    $report1 = ReportCacheService::getReport('approved_events', '2026-01-01', '2026-01-31', 'org_1', function() {
        return ['events' => 5];
    });
    
    $report2 = ReportCacheService::getReport('approved_events', '2026-01-01', '2026-01-31', 'org_2', function() {
        return ['events' => 10];
    });
    
    // Different filters should yield different cached data
    expect($report1)->toBe(['events' => 5])
        ->and($report2)->toBe(['events' => 10]);
});
