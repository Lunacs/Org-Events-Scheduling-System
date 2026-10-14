<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Positions;
use App\Models\Student_Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Every account is keyed on its email and marked verified, so the seeder can
     * be re-run and every seeded login works straight after `migrate:fresh --seed`.
     */
    public function run(): void
    {
        // Create SuperAdmin user
        $this->seedUser([
            'name' => 'Super Administrator',
            'email' => 'superadmin@plv.edu.ph',
            'password' => 'password',
            'role_id' => User::getRoleId('superadmin'),
            'org_id' => null,
            'office_id' => null,
        ]);

        // Create OSA Admin users
        $this->seedUser([
            'name' => 'OSA Administrator',
            'email' => 'osa@plv.edu.ph',
            'password' => 'password',
            'role_id' => User::getRoleId('osa'),
            'org_id' => null,
            'office_id' => Office::where('office_code', 'OSA')->first()?->office_id,
        ]);

        // Create GSO Admin users (assuming you have offices)
        $gsoOffice = Office::where('office_code', 'like', '%GSO%')->first();

        $this->seedUser([
            'name' => 'GSO Administrator',
            'email' => 'gso@plv.edu.ph',
            'password' => 'password',
            'role_id' => User::getRoleId('gso'),
            'org_id' => null,
            'office_id' => $gsoOffice?->office_id,
        ]);

        // Create Student Organization users
        $studentOrgs = Student_Organization::limit(3)->get();
        $positions = Positions::all();

        if ($studentOrgs->isNotEmpty()) {
            $this->seedUser([
                'name' => 'Student Organization Leader',
                'email' => 'student@plv.edu.ph',
                'password' => 'password',
                'role_id' => User::getRoleId('student-org'),
                'phone' => '09171234501',
                'org_id' => $studentOrgs->first()->org_id,
                'office_id' => null,
                'position_id' => $positions->where('position_name', 'President')->first()?->position_id,
            ]);

            $this->seedUser([
                'name' => 'Ana Dela Cruz',
                'email' => 'ana.delacruz@plv.edu.ph',
                'password' => 'password',
                'role_id' => User::getRoleId('student-org'),
                'phone' => '09171234502',
                'org_id' => $studentOrgs->first()->org_id,
                'office_id' => null,
                'position_id' => $positions->where('position_name', 'Chairperson')->first()?->position_id,
            ]);

            // Create users for other student organizations if available
            if ($studentOrgs->count() > 1) {
                $this->seedUser([
                    'name' => 'John Martinez',
                    'email' => 'john.martinez@plv.edu.ph',
                    'password' => 'password',
                    'role_id' => User::getRoleId('student-org'),
                    'phone' => '09171234503',
                    'org_id' => $studentOrgs->get(1)->org_id,
                    'office_id' => null,
                    'position_id' => $positions->where('position_name', 'President')->first()?->position_id,
                ]);
            }

            if ($studentOrgs->count() > 2) {
                $this->seedUser([
                    'name' => 'Lily Garcia',
                    'email' => 'lily.garcia@plv.edu.ph',
                    'password' => 'password',
                    'role_id' => User::getRoleId('student-org'),
                    'phone' => '09171234504',
                    'org_id' => $studentOrgs->get(2)->org_id,
                    'office_id' => null,
                    'position_id' => $positions->where('position_name', 'Adviser')->first()?->position_id,
                ]);
            }
        }

        // Create additional test users
        $this->seedUser([
            'name' => 'Elliot Anderson',
            'email' => 'johnreygabon@plv.edu.ph',
            'password' => 'password',
            'role_id' => User::getRoleId('student-org'),
            'phone' => '09171234505',
            'org_id' => $studentOrgs->first()?->org_id,
            'office_id' => null,
            'position_id' => $positions->where('position_name', 'President')->first()?->position_id,
        ]);

        $this->seedUser([
            'name' => 'Adrian Acob',
            'email' => 'adrianacob@plv.edu.ph',
            'password' => 'password',
            'role_id' => User::getRoleId('student-org'),
            'phone' => '09171234506',
            'org_id' => $studentOrgs->first()?->org_id,
            'office_id' => null,
            'position_id' => $positions->where('position_name', 'President')->first()?->position_id,
        ]);

        $this->seedUser([
            'name' => 'Johnrel Parente',
            'email' => 'johnrelparente@plv.edu.ph',
            'password' => 'password',
            'role_id' => User::getRoleId('student-org'),
            'phone' => '09171234507',
            'org_id' => $studentOrgs->first()?->org_id,
            'office_id' => null,
            'position_id' => $positions->where('position_name', 'President')->first()?->position_id,
        ]);

        $this->seedUser([
            'name' => 'Lanoira Cutie',
            'email' => 'lexerichsontalavera@plv.edu.ph',
            'password' => 'Lanoira1',
            'role_id' => User::getRoleId('student-org'),
            'phone' => '09171234508',
            'org_id' => Student_Organization::where('org_code', 'VITS')->first()?->org_id,
            'office_id' => null,
            'position_id' => $positions->where('position_name', 'President')->first()?->position_id,
        ]);

        // Create office staff users if offices exist
        $offices = Office::limit(2)->get();
        foreach ($offices as $office) {
            $this->seedUser([
                'name' => $office->office_head ?? 'Office Staff',
                'email' => strtolower(str_replace(' ', '.', $office->office_name)).'@plv.edu.ph',
                'password' => 'password',
                'role_id' => User::getRoleId('gso'), // Assuming office staff are GSO role
                'org_id' => null,
                'office_id' => $office->office_id,
            ]);
        }

        $this->command?->info('User seeder completed successfully! Every seeded account is verified.');
        $this->command?->info('Default login credentials (password: password):');
        $this->command?->info('  SuperAdmin:  superadmin@plv.edu.ph');
        $this->command?->info('  OSA Admin:   osa@plv.edu.ph');
        $this->command?->info('  GSO Admin:   gso@plv.edu.ph');
        $this->command?->info('  Student Org: student@plv.edu.ph, ana.delacruz@, john.martinez@, lily.garcia@plv.edu.ph');
    }

    /**
     * Create or refresh a verified account keyed on its email.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function seedUser(array $attributes): User
    {
        $user = User::withTrashed()->firstOrNew(['email' => $attributes['email']]);

        // email_verified_at is not mass assignable, so force-fill works outside db:seed too.
        $user->forceFill([
            ...$attributes,
            'password' => Hash::make($attributes['password']),
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }
}
