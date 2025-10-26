<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event_Type>
 */
class EventTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventTypes = [
            [
                'name' => 'Academic Conference',
                'description' => 'Educational conferences, seminars, and workshops organized by student organizations.',
            ],
            [
                'name' => 'Cultural Event',
                'description' => 'Cultural celebrations, festivals, and heritage showcases.',
            ],
            [
                'name' => 'Sports Competition',
                'description' => 'Athletic events, tournaments, and sports competitions.',
            ],
            [
                'name' => 'Community Service',
                'description' => 'Outreach programs, volunteer activities, and community engagement.',
            ],
            [
                'name' => 'Workshop',
                'description' => 'Skills training sessions, hands-on learning, and professional development.',
            ],
            [
                'name' => 'Fundraising',
                'description' => 'Fundraising activities and charity events.',
            ],
            [
                'name' => 'Social Gathering',
                'description' => 'Social events, parties, and recreational activities.',
            ],
            [
                'name' => 'Competition',
                'description' => 'Academic and talent competitions.',
            ],
        ];

        $type = fake()->randomElement($eventTypes);

        return [
            'type_name' => $type['name'],
            'description' => $type['description'],
        ];
    }
}
