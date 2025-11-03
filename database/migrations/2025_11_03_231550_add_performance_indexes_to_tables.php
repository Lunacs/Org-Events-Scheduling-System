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
        Schema::table('tickets', function (Blueprint $table) {
            try { $table->dropIndex('tickets_user_status_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('tickets_status_updated_idx'); } catch (\Throwable $e) {}
        });

        Schema::table('events', function (Blueprint $table) {
            try { $table->dropIndex('events_ticket_created_idx'); } catch (\Throwable $e) {}
        });

        Schema::table('event_schedules', function (Blueprint $table) {
            try { $table->dropIndex('schedules_event_date_idx'); } catch (\Throwable $e) {}
        });

        Schema::table('attachments', function (Blueprint $table) {
            try { $table->dropIndex('attachments_ticket_id_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('attachments_created_at_idx'); } catch (\Throwable $e) {}
        });
    }
};
