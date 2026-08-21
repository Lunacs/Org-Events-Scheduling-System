<?php

use App\Services\Cache\DashboardCacheService;
use App\Services\Cache\CalendarCacheService;
use App\Services\Cache\EventCacheService;
use App\Services\Cache\ReportCacheService;
use App\Services\Cache\ArchiveCacheService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Disable seeding to prevent database operations during test setup
    $this->seed = false;
    
    // Create a unique temp directory for this test run to prevent disk leakage
    $this->tempCacheDir = storage_path('framework/cache/test_data_' . uniqid());
    if (!file_exists($this->tempCacheDir)) {
        mkdir($this->tempCacheDir, 0777, true);
    }
    
    // Force a non-taggable cache store (file driver) using the temp path
    config([
        'cache.default' => 'file',
        'cache.stores.file.path' => $this->tempCacheDir,
    ]);
    Cache::flush();
});

afterEach(function () {
    Cache::flush();
    // Remove the temp directory
    if (file_exists($this->tempCacheDir)) {
        \Illuminate\Support\Facades\File::deleteDirectory($this->tempCacheDir);
    }
});

it('correctly tracks and clears dashboard caches under non-taggable driver without Cache::flush', function () {
    $val1 = DashboardCacheService::getRoleStats('osa', function () {
        return ['stats' => 1];
    });
    expect($val1)->toBe(['stats' => 1]);

    // Check that it retrieves from cache
    $val2 = DashboardCacheService::getRoleStats('osa', function () {
        return ['stats' => 2];
    });
    expect($val2)->toBe(['stats' => 1]);

    // Write unrelated cache directly to test that it is not flushed
    Cache::put('unrelated_key', 'unrelated_val', 600);

    // Invalidate
    DashboardCacheService::clearAllDashboards();

    // Assert unrelated cache still exists
    expect(Cache::get('unrelated_key'))->toBe('unrelated_val');

    // Assert dashboard cache was cleared (should execute new closure)
    $val3 = DashboardCacheService::getRoleStats('osa', function () {
        return ['stats' => 3];
    });
    expect($val3)->toBe(['stats' => 3]);
});

it('correctly tracks and clears calendar caches selectively using suffix matching under non-taggable driver', function () {
    $july = CalendarCacheService::getMonthlyEvents(2026, 7, function () {
        return 'july-1';
    });
    $august = CalendarCacheService::getMonthlyEvents(2026, 8, function () {
        return 'august-1';
    });

    expect($july)->toBe('july-1')
        ->and($august)->toBe('august-1');

    // Write unrelated cache
    Cache::put('unrelated_key', 'unrelated_val', 600);

    // Invalidate July only
    CalendarCacheService::clearMonthlyEvents(2026, 7);

    // Assert unrelated cache still exists
    expect(Cache::get('unrelated_key'))->toBe('unrelated_val');

    // Assert July is cleared, August is NOT
    $julyCached = CalendarCacheService::getMonthlyEvents(2026, 7, function () {
        return 'july-2';
    });
    $augustCached = CalendarCacheService::getMonthlyEvents(2026, 8, function () {
        return 'august-2';
    });

    expect($julyCached)->toBe('july-2')
        ->and($augustCached)->toBe('august-1');

    // Invalidate all calendar
    CalendarCacheService::clearAllCalendar();

    // Assert August is now cleared
    $augustCleared = CalendarCacheService::getMonthlyEvents(2026, 8, function () {
        return 'august-3';
    });
    expect($augustCleared)->toBe('august-3');
});

it('correctly tracks and clears event caches under non-taggable driver without Cache::flush', function () {
    $val1 = EventCacheService::getRequestList('osa', 'pending', 1, function () {
        return ['req' => 1];
    });
    expect($val1)->toBe(['req' => 1]);

    Cache::put('unrelated_key', 'unrelated_val', 600);

    EventCacheService::clearRequestLists();

    expect(Cache::get('unrelated_key'))->toBe('unrelated_val');

    $val2 = EventCacheService::getRequestList('osa', 'pending', 1, function () {
        return ['req' => 2];
    });
    expect($val2)->toBe(['req' => 2]);
});

it('correctly tracks and clears report caches under non-taggable driver without Cache::flush', function () {
    $val1 = ReportCacheService::getReport('summary', '2026-01-01', '2026-12-31', 'all', function () {
        return ['report' => 1];
    });
    expect($val1)->toBe(['report' => 1]);

    Cache::put('unrelated_key', 'unrelated_val', 600);

    ReportCacheService::clearAllReports();

    expect(Cache::get('unrelated_key'))->toBe('unrelated_val');

    $val2 = ReportCacheService::getReport('summary', '2026-01-01', '2026-12-31', 'all', function () {
        return ['report' => 2];
    });
    expect($val2)->toBe(['report' => 2]);
});

it('correctly tracks and clears archive caches under non-taggable driver without Cache::flush', function () {
    $val1 = ArchiveCacheService::getAvailableYears('osa', function () {
        return [2025, 2026];
    });
    expect($val1)->toBe([2025, 2026]);

    Cache::put('unrelated_key', 'unrelated_val', 600);

    ArchiveCacheService::clearAllArchives();

    expect(Cache::get('unrelated_key'))->toBe('unrelated_val');

    $val2 = ArchiveCacheService::getAvailableYears('osa', function () {
        return [2026];
    });
    expect($val2)->toBe([2026]);
});
