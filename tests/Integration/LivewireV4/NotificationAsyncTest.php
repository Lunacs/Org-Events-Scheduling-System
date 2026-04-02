<?php

use App\Livewire\Gso\Notifications as GsoNotifications;
use App\Livewire\NotificationDropdown;
use App\Livewire\Osa\Notifications as OsaNotifications;
use App\Livewire\StudentOrg\Notifications as StudentOrgNotifications;
use App\Livewire\Superadmin\Notifications as SuperadminNotifications;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

function createTestNotification(User $user): string
{
    $id = Str::uuid()->toString();

    DB::table('notifications')->insert([
        'id' => $id,
        'type' => 'App\\Notifications\\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->user_id,
        'data' => json_encode(['message' => 'Test notification', 'type' => 'info']),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}

it('marks notification as read in StudentOrg notifications', function () {
    $role = Roles::firstOrCreate(['role_name' => 'student-org']);
    $user = User::factory()->create(['role_id' => $role->role_id]);
    $notificationId = createTestNotification($user);

    Livewire::actingAs($user)
        ->test(StudentOrgNotifications::class)
        ->call('markAsRead', $notificationId);

    expect(DB::table('notifications')->where('id', $notificationId)->value('read_at'))->not->toBeNull();
});

it('marks notification as read in OSA notifications', function () {
    $role = Roles::firstOrCreate(['role_name' => 'osa']);
    $user = User::factory()->create(['role_id' => $role->role_id]);
    $notificationId = createTestNotification($user);

    Livewire::actingAs($user)
        ->test(OsaNotifications::class)
        ->call('markAsRead', $notificationId);

    expect(DB::table('notifications')->where('id', $notificationId)->value('read_at'))->not->toBeNull();
});

it('marks notification as read in GSO notifications', function () {
    $role = Roles::firstOrCreate(['role_name' => 'gso']);
    $user = User::factory()->create(['role_id' => $role->role_id]);
    $notificationId = createTestNotification($user);

    Livewire::actingAs($user)
        ->test(GsoNotifications::class)
        ->call('markAsRead', $notificationId);

    expect(DB::table('notifications')->where('id', $notificationId)->value('read_at'))->not->toBeNull();
});

it('marks notification as read in Superadmin notifications', function () {
    $role = Roles::firstOrCreate(['role_name' => 'superadmin']);
    $user = User::factory()->create(['role_id' => $role->role_id]);
    $notificationId = createTestNotification($user);

    Livewire::actingAs($user)
        ->test(SuperadminNotifications::class)
        ->call('markAsRead', $notificationId);

    expect(DB::table('notifications')->where('id', $notificationId)->value('read_at'))->not->toBeNull();
});

it('marks notification as read via NotificationDropdown', function () {
    $role = Roles::firstOrCreate(['role_name' => 'osa']);
    $user = User::factory()->create(['role_id' => $role->role_id]);
    $notificationId = createTestNotification($user);

    Livewire::actingAs($user)
        ->test(NotificationDropdown::class)
        ->call('markAsRead', $notificationId);

    expect(DB::table('notifications')->where('id', $notificationId)->value('read_at'))->not->toBeNull();
});

it('loads notifications via NotificationDropdown', function () {
    $role = Roles::firstOrCreate(['role_name' => 'osa']);
    $user = User::factory()->create(['role_id' => $role->role_id]);
    createTestNotification($user);

    Livewire::actingAs($user)
        ->test(NotificationDropdown::class)
        ->call('loadNotifications')
        ->assertSuccessful();
});
