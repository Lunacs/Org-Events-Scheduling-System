<?php

namespace Database\Seeders;

use App\Models\Roles;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Built-in roles are created protected and attached to their own portal,
     * matching what RbacSeeder enforces.
     */
    public function run(): void
    {
        foreach (Roles::BUILT_IN_ROLE_NAMES as $roleName) {
            Roles::firstOrCreate(
                ['role_name' => $roleName],
                ['is_protected' => true, 'base_portal' => $roleName],
            );
        }
    }
}
