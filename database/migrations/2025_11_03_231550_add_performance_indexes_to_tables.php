<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds performance indexes to frequently queried columns
     */
    public function up(): void
    {
        // Helper to check index existence without doctrine/dbal
        $indexExists = function (string $table, string $indexName): bool {
            $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE `Key_name` = ?', [$indexName]);
            return ! empty($rows);
        };

        // Tickets table - Most queried table
        Schema::table('tickets', function (Blueprint $table) use ($indexExists) {
            if (! $indexExists('tickets', 'tickets_user_status_idx')) {
                $table->index(['user_id', 'status'], 'tickets_user_status_idx');
            }
            if (! $indexExists('tickets', 'tickets_status_updated_idx')) {
                $table->index(['status', 'updated_at'], 'tickets_status_updated_idx');
            }
        });

        // Events table
        Schema::table('events', function (Blueprint $table) use ($indexExists) {
            if (! $indexExists('events', 'events_ticket_created_idx')) {
                $table->index(['ticket_id', 'created_at'], 'events_ticket_created_idx');
            }
        });

        // Event Schedules table
        Schema::table('event_schedules', function (Blueprint $table) use ($indexExists) {
            if (! $indexExists('event_schedules', 'schedules_event_date_idx')) {
                $table->index(['event_id', 'start_date'], 'schedules_event_date_idx');
            }
        });

        // Attachments table
        Schema::table('attachments', function (Blueprint $table) use ($indexExists) {
            if (! $indexExists('attachments', 'attachments_ticket_id_idx')) {
                $table->index('ticket_id', 'attachments_ticket_id_idx');
            }
            if (! $indexExists('attachments', 'attachments_created_at_idx')) {
                $table->index('created_at', 'attachments_created_at_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexSafely('tickets', 'tickets_user_status_idx');
        $this->dropIndexSafely('tickets', 'tickets_status_updated_idx');
        $this->dropIndexSafely('events', 'events_ticket_created_idx');
        $this->dropIndexSafely('event_schedules', 'schedules_event_date_idx');
        $this->dropIndexSafely('attachments', 'attachments_ticket_id_idx');
        $this->dropIndexSafely('attachments', 'attachments_created_at_idx');
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
