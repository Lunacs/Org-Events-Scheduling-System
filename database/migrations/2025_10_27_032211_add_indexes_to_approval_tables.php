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
        // Add indexes to OSA Approvals table for better query performance
        Schema::table('o_s_a__approvals', function (Blueprint $table) {
            // Composite index for ticket history queries (most common query pattern)
            $table->index(['ticket_id', 'created_at'], 'osa_approvals_ticket_created_idx');
            
            // Index for decision-based queries
            $table->index('decision', 'osa_approvals_decision_idx');
            
            // Index for user-based queries
            $table->index('user_id', 'osa_approvals_user_idx');
        });

        // Add indexes to Office Approvals table for better query performance
        Schema::table('office__approvals', function (Blueprint $table) {
            // Composite index for ticket history queries
            $table->index(['ticket_id', 'created_at'], 'office_approvals_ticket_created_idx');
            
            // Index for decision-based queries
            $table->index('decision', 'office_approvals_decision_idx');
            
            // Index for office-based queries
            $table->index('office_id', 'office_approvals_office_idx');
            
            // Index for user-based queries
            $table->index('user_id', 'office_approvals_user_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexSafely('o_s_a__approvals', 'osa_approvals_ticket_created_idx');
        $this->dropIndexSafely('o_s_a__approvals', 'osa_approvals_decision_idx');
        $this->dropIndexSafely('o_s_a__approvals', 'osa_approvals_user_idx');

        $this->dropIndexSafely('office__approvals', 'office_approvals_ticket_created_idx');
        $this->dropIndexSafely('office__approvals', 'office_approvals_decision_idx');
        $this->dropIndexSafely('office__approvals', 'office_approvals_office_idx');
        $this->dropIndexSafely('office__approvals', 'office_approvals_user_idx');
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
