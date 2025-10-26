<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event_Schedule>
 */
class EventSchedulesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $venues = [
            'University Auditorium',
            'Main Gymnasium',
            'Conference Hall A',
            'Conference Hall B',
            'Student Center',
            'Outdoor Field',
            'Multipurpose Hall',
            'Library Function Room',
            'College Quadrangle',
            'Covered Court',
            'Engineering Building - Room 301',
            'IT Building - Computer Lab',
            'Business Building - Seminar Room',
            'Sports Complex',
        ];

        $statuses = [
            'pending',
            'approved',
            'rejected',
        ];

        $remarks = [
            'All preparations are complete.',
            'Waiting for final approval from OSA.',
            'Venue has been reserved and confirmed.',
            'Equipment check completed successfully.',
            'Postponed due to weather conditions.',
            'Cancelled due to low participation.',
            'Event concluded successfully with full attendance.',
            'Some technical issues encountered but resolved.',
            'Outstanding event with positive feedback.',
            'Requires additional setup time.',
        ];

        // Generate a date between 1 week and 3 months from now
        $startDate = fake()->dateTimeBetween('+1 week', '+3 months');
        $endDate = (clone $startDate)->modify('+'.rand(0, 3).' days');

        // Generate realistic event times
        $startTime = fake()->randomElement(['08:00', '09:00', '10:00', '13:00', '14:00']);
        $endTime = fake()->randomElement(['12:00', '15:00', '16:00', '17:00', '18:00']);

        return [
            'event_id' => \App\Models\Event::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'venue' => fake()->randomElement($venues),
            'status' => fake()->randomElement($statuses),
            'remarks' => fake()->optional(0.6)->randomElement($remarks),
        ];
    }

    /**
     * Indicate that the schedule is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the schedule is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the schedule is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}
