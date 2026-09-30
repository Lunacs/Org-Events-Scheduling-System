<?php

use App\Models\Transaction_Logs;
use App\Models\User;
use App\Services\TransactionLogService;

// Validates: Requirements 7.5
it('records each supported RBAC operation with its JSON payload', function (string $action): void {
    $user = User::factory()->create();
    $payload = [
        'record_id' => 42,
        'before' => ['role_name' => 'osa'],
        'after' => ['role_name' => 'gso'],
        'timestamp' => '2026-08-21T20:56:33.000000Z',
    ];

    $this->actingAs($user);

    TransactionLogService::logRbacOperation($action, $payload);

    $transactionLog = Transaction_Logs::query()->sole();

    expect($transactionLog->user_id)->toBe($user->user_id)
        ->and($transactionLog->action)->toBe($action)
        ->and(json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR))->toBe($payload);
})->with([
    'permission created' => 'RBAC_PERMISSION_CREATED',
    'permission renamed' => 'RBAC_PERMISSION_RENAMED',
    'permission deleted' => 'RBAC_PERMISSION_DELETED',
    'role created' => 'RBAC_ROLE_CREATED',
    'role renamed' => 'RBAC_ROLE_RENAMED',
    'role deleted' => 'RBAC_ROLE_DELETED',
    'permissions mapped' => 'RBAC_PERMISSIONS_MAPPED',
    'user role changed' => 'RBAC_USER_ROLE_CHANGED',
]);

// Validates: Requirements 7.5
it('throws when the RBAC payload cannot be encoded as JSON', function (): void {
    $user = User::factory()->create();
    $resource = fopen('php://memory', 'r');

    $this->actingAs($user);

    expect(fn (): null => TransactionLogService::logRbacOperation('RBAC_ROLE_CREATED', [
        'record_id' => 42,
        'before' => null,
        'after' => ['resource' => $resource],
        'timestamp' => '2026-08-21T20:56:33.000000Z',
    ]))->toThrow(JsonException::class);

    fclose($resource);

    expect(Transaction_Logs::count())->toBe(0);
});

it('preserves RBAC audit payloads that exceed the legacy details column capacity', function (): void {
    $user = User::factory()->create();
    $payload = [
        'record_id' => 42,
        'before' => ['permission_ids' => range(1, 200)],
        'after' => ['permission_ids' => range(201, 400)],
        'timestamp' => '2026-08-22T12:00:00.000000Z',
    ];

    expect(strlen(json_encode($payload, JSON_THROW_ON_ERROR)))->toBeGreaterThan(255);

    $this->actingAs($user);

    $thrown = null;

    try {
        TransactionLogService::logRbacOperation('RBAC_PERMISSIONS_MAPPED', $payload);
    } catch (Throwable $exception) {
        $thrown = $exception;
    }

    expect($thrown)->toBeNull();

    $transactionLog = Transaction_Logs::query()->sole();

    expect(json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR))->toBe($payload);
});
