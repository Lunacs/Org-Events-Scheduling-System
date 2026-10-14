<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offices = [
            [
                'office_code' => 'OSA',
                'office_name' => 'Office of Student Affairs',
                'description' => 'Primary office responsible for student organization event approvals',
            ],
            [
                'office_code' => 'GSO',
                'office_name' => 'General Services Office',
                'description' => 'Manages facilities, venues, and general services for events.',
            ],
        ];

        foreach ($offices as $office) {
            Office::updateOrCreate(['office_code' => $office['office_code']], $office);
        }
    }
}
