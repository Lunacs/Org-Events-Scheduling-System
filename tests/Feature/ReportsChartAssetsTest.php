<?php

use App\Livewire\Osa\Reports as OsaReports;
use App\Livewire\Superadmin\Reports\Index as SuperadminReports;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

/*
 * The OSA and SuperAdmin report pages are #[Lazy]: their real view renders in a
 * follow-up Livewire request, where Blade @push stacks never reach the page. The
 * Chart.js bundle must be registered with Livewire's @assets so it loads on lazy
 * renders too (otherwise the browser logs "Chart.js is not loaded" and no chart draws).
 */
beforeEach(function () {
    Cache::flush();
});

/**
 * Keys of the @assets blocks the component registered while rendering (Livewire keeps
 * them in the snapshot memo so it loads each asset once, on any request).
 */
function registeredAssetKeys($testable): array
{
    return \Livewire\invade($testable)->lastState->getSnapshot()['memo']['assets'] ?? [];
}

it('registers the Chart.js bundle as a Livewire asset on the lazily rendered OSA reports view', function () {
    $user = User::factory()->create(['role_id' => User::getRoleId('osa')]);

    $component = Livewire::withoutLazyLoading()
        ->actingAs($user)
        ->test(OsaReports::class)
        ->assertOk();

    expect(registeredAssetKeys($component))->not->toBeEmpty();
});

it('registers the Chart.js bundle as a Livewire asset on the lazily rendered SuperAdmin reports view', function () {
    $user = User::factory()->create(['role_id' => User::getRoleId('superadmin')]);

    $component = Livewire::withoutLazyLoading()
        ->actingAs($user)
        ->test(SuperadminReports::class)
        ->assertOk();

    expect(registeredAssetKeys($component))->not->toBeEmpty();
});

it('loads Chart.js through @assets, not a Blade stack, in the lazy report views', function (string $view) {
    $source = file_get_contents(resource_path("views/livewire/{$view}.blade.php"));

    expect($source)->toMatch("/@assets\\s+@vite\\('resources\\/js\\/charts\\.js'\\)\\s+@endassets/")
        ->and($source)->not->toContain("@push('head')");
})->with(['osa/reports', 'superadmin/reports/index']);
