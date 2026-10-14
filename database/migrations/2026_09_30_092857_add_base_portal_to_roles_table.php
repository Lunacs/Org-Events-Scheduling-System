<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->string('base_portal', 32)->nullable()->after('is_protected');
        });

        foreach (['superadmin', 'osa', 'gso', 'student-org'] as $roleName) {
            DB::table('roles')->where('role_name', $roleName)->update(['base_portal' => $roleName]);
        }

        DB::table('roles')->whereNull('base_portal')->update(['base_portal' => 'viewer']);

        Cache::forget('rbac_role_portals');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropColumn('base_portal');
        });

        Cache::forget('rbac_role_portals');
    }
};
