<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'office_code' => 'GSO',
                'office_name' => 'General Services Office',
                'description' => 'Manages facilities, venues, and general services for events.',
            ],
            [
                'office_code' => 'VPAA',
                'office_name' => 'Vice President for Academic Affairs',
                'description' => 'Oversees academic-related events and activities.',
            ],
            [
                'office_code' => 'VPSA',
                'office_name' => 'Vice President for Student Affairs',
                'description' => 'Handles student welfare and student organization activities.',
            ],
            [
                'office_code' => 'FINANCE',
                'office_name' => 'Finance Office',
                'description' => 'Manages budget approval and financial aspects of events.',
            ],
            [
                'office_code' => 'SECURITY',
                'office_name' => 'Security Office',
                'description' => 'Ensures safety and security during events.',
            ],
            [
                'office_code' => 'REGISTRAR',
                'office_name' => 'Registrar Office',
                'description' => 'Handles academic records and certifications.',
            ],
        ];

        foreach ($offices as $office) {
            \App\Models\Office::create($office);
        }
    }
}
