<?php

use App\Livewire\StudentOrg\Profile;
use App\Models\User;
use Livewire\Livewire;

/*
 * Guards the institutional refresh of public + auth pages and the profile
 * unification: pages render, and the refreshed surfaces are token-driven and
 * free of the banned patterns (hardcoded gray, scale-hover, oklch hacks, gradient banner).
 */

function fileContents(string $relative): string
{
    return file_get_contents(resource_path($relative));
}

it('renders the public and auth pages for guests', function () {
    $this->get(route('login'))->assertOk();
    $this->get(route('password.request'))->assertOk();
    $this->get(route('about-us'))->assertOk()->assertSee('developers');
    $this->get(route('faq'))->assertOk();
    $this->get(route('data-privacy'))->assertOk()->assertSee('Data Privacy Notice');
});

it('keeps the auth shell + login token-driven and free of tells', function () {
    $guest = fileContents('views/components/layouts/guest.blade.php');
    $login = fileContents('views/livewire/pages/auth/login.blade.php');

    foreach ([$guest, $login] as $markup) {
        expect($markup)->not->toContain('text-gray-')
            ->and($markup)->not->toContain('hover:scale-105')
            ->and($markup)->not->toContain('oklch(');
    }

    expect($guest)->toContain('bg-base-200')
        ->and($login)->toContain('text-base-content');
});

it('replaces the gradient profile banner and Mary inputs with token components', function () {
    foreach (['gso', 'osa', 'student-org', 'superadmin'] as $role) {
        $markup = fileContents("views/livewire/{$role}/profile.blade.php");

        expect($markup)->toContain('<x-profile.identity-header')
            ->and($markup)->not->toContain('bg-gradient-to-r from-primary to-secondary')
            ->and($markup)->not->toContain('text-gray-400 hover:text-gray-600')
            ->and($markup)->not->toContain('x-mary-input');
    }

    // Identity header is role-tinted (distinct accent per role), not plain white.
    $header = fileContents('views/components/profile/identity-header.blade.php');
    expect($header)->toContain("'osa'")
        ->and($header)->toContain('bg-secondary/5')
        ->and($header)->toContain('bg-accent/5')
        ->and($header)->toContain('bg-info/5');

    // Token inputs use a clean focus ring, not Mary's default outline.
    $field = fileContents('views/components/profile/text-field.blade.php');
    expect($field)->toContain('focus:outline-none')
        ->and($field)->toContain('focus:ring-2');
});

it('gives the token fields a stable id and inline validation errors', function () {
    foreach (['text-field', 'password-field'] as $component) {
        $markup = fileContents("views/components/profile/{$component}.blade.php");

        // Stable id derived from the bound model (fixes focus loss during live typing).
        expect($markup)->toContain("wire('model')->value()")
            ->and($markup)->toContain('field-')
            // Inline validation errors surfaced from the shared $errors bag.
            ->and($markup)->toContain('$errors->has($model)')
            ->and($markup)->toContain('$errors->first($model)')
            ->and($markup)->toContain('aria-invalid');
    }
});

it('shows a live password strength meter on the profile new password field', function () {
    $passwordField = fileContents('views/components/profile/password-field.blade.php');
    expect($passwordField)->toContain('Password strength')
        ->and($passwordField)->toContain('checkStrength()');

    foreach (['gso', 'osa', 'student-org', 'superadmin'] as $role) {
        $markup = fileContents("views/livewire/{$role}/profile.blade.php");

        expect($markup)->toContain('wire:model.live.debounce.300ms="new_password"')
            ->and($markup)->toContain('strength');
    }
});

it('renders each role profile component without errors', function () {
    $cases = [
        Profile::class => 'student@plv.edu.ph',
        App\Livewire\Osa\Profile::class => 'osa@plv.edu.ph',
        App\Livewire\Gso\Profile::class => 'gso@plv.edu.ph',
    ];

    foreach ($cases as $component => $email) {
        $user = User::where('email', $email)->first();
        expect($user)->not->toBeNull();

        Livewire::actingAs($user)
            ->test($component)
            ->assertOk()
            ->assertHasNoErrors()
            ->assertSee($user->name);
    }
});
