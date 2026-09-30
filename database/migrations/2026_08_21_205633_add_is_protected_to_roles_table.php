<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasRoleNameUniqueIndex = Schema::hasIndex('roles', ['role_name'], 'unique');

        Schema::table('roles', static function (Blueprint $table) use ($hasRoleNameUniqueIndex): void {
            $table->boolean('is_protected')->default(false)->after('role_name');

            if (! $hasRoleNameUniqueIndex) {
                $table->unique('role_name', 'dynamic_rbac_roles_role_name_unique');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('roles', 'dynamic_rbac_roles_role_name_unique')) {
            Schema::table('roles', static function (Blueprint $table): void {
                $table->dropUnique('dynamic_rbac_roles_role_name_unique');
            });
        }

        Schema::table('roles', static function (Blueprint $table): void {
            $table->dropColumn('is_protected');
        });
    }
};
