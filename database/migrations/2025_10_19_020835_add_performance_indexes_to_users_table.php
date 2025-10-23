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
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes in reverse order
            $table->dropIndex('idx_users_office_id');
            $table->dropIndex('idx_users_org_id');
            $table->dropIndex('idx_users_role_verified');
            $table->dropIndex('idx_users_email_verified_at');
            $table->dropIndex('idx_users_role');
        });
    }
};
