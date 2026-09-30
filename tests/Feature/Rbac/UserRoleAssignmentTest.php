<?php

use App\Livewire\Superadmin\Users\Index;
use App\Models\Roles;
use App\Models\Transaction_Logs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;

// Task 11.1: role selector data and state binding
// **Validates: Requirements 4.1, 9.1**
it('loads every role for row selector options and keeps selected role IDs in transient state', function (): void {
    $superadminRole = Roles::query()->firstOrCreate(['role_name' => 'superadmin']);
    $coordinatorRole = Roles::query()->create(['role_name' => 'event-coordinator']);
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create(['role_id' => $coordinatorRole->role_id]);
    $roleIds = Roles::query()->pluck('role_id')->all();

    $this->actingAs($administrator);

    $component = Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->assertSet("selectedRoleIds.{$managedUser->user_id}", $coordinatorRole->role_id)
        ->assertSeeHtml("wire:model.live=\"selectedRoleIds.{$managedUser->user_id}\"")
        ->assertSeeHtml("wire:click=\"saveUserRole({$managedUser->user_id})\"");

    expect($component->instance()->availableRoles->pluck('role_id')->all())
        ->toEqualCanonicalizing($roleIds);

    $component
        ->set("selectedRoleIds.{$managedUser->user_id}", $superadminRole->role_id)
        ->assertSet("selectedRoleIds.{$managedUser->user_id}", $superadminRole->role_id);

    expect($managedUser->fresh()->role_id)->toBe($coordinatorRole->role_id);
});

// Feature: dynamic-rbac, Property 10: Role Selector Lists All Active Roles
// **Validates: Requirements 4.1**
it('lists every persisted role name in the selector across varied role cardinalities', function (): void {
    $superadminRole = Roles::query()->firstOrCreate(['role_name' => 'superadmin']);
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $additionalRoleCount = random_int(0, 25);

    for ($roleNumber = 1; $roleNumber <= $additionalRoleCount; $roleNumber++) {
        Roles::query()->create([
            'role_name' => "rbac-property-10-role-{$roleNumber}",
        ]);
    }

    $persistedRoles = Roles::query()
        ->orderBy('role_name')
        ->get(['role_id', 'role_name']);

    $this->actingAs($administrator);

    $selectorRoles = Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->instance()
        ->availableRoles;
    $persistedRoleNames = $persistedRoles->pluck('role_name')->all();

    expect($selectorRoles)->toHaveCount($persistedRoles->count())
        ->and($selectorRoles->pluck('role_name')->all())->toBe($persistedRoleNames);

    foreach ($selectorRoles as $selectorRole) {
        expect($selectorRole->role_name)->toBeIn($persistedRoleNames);
    }
})->repeat(100)->group('rbac', 'property');

// Feature: dynamic-rbac, Property 11: User Role Assignment Persists Correctly
// **Validates: Requirements 4.2**
it('persists each managed users selected valid role through the non-self save flow', function (): void {
    $superadminRole = Roles::query()->firstOrCreate(['role_name' => 'superadmin']);
    $originalRole = Roles::query()->create([
        'role_name' => 'rbac-property-11-original-'.Str::uuid(),
    ]);
    $targetRole = Roles::query()->create([
        'role_name' => 'rbac-property-11-target-'.Str::uuid(),
    ]);
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create(['role_id' => $originalRole->role_id]);

    $this->actingAs($administrator);

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->set("selectedRoleIds.{$managedUser->user_id}", $targetRole->role_id)
        ->call('saveUserRole', $managedUser->user_id);

    expect($managedUser->fresh()->role_id)->toBe($targetRole->role_id);
})->repeat(100)->group('rbac', 'property');

// Feature: dynamic-rbac, Property 12: Invalid Role ID Rejected During User Role Assignment
// **Validates: Requirements 4.5**
it('rejects varying nonexistent role IDs without changing the managed users role', function (): void {
    $superadminRole = Roles::query()->firstOrCreate(['role_name' => 'superadmin']);
    $originalRole = Roles::query()->create([
        'role_name' => 'rbac-property-12-original-'.Str::uuid(),
    ]);
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create(['role_id' => $originalRole->role_id]);
    $invalidRoleId = (int) Roles::query()->max('role_id') + random_int(1, 1_000_000);

    expect(Roles::query()->where('role_id', $invalidRoleId)->exists())->toBeFalse();

    $this->actingAs($administrator);

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->set("selectedRoleIds.{$managedUser->user_id}", $invalidRoleId)
        ->call('saveUserRole', $managedUser->user_id)
        ->assertHasErrors(["selectedRoleIds.{$managedUser->user_id}" => ['exists']]);

    expect($managedUser->fresh()->role_id)->toBe($originalRole->role_id);
})->repeat(100)->group('rbac', 'property');

// Task 11.2: role-change action validation
// **Validates: Requirements 4.5**
it('rejects a selected role that no longer exists without changing the assigned user', function (): void {
    $superadminRole = Roles::query()->firstOrCreate(['role_name' => 'superadmin']);
    $originalRole = Roles::query()->create(['role_name' => 'event-coordinator']);
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create(['role_id' => $originalRole->role_id]);

    $this->actingAs($administrator);

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->set("selectedRoleIds.{$managedUser->user_id}", 999999)
        ->call('saveUserRole', $managedUser->user_id)
        ->assertHasErrors(["selectedRoleIds.{$managedUser->user_id}" => ['exists']]);

    expect($managedUser->fresh()->role_id)->toBe($originalRole->role_id)
        ->and(Transaction_Logs::query()->where('action', 'RBAC_USER_ROLE_CHANGED')->count())->toBe(0);
});

// Task 11.2: authenticated-self confirmation guard
// **Validates: Requirements 4.3**
it('stages an authenticated users role change for confirmation without persisting it', function (): void {
    $superadminRole = Roles::query()->firstOrCreate(['role_name' => 'superadmin']);
    $replacementRole = Roles::query()->create(['role_name' => 'event-coordinator']);
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);

    $this->actingAs($administrator);

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->set("selectedRoleIds.{$administrator->user_id}", $replacementRole->role_id)
        ->call('saveUserRole', $administrator->user_id)
        ->assertSet('showSelfRoleChangeConfirmation', true)
        ->assertSet('pendingSelfRoleChangeUserId', $administrator->user_id)
        ->assertSet('pendingSelfRoleChangeRoleId', $replacementRole->role_id)
        ->assertSet('pendingSelfRoleChangeOriginalRoleId', $superadminRole->role_id);

    expect($administrator->fresh()->role_id)->toBe($superadminRole->role_id)
        ->and(Transaction_Logs::query()->where('action', 'RBAC_USER_ROLE_CHANGED')->count())->toBe(0);
});

// Task 11.3: self-role-change modal rendering and interaction
// **Validates: Requirements 4.3, 9.2, 9.4**
it('renders a self-role-change confirmation modal that can be dismissed or confirmed', function (): void {
    $superadminRole = Roles::query()->firstOrCreate(['role_name' => 'superadmin']);
    $replacementRole = Roles::query()->create(['role_name' => 'event-coordinator']);
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    User::factory()->create(['role_id' => $superadminRole->role_id]);

    $this->actingAs($administrator);

    $component = Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->set("selectedRoleIds.{$administrator->user_id}", $replacementRole->role_id)
        ->call('saveUserRole', $administrator->user_id)
        ->assertSet('showSelfRoleChangeConfirmation', true)
        ->assertSee('Confirm your role change')
        ->assertSeeHtml('class="modal"')
        ->assertSeeHtml('wire:click="cancelSelfRoleChange"')
        ->assertSeeHtml('wire:click="confirmSelfRoleChange"');

    $component
        ->call('cancelSelfRoleChange')
        ->assertSet('showSelfRoleChangeConfirmation', false)
        ->assertSet('pendingSelfRoleChangeUserId', null)
        ->assertSet('pendingSelfRoleChangeRoleId', null);

    expect($administrator->fresh()->role_id)->toBe($superadminRole->role_id);

    $component
        ->set("selectedRoleIds.{$administrator->user_id}", $replacementRole->role_id)
        ->call('saveUserRole', $administrator->user_id)
        ->call('confirmSelfRoleChange')
        ->assertSet('showSelfRoleChangeConfirmation', false);

    expect($administrator->fresh()->role_id)->toBe($replacementRole->role_id);
});

// Task 11.2: last-superadmin guard
// **Validates: Requirements 7.7**
it('prevents the confirmed role change that would remove the final superadmin user', function (): void {
    $superadminRole = Roles::query()->firstOrCreate(['role_name' => 'superadmin']);
    $replacementRole = Roles::query()->create(['role_name' => 'event-coordinator']);
    $administrator = User::query()
        ->where('role_id', $superadminRole->role_id)
        ->sole();

    $this->actingAs($administrator);

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->set("selectedRoleIds.{$administrator->user_id}", $replacementRole->role_id)
        ->call('saveUserRole', $administrator->user_id)
        ->call('confirmSelfRoleChange')
        ->assertDispatched(
            'toast-show',
            type: 'error',
            message: 'Cannot remove the last superadmin user.',
            position: 'toast-top',
        );

    expect($administrator->fresh()->role_id)->toBe($superadminRole->role_id)
        ->and(Transaction_Logs::query()->where('action', 'RBAC_USER_ROLE_CHANGED')->count())->toBe(0);
});

// Task 11.2: transactional role persistence, audit, and success feedback
// **Validates: Requirements 4.2, 7.5**
it('changes another users role transactionally, records before and after role IDs, and reports success', function (): void {
    $superadminRole = Roles::query()->firstOrCreate(['role_name' => 'superadmin']);
    $originalRole = Roles::query()->create(['role_name' => 'event-coordinator']);
    $replacementRole = Roles::query()->create(['role_name' => 'event-manager']);
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create(['role_id' => $originalRole->role_id]);

    $this->actingAs($administrator);

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->set("selectedRoleIds.{$managedUser->user_id}", $replacementRole->role_id)
        ->call('saveUserRole', $managedUser->user_id)
        ->assertDispatched(
            'toast-show',
            type: 'success',
            message: 'User role updated successfully.',
            position: 'toast-top',
        );

    $transactionLog = Transaction_Logs::query()
        ->where('user_id', $administrator->user_id)
        ->where('action', 'RBAC_USER_ROLE_CHANGED')
        ->sole();
    $payload = json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR);

    expect($managedUser->fresh()->role_id)->toBe($replacementRole->role_id)
        ->and($payload['record_id'])->toBe($managedUser->user_id)
        ->and($payload['before'])->toBe(['role_id' => $originalRole->role_id])
        ->and($payload['after'])->toBe(['role_id' => $replacementRole->role_id])
        ->and($payload['timestamp'])->toEndWith('Z');
});

it('forbids stale superadmin user-role mutations after the actor loses that role', function (string $mutation): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $osaRole = Roles::query()->where('role_name', 'osa')->sole();
    $replacementRole = Roles::query()->create(['role_name' => 'stale-user-role-replacement']);
    $originalRole = Roles::query()->create(['role_name' => 'stale-user-role-original']);
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create(['role_id' => $originalRole->role_id]);

    $this->actingAs($actor);

    $component = Livewire::withoutLazyLoading()->test(Index::class);

    if ($mutation === 'save') {
        $component->set("selectedRoleIds.{$managedUser->user_id}", $replacementRole->role_id);
    }

    if ($mutation === 'confirm') {
        $component
            ->set("selectedRoleIds.{$actor->user_id}", $replacementRole->role_id)
            ->call('saveUserRole', $actor->user_id)
            ->assertSet('showSelfRoleChangeConfirmation', true);
    }

    $actor->update(['role_id' => $osaRole->role_id]);
    Auth::setUser($actor->fresh());

    if ($mutation === 'save') {
        $component
            ->call('saveUserRole', $managedUser->user_id)
            ->assertForbidden();

        expect($managedUser->fresh()->role_id)->toBe($originalRole->role_id);

        return;
    }

    $component
        ->call('confirmSelfRoleChange')
        ->assertForbidden();

    expect($actor->fresh()->role_id)->toBe($osaRole->role_id);
})->with([
    'row selector save action' => 'save',
    'self-change confirmation action' => 'confirm',
]);

it('forbids a stale superadmin from creating a user', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $osaRole = Roles::query()->where('role_name', 'osa')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $email = 'stale-created-user@example.test';

    Notification::fake();
    $this->actingAs($actor);

    $component = Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->set('createForm.name', 'Stale Created User')
        ->set('createForm.email', $email)
        ->set('createForm.password', 'Password1!')
        ->set('createForm.password_confirmation', 'Password1!')
        ->set('createForm.role', 'osa')
        ->set('createForm.phone', '09123456789');

    $actor->update(['role_id' => $osaRole->role_id]);
    Auth::setUser($actor->fresh());

    $component
        ->call('createUser')
        ->assertForbidden();

    expect(User::withTrashed()->where('email', $email)->exists())->toBeFalse();
});

it('forbids a stale superadmin from deleting a user', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $osaRole = Roles::query()->where('role_name', 'osa')->sole();
    $gsoRole = Roles::query()->where('role_name', 'gso')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create(['role_id' => $gsoRole->role_id]);

    $this->actingAs($actor);

    $component = Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->call('openDeleteModal', $managedUser->user_id, $managedUser->name);

    $actor->update(['role_id' => $osaRole->role_id]);
    Auth::setUser($actor->fresh());

    $component
        ->call('confirmDelete')
        ->assertForbidden();

    expect(User::withTrashed()->findOrFail($managedUser->user_id)->trashed())->toBeFalse();
});

it('revokes database sessions and remember tokens after assigning another users role', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $originalRole = Roles::query()->create(['role_name' => 'credential-revocation-original']);
    $replacementRole = Roles::query()->create(['role_name' => 'credential-revocation-replacement']);
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create([
        'role_id' => $originalRole->role_id,
        'remember_token' => 'managed-user-original-token',
    ]);
    $sessionId = 'managed-user-role-change-session';

    config()->set('session.driver', 'database');
    DB::table(config('session.table'))->insert([
        'id' => $sessionId,
        'user_id' => $managedUser->user_id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'test-payload',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($administrator);

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->set("selectedRoleIds.{$managedUser->user_id}", $replacementRole->role_id)
        ->call('saveUserRole', $managedUser->user_id)
        ->assertDispatched(
            'toast-show',
            type: 'success',
            message: 'User role updated successfully.',
            position: 'toast-top',
        );

    expect($managedUser->fresh()->role_id)->toBe($replacementRole->role_id)
        ->and($managedUser->fresh()->remember_token)->not->toBe('managed-user-original-token')
        ->and(DB::table(config('session.table'))->where('id', $sessionId)->exists())
        ->toBeFalse();
});

it('logs out and revokes credentials after a superadmin confirms their own role change', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $replacementRole = Roles::query()->create(['role_name' => 'self-credential-revocation-role']);
    $administrator = User::factory()->create([
        'role_id' => $superadminRole->role_id,
        'remember_token' => 'self-role-change-original-token',
    ]);
    User::factory()->create(['role_id' => $superadminRole->role_id]);
    $sessionId = 'self-role-change-session';

    config()->set('session.driver', 'database');
    DB::table(config('session.table'))->insert([
        'id' => $sessionId,
        'user_id' => $administrator->user_id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'test-payload',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($administrator);

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->set("selectedRoleIds.{$administrator->user_id}", $replacementRole->role_id)
        ->call('saveUserRole', $administrator->user_id)
        ->call('confirmSelfRoleChange');

    $this->assertGuest();

    expect($administrator->fresh()->role_id)->toBe($replacementRole->role_id)
        ->and($administrator->fresh()->remember_token)->not->toBe('self-role-change-original-token')
        ->and(DB::table(config('session.table'))->where('id', $sessionId)->exists())
        ->toBeFalse();
});

it('uses the audited credential-revoking role assignment path from the legacy user edit action', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $originalRole = Roles::query()->where('role_name', 'gso')->sole();
    $replacementRole = Roles::query()->where('role_name', 'osa')->sole();
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create([
        'name' => 'Managed User',
        'email' => 'managed-user@example.test',
        'phone' => '09123456789',
        'role_id' => $originalRole->role_id,
        'remember_token' => 'legacy-role-change-original-token',
    ]);
    $sessionId = 'legacy-user-role-change-session';

    config()->set('session.driver', 'database');
    DB::table(config('session.table'))->insert([
        'id' => $sessionId,
        'user_id' => $managedUser->user_id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'test-payload',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($administrator);

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->call('loadEditForm', $managedUser->user_id)
        ->set('editForm.name', 'Managed User')
        ->set('editForm.email', 'managed-user@example.test')
        ->set('editForm.phone', '09123456789')
        ->set('editForm.role', $replacementRole->role_name)
        ->call('updateUser');

    expect($managedUser->fresh()->role_id)->toBe($replacementRole->role_id)
        ->and($managedUser->fresh()->remember_token)->not->toBe('legacy-role-change-original-token')
        ->and(DB::table(config('session.table'))->where('id', $sessionId)->exists())
        ->toBeFalse()
        ->and(Transaction_Logs::query()
            ->where('user_id', $administrator->user_id)
            ->where('action', 'RBAC_USER_ROLE_CHANGED')
            ->exists())->toBeTrue();
});

it('forbids a stale superadmin from changing a role through the legacy user edit action', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $osaRole = Roles::query()->where('role_name', 'osa')->sole();
    $originalRole = Roles::query()->where('role_name', 'gso')->sole();
    $replacementRole = Roles::query()->where('role_name', 'student-org')->sole();
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create([
        'name' => 'Managed User',
        'email' => 'stale-legacy-user@example.test',
        'phone' => '09123456789',
        'role_id' => $originalRole->role_id,
    ]);

    $this->actingAs($administrator);

    $component = Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->call('loadEditForm', $managedUser->user_id)
        ->set('editForm.name', 'Managed User')
        ->set('editForm.email', 'stale-legacy-user@example.test')
        ->set('editForm.phone', '09123456789')
        ->set('editForm.role', $replacementRole->role_name);

    $administrator->update(['role_id' => $osaRole->role_id]);
    Auth::setUser($administrator->fresh());

    $component
        ->call('updateUser')
        ->assertForbidden();

    expect($managedUser->fresh()->role_id)->toBe($originalRole->role_id);
});

it('treats saving an unchanged role as a no-op without logging or revoking sessions', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $currentRole = Roles::query()->create(['role_name' => 'unchanged-role']);
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create([
        'role_id' => $currentRole->role_id,
        'remember_token' => 'unchanged-role-token',
    ]);
    $sessionId = 'unchanged-role-session';

    config()->set('session.driver', 'database');
    DB::table(config('session.table'))->insert([
        'id' => $sessionId,
        'user_id' => $managedUser->user_id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'test-payload',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($administrator);

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->call('saveUserRole', $managedUser->user_id)
        ->assertDispatched(
            'toast-show',
            type: 'info',
            message: 'This user already has the selected role.',
            position: 'toast-top',
        );

    expect(Transaction_Logs::query()->where('action', 'RBAC_USER_ROLE_CHANGED')->exists())->toBeFalse()
        ->and($managedUser->fresh()->remember_token)->toBe('unchanged-role-token')
        ->and(DB::table(config('session.table'))->where('id', $sessionId)->exists())->toBeTrue();
});

it('does not open the self-change dialog or log out when the current user saves their unchanged role', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);

    $this->actingAs($administrator);

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->call('saveUserRole', $administrator->user_id)
        ->assertSet('showSelfRoleChangeConfirmation', false)
        ->assertSet('pendingSelfRoleChangeUserId', null);

    $this->assertAuthenticatedAs($administrator);
    expect(Transaction_Logs::query()->where('action', 'RBAC_USER_ROLE_CHANGED')->exists())->toBeFalse();
});

it('restores the row role selector to the stored role when persisting the change fails', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $originalRole = Roles::query()->create(['role_name' => 'failure-original-role']);
    $replacementRole = Roles::query()->create(['role_name' => 'failure-replacement-role']);
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create(['role_id' => $originalRole->role_id]);

    $this->actingAs($administrator);

    User::updating(static function (User $user) use ($managedUser): void {
        if ($user->user_id === $managedUser->user_id) {
            throw new RuntimeException('Simulated persistence failure.');
        }
    });

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->set("selectedRoleIds.{$managedUser->user_id}", $replacementRole->role_id)
        ->call('saveUserRole', $managedUser->user_id)
        ->assertDispatched('toast-show', type: 'error')
        ->assertSet("selectedRoleIds.{$managedUser->user_id}", $originalRole->role_id);

    expect($managedUser->fresh()->role_id)->toBe($originalRole->role_id);
});

it('only tracks role selections for the users on the current page', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);
    User::factory()->count(14)->create(['role_id' => $superadminRole->role_id]);

    $this->actingAs($administrator);

    $component = Livewire::withoutLazyLoading()->test(Index::class);

    expect($component->get('selectedRoleIds'))->toHaveCount(10);
});

it('tracks the role selection for a user created after the page loaded', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $replacementRole = Roles::query()->create(['role_name' => 'late-user-replacement-role']);
    $administrator = User::factory()->create(['role_id' => $superadminRole->role_id]);

    $this->actingAs($administrator);

    $component = Livewire::withoutLazyLoading()->test(Index::class);

    $lateUser = User::factory()->create(['role_id' => $superadminRole->role_id]);

    $component->call('$refresh')
        ->assertSet("selectedRoleIds.{$lateUser->user_id}", $superadminRole->role_id)
        ->set("selectedRoleIds.{$lateUser->user_id}", $replacementRole->role_id)
        ->call('saveUserRole', $lateUser->user_id)
        ->assertHasNoErrors();

    expect($lateUser->fresh()->role_id)->toBe($replacementRole->role_id);
});
