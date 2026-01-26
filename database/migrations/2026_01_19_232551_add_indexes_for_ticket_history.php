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
        Schema::table('o_s_a__approvals', function (Blueprint $table) {
            try { $table->dropIndex('idx_osa_ticket_updated'); } catch (\Throwable $e) {}
        });

        Schema::table('tickets', function (Blueprint $table) {
            try { $table->dropIndex('idx_tickets_user_status'); } catch (\Throwable $e) {}
        });

        Schema::table('event_schedules', function (Blueprint $table) {
            try { $table->dropIndex('idx_schedules_event_status'); } catch (\Throwable $e) {}
        });
    }
};
