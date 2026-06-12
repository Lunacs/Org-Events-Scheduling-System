<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\CustomResetPassword as ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_id' => User::getRoleId('osa'),
        ]);

        Livewire::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_id' => User::getRoleId('osa'),
        ]);

        Livewire::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_id' => User::getRoleId('osa'),
        ]);

        Livewire::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            Livewire::test('pages.auth.reset-password', ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'Password123!')
                ->set('password_confirmation', 'Password123!')
                ->call('resetPassword')
                ->assertHasNoErrors();

            return true;
        });
    }

    public function test_osa_user_redirected_to_admin_login_after_password_reset(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_id' => User::getRoleId('osa'),
        ]);

        Livewire::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            Livewire::test('pages.auth.reset-password', ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'Password123!')
                ->set('password_confirmation', 'Password123!')
                ->call('resetPassword')
                ->assertRedirect(route('admin.login'));

            return true;
        });
    }

    public function test_student_org_user_redirected_to_student_org_login_after_password_reset(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_id' => User::getRoleId('student-org'),
        ]);

        Livewire::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            Livewire::test('pages.auth.reset-password', ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'Password123!')
                ->set('password_confirmation', 'Password123!')
                ->call('resetPassword')
                ->assertRedirect(route('student-org.login'));

            return true;
        });
    }

    public function test_gso_user_redirected_to_gso_login_after_password_reset(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_id' => User::getRoleId('gso'),
        ]);

        Livewire::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            Livewire::test('pages.auth.reset-password', ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'Password123!')
                ->set('password_confirmation', 'Password123!')
                ->call('resetPassword')
                ->assertRedirect(route('gso.login'));

            return true;
        });
    }

    public function test_superadmin_user_redirected_to_superadmin_login_after_password_reset(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_id' => User::getRoleId('superadmin'),
        ]);

        Livewire::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            Livewire::test('pages.auth.reset-password', ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'Password123!')
                ->set('password_confirmation', 'Password123!')
                ->call('resetPassword')
                ->assertRedirect(route('superadmin.login'));

            return true;
        });
    }
}
