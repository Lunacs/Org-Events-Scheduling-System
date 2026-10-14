<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add indexes for commonly queried columns
            $table->index('role_id', 'idx_users_role');
            $table->index('email_verified_at', 'idx_users_email_verified_at');
            $table->index(['role_id', 'email_verified_at'], 'idx_users_role_verified');

            // Add indexes for foreign keys if not already indexed
            if (!Schema::hasColumn('users', 'org_id')) {
                return;
            }
            $table->index('org_id', 'idx_users_org_id');
            $table->index('office_id', 'idx_users_office_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes in reverse order
        $this->dropIndexSafely('users', 'idx_users_office_id');
        $this->dropIndexSafely('users', 'idx_users_org_id');
        $this->dropIndexSafely('users', 'idx_users_role_verified');
        $this->dropIndexSafely('users', 'idx_users_email_verified_at');
        $this->dropIndexSafely('users', 'idx_users_role');
    }

    /**
     * Drop an index if it exists. MySQL silently drops a foreign key's own
     * index once another index covers its columns, and then refuses to drop
     * that covering index (error 1553), so restore a plain index for any
     * foreign key that would be left without one.
     */
    private function dropIndexSafely(string $table, string $index): void
    {
        $indexes = collect(Schema::getIndexes($table));
        $target = $indexes->firstWhere('name', $index);

        if (! $target) {
            return;
        }

        $others = $indexes->reject(fn (array $i) => $i['name'] === $index);
        $covers = fn (array $i, array $columns) => array_slice($i['columns'], 0, count($columns)) === $columns;

        $orphaned = collect(Schema::getForeignKeys($table))->filter(
            fn (array $fk) => $covers($target, $fk['columns'])
                && ! $others->contains(fn (array $i) => $covers($i, $fk['columns']))
        );

        Schema::table($table, function (Blueprint $table) use ($index, $orphaned) {
            foreach ($orphaned as $fk) {
                $table->index($fk['columns'], $fk['name']);
            }

            $table->dropIndex($index);
        });
    }
};
