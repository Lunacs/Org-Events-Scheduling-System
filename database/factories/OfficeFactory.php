<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Office>
 */
class OfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $offices = [
            [
                'code' => 'GSO',
                'name' => 'General Services Office',
                'description' => 'Manages facilities, venues, and general services for events.',
            ],
            [
                'code' => 'VPAA',
                'name' => 'Vice President for Academic Affairs',
                'description' => 'Oversees academic-related events and activities.',
            ],
            [
                'code' => 'VPSA',
                'name' => 'Vice President for Student Affairs',
                'description' => 'Handles student welfare and student organization activities.',
            ],
            [
                'code' => 'FINANCE',
                'name' => 'Finance Office',
                'description' => 'Manages budget approval and financial aspects of events.',
            ],
            [
                'code' => 'SECURITY',
                'name' => 'Security Office',
                'description' => 'Ensures safety and security during events.',
            ],
            [
                'code' => 'REGISTRAR',
                'name' => 'Registrar Office',
                'description' => 'Handles academic records and certifications.',
            ],
        ];

        $office = fake()->randomElement($offices);

        return [
            'office_code' => $office['code'],
            'office_name' => $office['name'],
            'description' => $office['description'],
        ];
    }
}
