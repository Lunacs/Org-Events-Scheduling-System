<?php

use App\Livewire\Osa\Dashboard as OsaDashboard;
use App\Livewire\StudentOrg\Dashboard;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

/*
 * Task 1 guard: the global design-system wiring (fonts, focus rings, dark mode
 * mechanism) must stay consistent so every refreshed dashboard renders correctly.
 */

beforeEach(function () {
    Cache::flush();
});

function appCss(): string
{
    return file_get_contents(resource_path('css/app.css'));
}

it('wires Inter as the body sans font while keeping Poppins for headings', function () {
    $css = appCss();

    expect($css)->toContain('--font-sans:')
        ->and($css)->toContain('"Inter"')
        ->and($css)->toContain('--font-heading: "Poppins", sans-serif;');
});

it('exposes a visible keyboard-focus ring instead of blanket focus removal', function () {
    $css = appCss();

    // The visible focus-visible ring must exist.
    expect($css)->toContain(':focus-visible')
        ->and($css)->toContain('outline: 2px solid var(--color-primary);');

    // The old blanket removals that killed keyboard focus must be gone.
    expect($css)->not->toContain('[class*="focus:ring"]:focus')
        ->and($css)->not->toContain('ring: none !important;');
});

it('defines the dashboard type-scale tokens', function () {
    $css = appCss();

    expect($css)->toContain('--text-dash-title:')
        ->and($css)->toContain('--text-dash-stat:')
        ->and($css)->toContain('--text-dash-meta:');
});

it('keeps every sidebar expanded state aligned with its drawer checkbox', function () {
    $layouts = [
        'views/components/layouts/app.blade.php' => 'osa-sidebar-expanded',
        'views/components/layouts/student-org-layout.blade.php' => 'student-sidebar-expanded',
        'views/components/layouts/gso-layout.blade.php' => 'gso-sidebar-expanded',
        'views/components/layouts/superadmin.blade.php' => 'superadmin-sidebar-expanded',
    ];

    foreach ($layouts as $path => $storageKey) {
        $layout = file_get_contents(resource_path($path));
        $persistExpression = "\$persist(true).as('{$storageKey}')";

        expect($layout)->toContain($persistExpression)
            ->and($layout)->toContain(':checked="sidebarExpanded"')
            ->and($layout)->toContain('@change="sidebarExpanded = $event.target.checked"')
            ->and($layout)->toContain("localStorage.getItem('{$storageKey}') === 'true'")
            ->and($layout)->not->toContain(':checked="!sidebarExpanded"')
            ->and($layout)->not->toContain('@change="sidebarExpanded = !$event.target.checked"');
    }
});

it('does not force white text onto tinted (opacity) colored backgrounds', function () {
    $css = appCss();

    // The broad wildcard used to force white on bg-primary/10 icon chips; it must be gone.
    expect($css)->not->toContain('[class*="bg-primary"]')
        ->and($css)->not->toContain('[class*="bg-secondary"]')
        ->and($css)->not->toContain('[class*="bg-accent"]');

    // Solid backgrounds are still handled, but opacity tints are excluded.
    expect($css)->toContain('.bg-primary:not([class*="/"])');
});

it('renders the OSA dashboard component without errors', function () {
    $user = User::factory()->create(['role_id' => User::getRoleId('osa')]);

    Livewire::actingAs($user)
        ->test(OsaDashboard::class)
        ->assertOk()
        ->assertHasNoErrors();
});

it('renders the student-org dashboard component without errors', function () {
    $user = User::where('email', 'student@plv.edu.ph')->first();
    expect($user)->not->toBeNull();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSee('Welcome back');
});

it('renders the OSA deferred child widgets without errors', function () {
    $user = User::factory()->create(['role_id' => User::getRoleId('osa')]);

    Livewire::actingAs($user)->test('osa.dashboard.recent-activity')->assertOk();
    Livewire::actingAs($user)->test('osa.dashboard.sidebar')->assertOk();
});

it('renders the GSO dashboard component without errors', function () {
    $user = User::where('email', 'gso@plv.edu.ph')->first();
    expect($user)->not->toBeNull();

    Livewire::actingAs($user)
        ->test(App\Livewire\Gso\Dashboard::class)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSee('GSO Dashboard');
});

it('renders the superadmin dashboard and its deferred widgets without errors', function () {
    $user = User::factory()->create(['role_id' => User::getRoleId('superadmin')]);

    // The dashboard itself is #[Lazy]; assert it boots and its placeholder renders.
    Livewire::actingAs($user)
        ->test(App\Livewire\Superadmin\Dashboard::class)
        ->assertOk()
        ->assertHasNoErrors();

    // The deferred child widgets render their real content.
    Livewire::actingAs($user)->test('superadmin.dashboard.pending-approvals')->assertOk()->assertSee('Pending Approvals');
    Livewire::actingAs($user)->test('superadmin.dashboard.recent-activity')->assertOk()->assertSee('Recent Activity');
});
