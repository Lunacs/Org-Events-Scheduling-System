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

it('wires IBM Plex Sans for all UI text and Plex Mono for identifiers', function () {
    $css = appCss();

    // DESIGN.md "The One Family Rule": font-heading is an alias of the same family.
    expect($css)->toContain('--font-sans:')
        ->and($css)->toContain('"IBM Plex Sans"')
        ->and($css)->toContain('--font-heading: "IBM Plex Sans"')
        ->and($css)->toContain('--font-mono: "IBM Plex Mono"')
        ->and($css)->not->toContain('Poppins');
});

it('exposes a visible keyboard-focus ring instead of blanket focus removal', function () {
    $css = appCss();

    // The visible focus-visible ring must exist, in the contrast-checked focus color.
    expect($css)->toContain(':focus-visible {')
        ->and($css)->toContain('outline: 2px solid var(--color-focus);')
        ->and($css)->toContain('--color-focus:');

    // The old blanket removals that killed keyboard focus must be gone.
    expect($css)->not->toContain('[class*="focus:ring"]:focus')
        ->and($css)->not->toContain('ring: none !important;')
        ->and($css)->not->toContain('outline: none !important;');
});

it('routes every portal through the shared shell, which never opens the mobile drawer on load', function () {
    $layouts = [
        'app.blade.php' => 'osa',
        'student-org-layout.blade.php' => 'student-org',
        'gso-layout.blade.php' => 'gso',
        'superadmin.blade.php' => 'superadmin',
        'viewer-layout.blade.php' => 'viewer',
    ];

    foreach ($layouts as $file => $portal) {
        $layout = file_get_contents(resource_path("views/components/layouts/{$file}"));

        expect($layout)->toContain("<x-layouts.portal portal=\"{$portal}\"");
    }

    $shell = file_get_contents(resource_path('views/components/layouts/portal.blade.php'));

    // Desktop restores the remembered expanded/collapsed state; mobile always starts closed.
    expect($shell)->toContain('toggle.checked = desktop.matches ? saved() : false;')
        ->and($shell)->toContain('<main id="main"')
        ->and($shell)->toContain('Skip to main content')
        ->and($shell)->not->toContain('$persist(true)');
});

it('never forces white text onto brand or status fills', function () {
    $css = appCss();

    // Each fill owns its *-content color (white on navy, dark ink on dark-mode cyan).
    expect($css)->not->toContain('[class*="bg-primary"]')
        ->and($css)->not->toContain('.bg-primary:not([class*="/"])')
        ->and($css)->not->toContain('color: white !important')
        ->and($css)->toContain('--color-primary-content:')
        ->and($css)->toContain('--color-warning-content:');
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
