<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'type_name' => 'Academic Conference',
                'description' => 'Educational conferences, seminars, and workshops organized by student organizations.',
            ],
            [
                'type_name' => 'Cultural Event',
                'description' => 'Cultural celebrations, festivals, and heritage showcases.',
            ],
            [
                'type_name' => 'Sports Competition',
                'description' => 'Athletic events, tournaments, and sports competitions.',
            ],
            [
                'type_name' => 'Community Service',
                'description' => 'Outreach programs, volunteer activities, and community engagement.',
            ],
            [
                'type_name' => 'Workshop',
                'description' => 'Skills training sessions, hands-on learning, and professional development.',
            ],
            [
                'type_name' => 'Fundraising',
                'description' => 'Fundraising activities and charity events.',
            ],
            [
                'type_name' => 'Social Gathering',
                'description' => 'Social events, parties, and recreational activities.',
            ],
            [
                'type_name' => 'Competition',
                'description' => 'Academic and talent competitions.',
            ],
        ];

        foreach ($eventTypes as $type) {
            \App\Models\Event_Type::create($type);
        }
    }
}
