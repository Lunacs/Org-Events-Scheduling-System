<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Migrates existing approval records from osa_approvals and office_approvals
     * to the new approval_history table for audit trail purposes.
     *
     * After migration, osa_approvals and office_approvals will only contain
     * the current/latest approval state (one record per ticket/office).
     */
    public function up(): void
    {
        // Migrate OSA approvals to history
        DB::statement("
            INSERT INTO approval_history (
                ticket_id,
                user_id,
                office_id,
                approval_type,
                action,
                remarks,
                office_name,
                created_at,
                updated_at
            )
            SELECT 
                ticket_id,
                user_id,
                NULL as office_id,
                'osa' as approval_type,
                decision as action,
                remarks,
                'Office of Student Affairs' as office_name,
                created_at,
                updated_at
            FROM o_s_a__approvals
            ORDER BY created_at ASC
        ");

        // Migrate Office approvals to history
        DB::statement("
            INSERT INTO approval_history (
                ticket_id,
                user_id,
                office_id,
                approval_type,
                action,
                remarks,
                office_name,
                created_at,
                updated_at
            )
            SELECT 
                oa.ticket_id,
                oa.user_id,
                oa.office_id,
                'office' as approval_type,
                oa.decision as action,
                oa.remarks,
                COALESCE(o.office_name, 'Unknown Office') as office_name,
                oa.created_at,
                oa.updated_at
            FROM office__approvals oa
            LEFT JOIN offices o ON oa.office_id = o.office_id
            ORDER BY oa.created_at ASC
        ");

        // Note: We keep the existing records in osa_approvals and office_approvals
        // for backward compatibility and current state tracking.
        // The application code will now use updateOrCreate to maintain only
        // the latest state in those tables while logging all actions to approval_history.
    }

    /**
     * Reverse the migrations.
     *
     * Note: This migration does not delete existing approval records,
     * so there's nothing to reverse. The approval_history table will
     * be dropped by the create_approval_history_table migration's down() method.
     */
    public function down(): void
    {
        // Clear approval_history table
        // The table itself will be dropped by the create migration's down() method
        DB::table('approval_history')->truncate();
    }
};
