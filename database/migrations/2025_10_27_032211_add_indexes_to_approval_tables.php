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
        Schema::table('o_s_a__approvals', function (Blueprint $table) {
            $table->dropIndex('osa_approvals_ticket_created_idx');
            $table->dropIndex('osa_approvals_decision_idx');
            $table->dropIndex('osa_approvals_user_idx');
        });

        Schema::table('office__approvals', function (Blueprint $table) {
            $table->dropIndex('office_approvals_ticket_created_idx');
            $table->dropIndex('office_approvals_decision_idx');
            $table->dropIndex('office_approvals_office_idx');
            $table->dropIndex('office_approvals_user_idx');
        });
    }
};
