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
                'office_code' => 'GSO',
                'office_name' => 'General Services Office',
                'description' => 'Handles logistics, equipment, and venue coordination.',
            ],
            [
                'office_code' => 'OSA',
                'office_name' => 'Office of Student Affairs',
                'description' => 'Oversees student organization activities and approvals.',
            ],
        ];

        foreach ($offices as $office) {
            Office::updateOrCreate(
                ['office_code' => $office['office_code']],
                [
                    'office_name' => $office['office_name'],
                    'description' => $office['description'],
                ]
            );
        }
    }
}
