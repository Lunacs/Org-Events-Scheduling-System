<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'role' => User::getRoleId('osa'),
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('admin.login'));
    }

    public function test_guest_redirected_to_login_when_accessing_protected_routes(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => User::getRoleId('osa'),
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_default_login_route_redirects_to_admin_login(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_remember_me_functionality_works(): void
    {
        $user = User::factory()->create([
            'role' => User::getRoleId('osa'),
            'email' => 'osa@example.com',
            'password' => bcrypt('password'),
        ]);

        Volt::test('pages.auth.osa-login')
            ->set('form.email', 'osa@example.com')
            ->set('form.password', 'password')
            ->set('form.remember', true)
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
        $this->assertNotNull($user->fresh()->remember_token);
    }
}
