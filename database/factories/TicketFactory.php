<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventTitles = [
            'Annual Tech Summit 2024',
            'Cultural Festival Celebration',
            'Sports Week Competition',
            'Environmental Awareness Campaign',
            'Leadership Training Workshop',
            'Community Outreach Program',
            'Academic Excellence Seminar',
            'Music and Arts Festival',
            'Career Development Workshop',
            'Charity Fundraising Event',
            'Innovation Challenge Competition',
            'Student Orientation Program',
        ];

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
        ];

        $statuses = [
            'pending',
            'approved',
            'rejected',
        ];

        // Generate realistic ticket number (e.g., TKT-2024-0001)
        $ticketNumber = 'TKT-' . date('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);

        return [
            'ticket_number' => $ticketNumber,
            'user_id' => \App\Models\User::factory(),
            'event_type_id' => \App\Models\Event_Type::factory(),
            'title' => fake()->randomElement($eventTitles),
            'description' => fake()->paragraph(3),
            'venue-requested' => fake()->randomElement($venues),
            'date-requested' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'status' => fake()->randomElement($statuses),
        ];
    }

    /**
     * Indicate that the ticket is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the ticket is approved.
     */
    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the ticket is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'rejected',
        ]);
    }

}
