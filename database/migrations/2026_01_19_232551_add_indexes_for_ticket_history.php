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
        // Helper to check index existence without doctrine/dbal
        $indexExists = function (string $table, string $indexName): bool {
            $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE `Key_name` = ?', [$indexName]);
            return ! empty($rows);
        };

        // OSA Approvals - Add ticket/updated_at composite index
        Schema::table('o_s_a__approvals', function (Blueprint $table) use ($indexExists) {
            if (! $indexExists('o_s_a__approvals', 'idx_osa_ticket_updated')) {
                $table->index(['ticket_id', 'updated_at'], 'idx_osa_ticket_updated');
            }
        });

        // Event Schedules - Add event/status composite index
        Schema::table('event_schedules', function (Blueprint $table) use ($indexExists) {
            if (! $indexExists('event_schedules', 'idx_schedules_event_status')) {
                $table->index(['event_id', 'status'], 'idx_schedules_event_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexSafely('o_s_a__approvals', 'idx_osa_ticket_updated');
        $this->dropIndexSafely('event_schedules', 'idx_schedules_event_status');
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
