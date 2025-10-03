<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Office;
use App\Models\Student_Organization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create SuperAdmin user
        User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@plv.edu.ph',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERADMIN,
            'org_id' => null,
            'office_id' => null,
        ]);

        // Create OSA Admin users
        User::create([
            'name' => 'OSA Administrator',
            'email' => 'osa@plv.edu.ph',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_OSA,
            'org_id' => null,
            'office_id' => null,
        ]);

        User::create([
            'name' => 'Maria Santos',
            'email' => 'maria.santos@plv.edu.ph',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_OSA,
            'org_id' => null,
            'office_id' => null,
        ]);

        // Create GSO Admin users (assuming you have offices)
        $gsoOffice = Office::where('office_name', 'like', '%GSO%')->first();

        User::create([
            'name' => 'GSO Administrator',
            'email' => 'gso@plv.edu.ph',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_GSO,
            'org_id' => null,
            'office_id' => $gsoOffice?->office_id,
        ]);

        User::create([
            'name' => 'Carlos Rodriguez',
            'email' => 'carlos.rodriguez@plv.edu.ph',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_GSO,
            'org_id' => null,
            'office_id' => $gsoOffice?->office_id,
        ]);

        // Create Student Organization users
        $studentOrgs = Student_Organization::limit(3)->get();

        if ($studentOrgs->isNotEmpty()) {
            User::create([
                'name' => 'Student Organization Leader',
                'email' => 'student@plv.edu.ph',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => User::ROLE_STUDENT_ORG,
                'org_id' => $studentOrgs->first()->org_id,
                'office_id' => null,
            ]);

            User::create([
                'name' => 'Ana Dela Cruz',
                'email' => 'ana.delacruz@plv.edu.ph',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => User::ROLE_STUDENT_ORG,
                'org_id' => $studentOrgs->first()->org_id,
                'office_id' => null,
            ]);

            // Create users for other student organizations if available
            if ($studentOrgs->count() > 1) {
                User::create([
                    'name' => 'John Martinez',
                    'email' => 'john.martinez@plv.edu.ph',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_STUDENT_ORG,
                    'org_id' => $studentOrgs->get(1)->org_id,
                    'office_id' => null,
                ]);
            }

            if ($studentOrgs->count() > 2) {
                User::create([
                    'name' => 'Lily Garcia',
                    'email' => 'lily.garcia@plv.edu.ph',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_STUDENT_ORG,
                    'org_id' => $studentOrgs->get(2)->org_id,
                    'office_id' => null,
                ]);
            }
        }

        // Create additional test users
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_STUDENT_ORG,
            'org_id' => $studentOrgs->first()?->org_id,
            'office_id' => null,
        ]);

        // Create office staff users if offices exist
        $offices = Office::limit(2)->get();
        foreach ($offices as $office) {
            User::create([
                'name' => $office->office_head ?? 'Office Staff',
                'email' => strtolower(str_replace(' ', '.', $office->office_name)) . '@plv.edu.ph',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => User::ROLE_GSO, // Assuming office staff are GSO role
                'org_id' => null,
                'office_id' => $office->office_id,
            ]);
        }

        $this->command->info('User seeder completed successfully!');
        $this->command->info('Default login credentials:');
        $this->command->info('SuperAdmin: superadmin@plv.edu.ph / password');
        $this->command->info('OSA Admin: osa@plv.edu.ph / password');
        $this->command->info('GSO Admin: gso@plv.edu.ph / password');
        $this->command->info('Student Org: student@plv.edu.ph / password');
    }
}
