<?php

use App\Livewire\Superadmin\Users\Index;
use App\Models\Roles;
use App\Models\Transaction_Logs;
use App\Models\User;
use App\Services\TransactionLogService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// Feature: dynamic-rbac, Property 19: Database Unique Constraints Reject Duplicate Permission and Role Names
// Validates: Requirements 7.1, 7.2
it('rejects duplicate permission names through the database unique constraint', function (): void {
    $attributes = [
        'name' => 'tickets.'.Str::uuid(),
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('permissions')->insert($attributes);

    try {
        DB::table('permissions')->insert($attributes);

        $this->fail('Expected the permissions.name unique constraint to reject a duplicate insert.');
    } catch (QueryException $exception) {
        expect($exception->getCode())->toBe('23000');
    }
})->repeat(100)->group('rbac', 'property');

// Feature: dynamic-rbac, Property 21: Every RBAC Operation Produces a Correctly Structured Transaction Log Entry
// Validates: Requirements 7.5
it('records a structurally valid transaction log for every RBAC operation', function (): void {
    $user = User::factory()->create();
    $actions = [
        'RBAC_PERMISSION_CREATED',
        'RBAC_PERMISSION_RENAMED',
        'RBAC_PERMISSION_DELETED',
        'RBAC_ROLE_CREATED',
        'RBAC_ROLE_RENAMED',
        'RBAC_ROLE_DELETED',
        'RBAC_PERMISSIONS_MAPPED',
        'RBAC_USER_ROLE_CHANGED',
    ];

    $this->actingAs($user);

    foreach ($actions as $recordId => $action) {
        $recordId++;
        $timestamp = now()->utc()->toISOString();

        TransactionLogService::logRbacOperation($action, [
            'record_id' => $recordId,
            'timestamp' => $timestamp,
        ]);

        $transactionLog = Transaction_Logs::query()
            ->where('user_id', $user->user_id)
            ->where('action', $action)
            ->sole();
        $details = json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR);

        expect($transactionLog->user_id)->toBe($user->user_id)
            ->and($transactionLog->action)->toBe($action)
            ->and($details)->toHaveKeys(['record_id', 'timestamp'])
            ->and($details['record_id'])->toBe($recordId)
            ->and($details['timestamp'])->toBe($timestamp);
    }

    expect(Transaction_Logs::query()->where('user_id', $user->user_id)->count())
        ->toBe(count($actions));
})->group('rbac', 'property');

// Feature: dynamic-rbac, Property 19: Database Unique Constraints Reject Duplicate Permission and Role Names
// Validates: Requirements 7.1, 7.2
it('rejects duplicate role names through the database unique constraint', function (): void {
    $attributes = [
        'role_name' => 'role-'.Str::uuid(),
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('roles')->insert($attributes);

    try {
        DB::table('roles')->insert($attributes);

        $this->fail('Expected the roles.role_name unique constraint to reject a duplicate insert.');
    } catch (QueryException $exception) {
        expect($exception->getCode())->toBe('23000');
    }
})->repeat(100)->group('rbac', 'property');

// Feature: dynamic-rbac, Property 20: Role Deletion Cascades All role_permission Rows for That Role
// Validates: Requirements 7.3
it('removes every permission assignment when a role is deleted', function (): void {
    $roleId = DB::table('roles')->insertGetId([
        'role_name' => 'role-'.Str::uuid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $assignmentCount = random_int(0, 10);

    for ($assignmentIndex = 0; $assignmentIndex < $assignmentCount; $assignmentIndex++) {
        $permissionId = DB::table('permissions')->insertGetId([
            'name' => 'permissions.'.Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('role_permission')->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
        ]);
    }

    expect(DB::table('role_permission')->where('role_id', $roleId)->count())
        ->toBe($assignmentCount);

    DB::table('roles')->where('role_id', $roleId)->delete();

    expect(DB::table('role_permission')->where('role_id', $roleId)->count())
        ->toBe(0);
})->repeat(100)->group('rbac', 'property');

// Task 11.7: superadmin lockout prevention
// **Validates: Requirements 7.7**
it('prevents the sole superadmin from confirming a role change to osa', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $osaRole = Roles::query()->where('role_name', 'osa')->sole();

    expect(User::query()->where('role_id', $superadminRole->role_id)->count())->toBe(1);

    $superadmin = User::query()
        ->where('role_id', $superadminRole->role_id)
        ->sole();

    $this->actingAs($superadmin);

    Livewire::withoutLazyLoading()
        ->test(Index::class)
        ->set("selectedRoleIds.{$superadmin->user_id}", $osaRole->role_id)
        ->call('saveUserRole', $superadmin->user_id)
        ->call('confirmSelfRoleChange')
        ->assertDispatched(
            'toast-show',
            type: 'error',
            message: 'Cannot remove the last superadmin user.',
            position: 'toast-top',
        );

    expect($superadmin->fresh()->role_id)->toBe($superadminRole->role_id);
});
