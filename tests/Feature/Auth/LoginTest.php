<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_osa_users_can_authenticate_through_unified_portal(): void
    {
        $user = User::factory()->create([
            'role_id' => User::getRoleId('osa'),
            'email' => 'osa-test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'osa-test@example.com')
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_student_org_users_can_authenticate_through_unified_portal(): void
    {
        $user = User::factory()->create([
            'role_id' => User::getRoleId('student-org'),
            'email' => 'student-test@plv.edu.ph',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'student-test@plv.edu.ph')
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(route('student-org.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_gso_users_can_authenticate_through_unified_portal(): void
    {
        $user = User::factory()->create([
            'role_id' => User::getRoleId('gso'),
            'email' => 'gso-test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'gso-test@example.com')
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(route('gso.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_superadmin_users_can_authenticate_through_unified_portal(): void
    {
        $user = User::factory()->create([
            'role_id' => User::getRoleId('superadmin'),
            'email' => 'superadmin-test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'superadmin-test@example.com')
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(route('superadmin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'role_id' => User::getRoleId('osa'),
            'email' => 'osa-test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'osa-test@example.com')
            ->set('form.password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['form.password']);

        $this->assertGuest();
    }

    public function test_login_throttles_after_too_many_attempts(): void
    {
        $user = User::factory()->create([
            'role_id' => User::getRoleId('osa'),
            'email' => 'osa-test-throttle@example.com',
            'password' => bcrypt('password'),
        ]);

        // Attempt login 5 times with wrong password
        for ($i = 0; $i < 5; $i++) {
            Livewire::test('pages.auth.login')
                ->set('form.email', 'osa-test-throttle@example.com')
                ->set('form.password', 'wrong-password')
                ->call('login')
                ->assertHasErrors(['form.password']);
        }

        // The 6th attempt should hit rate limiter on email
        Livewire::test('pages.auth.login')
            ->set('form.email', 'osa-test-throttle@example.com')
            ->set('form.password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['form.email']);

        $this->assertGuest();
    }

    public function test_unverified_student_org_redirected_to_verification(): void
    {
        $user = User::factory()->create([
            'role_id' => User::getRoleId('student-org'),
            'email' => 'student-unverified-test@plv.edu.ph',
            'password' => bcrypt('password'),
            'email_verified_at' => null,
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'student-unverified-test@plv.edu.ph')
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(route('verification.notice'));

        $this->assertAuthenticated();
    }
}
