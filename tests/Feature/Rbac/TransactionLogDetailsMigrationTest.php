<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('refuses to narrow audit details when doing so would lose data', function (): void {
    $user = User::factory()->create();
    $details = str_repeat('a', 256);

    DB::table('transaction_logs')->insert([
        'user_id' => $user->user_id,
        'action' => 'RBAC_ROLE_DELETED',
        'details' => $details,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration = require database_path('migrations/2026_08_22_190309_widen_transaction_log_details_column.php');

    try {
        expect(fn (): mixed => $migration->down())
            ->toThrow(RuntimeException::class, 'Cannot roll back the transaction log details migration because rows exceed 255 characters.');

        expect(DB::table('transaction_logs')->value('details'))->toBe($details);
    } finally {
        $migration->up();
    }
});

it('narrows audit details when every value fits the legacy column capacity', function (): void {
    $user = User::factory()->create();
    $details = str_repeat('a', 255);

    DB::table('transaction_logs')->insert([
        'user_id' => $user->user_id,
        'action' => 'RBAC_ROLE_DELETED',
        'details' => $details,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration = require database_path('migrations/2026_08_22_190309_widen_transaction_log_details_column.php');

    try {
        $migration->down();

        $column = DB::selectOne('SHOW COLUMNS FROM transaction_logs LIKE ?', ['details']);

        expect($column->Type)->toBe('varchar(255)')
            ->and(DB::table('transaction_logs')->value('details'))->toBe($details);
    } finally {
        $migration->up();
    }
});
