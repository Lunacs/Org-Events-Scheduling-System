<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_student_org_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/student-org/login');

        $response->assertStatus(200);
    }

    public function test_gso_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/gso/login');

        $response->assertStatus(200);
    }

    public function test_superadmin_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/superadmin/login');

        $response->assertStatus(200);
    }

    public function test_osa_users_can_authenticate_through_admin_portal(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_OSA,
            'email' => 'osa@example.com',
            'password' => bcrypt('password'),
        ]);

        Volt::test('pages.auth.osa-login')
            ->set('form.email', 'osa@example.com')
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_student_org_users_can_authenticate_through_student_org_portal(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_STUDENT_ORG,
            'email' => 'student@plv.edu.ph',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        Volt::test('pages.auth.student-org-login')
            ->set('form.email', 'student@plv.edu.ph')
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(route('student-org.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_gso_users_can_authenticate_through_gso_portal(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_GSO,
            'email' => 'gso@example.com',
            'password' => bcrypt('password'),
        ]);

        Volt::test('pages.auth.gso-login')
            ->set('form.email', 'gso@example.com')
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(route('gso.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_superadmin_users_can_authenticate_through_superadmin_portal(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);

        Volt::test('pages.auth.superadmin-login')
            ->set('form.email', 'superadmin@example.com')
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(route('superadmin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_student_org_login_requires_plv_email(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_STUDENT_ORG,
            'email' => 'student@gmail.com',
            'password' => bcrypt('password'),
        ]);

        Volt::test('pages.auth.student-org-login')
            ->set('form.email', 'student@gmail.com')
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasErrors(['form.email']);

        $this->assertGuest();
    }

    public function test_osa_user_cannot_login_through_gso_portal(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_OSA,
            'email' => 'osa@example.com',
            'password' => bcrypt('password'),
        ]);

        Volt::test('pages.auth.gso-login')
            ->set('form.email', 'osa@example.com')
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasErrors(['form.email']);

        $this->assertGuest();
    }

    public function test_student_org_user_cannot_login_through_admin_portal(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_STUDENT_ORG,
            'email' => 'student@plv.edu.ph',
            'password' => bcrypt('password'),
        ]);

        Volt::test('pages.auth.osa-login')
            ->set('form.email', 'student@plv.edu.ph')
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasErrors(['form.email']);

        $this->assertGuest();
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_OSA,
            'email' => 'osa@example.com',
            'password' => bcrypt('password'),
        ]);

        Volt::test('pages.auth.osa-login')
            ->set('form.email', 'osa@example.com')
            ->set('form.password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['form.email']);

        $this->assertGuest();
    }

    public function test_login_throttles_after_too_many_attempts(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_OSA,
            'email' => 'osa@example.com',
            'password' => bcrypt('password'),
        ]);

        // Attempt login 6 times with wrong password
        for ($i = 0; $i < 6; $i++) {
            Volt::test('pages.auth.osa-login')
                ->set('form.email', 'osa@example.com')
                ->set('form.password', 'wrong-password')
                ->call('login')
                ->assertHasErrors(['form.email']);
        }

        $this->assertGuest();
    }

    public function test_unverified_student_org_redirected_to_verification(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_STUDENT_ORG,
            'email' => 'student@plv.edu.ph',
            'password' => bcrypt('password'),
            'email_verified_at' => null,
        ]);

        Volt::test('pages.auth.student-org-login')
            ->set('form.email', 'student@plv.edu.ph')
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(route('verification.notice'));

        $this->assertAuthenticated();
    }
}
