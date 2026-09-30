<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Flag existing built-in roles as protected so deployments that only run
     * migrations (without the RBAC seeder) cannot rename or delete them.
     */
    public function up(): void
    {
        DB::table('roles')
            ->whereIn('role_name', ['superadmin', 'osa', 'gso', 'student-org'])
            ->update(['is_protected' => true]);
    }

    /**
     * The protected flag is left in place because the seeder owns it as well.
     */
    public function down(): void
    {
        //
    }
};
