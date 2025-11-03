<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds a standalone index on status column for faster COUNT queries
     */
    public function up(): void
    {
        // Helper to check index existence without doctrine/dbal
        $indexExists = function (string $table, string $indexName): bool {
            $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE `Key_name` = ?', [$indexName]);
            return ! empty($rows);
        };

        Schema::table('tickets', function (Blueprint $table) use ($indexExists) {
            // Add standalone status index if it doesn't exist
            // This helps with COUNT queries that filter only by status
            if (! $indexExists('tickets', 'tickets_status_idx')) {
                $table->index('status', 'tickets_status_idx');
            }
            
            // Add index on created_at for month/year filtering
            if (! $indexExists('tickets', 'tickets_created_at_idx')) {
                $table->index('created_at', 'tickets_created_at_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            try { $table->dropIndex('tickets_status_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('tickets_created_at_idx'); } catch (\Throwable $e) {}
        });
    }
};
