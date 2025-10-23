<?php

namespace Database\Seeders;

use App\Models\Roles;
use Couchbase\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //All roles here
        $roles = [
            ['role_name' => 'superadmin'],
            ['role_name' => 'osa'],
            ['role_name' => 'gso'],
            ['role_name' => 'student-org'],
        ];
        foreach ($roles as $role) {
            Roles::create($role);
        }
    }
}
