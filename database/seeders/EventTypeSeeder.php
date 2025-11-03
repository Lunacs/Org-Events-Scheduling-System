<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventTypes = [
            [
                'type_name' => 'General Assemblies and Similar Activities',
                'description' => 'Activities mainly catered for Student Organization members. (Ex. Socstud Week, Educ Week, etc.)',
            ],
            [
                'type_name' => 'Organization Shirts / IGP',
                'description' => 'Activities related to the production and distribution of organization shirts.',
            ],
            [
                'type_name' => 'Off-Campus Activities',
                'description' => 'Activities held outside the campus premises.',
            ],
            [
                'type_name' => 'Online Activities',
                'description' => 'Activities conducted through online platforms.',
            ],
            [
                'type_name' => 'Training, Rehearsals, Practices',
                'description' => 'Activities focused on skill development and preparation.',
            ],
        ];

        foreach ($eventTypes as $type) {
            \App\Models\Event_Type::create($type);
        }
    }
}
