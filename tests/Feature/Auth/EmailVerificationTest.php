<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create([
            'role_id' => User::getRoleId('student-org'),
        ]);

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create([
            'role_id' => User::getRoleId('student-org'),
        ]);

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->user_id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect();
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create([
            'role_id' => User::getRoleId('student-org'),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->user_id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verified_users_redirected_to_dashboard_from_verification_page(): void
    {
        $user = User::factory()->create([
            'role_id' => User::getRoleId('student-org'),
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertRedirect(route('student-org.dashboard'));
    }

    public function test_osa_user_redirected_to_admin_dashboard_after_verification(): void
    {
        $user = User::factory()->unverified()->create([
            'role_id' => User::getRoleId('osa'),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->user_id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect();
        $this->assertStringContainsString('admin/dashboard', $response->headers->get('Location'));
    }

    public function test_gso_user_redirected_to_gso_dashboard_after_verification(): void
    {
        $user = User::factory()->unverified()->create([
            'role_id' => User::getRoleId('gso'),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->user_id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect();
        $this->assertStringContainsString('gso/dashboard', $response->headers->get('Location'));
    }

    public function test_superadmin_user_redirected_to_superadmin_dashboard_after_verification(): void
    {
        $user = User::factory()->unverified()->create([
            'role_id' => User::getRoleId('superadmin'),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->user_id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect();
        $this->assertStringContainsString('superadmin/dashboard', $response->headers->get('Location'));
    }
}
