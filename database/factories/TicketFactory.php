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
            'received',
            'gso_review',
            'approved',
            'for_revision',
            'needs_revision',
        ];

        // Generate realistic ticket number (e.g., TKT-2024-0001)
        $ticketNumber = 'TKT-' . date('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);

        $plvParticipants = fake()->numberBetween(20, 200);
        $externalParticipants = fake()->numberBetween(0, 50);

        return [
            'ticket_number' => $ticketNumber,
            'user_id' => \App\Models\User::factory(),
            'event_type_id' => \App\Models\Event_Type::factory(),
            'title' => fake()->randomElement($eventTitles),
            'description' => fake()->paragraph(3),
            'proponent_contact' => fake()->phoneNumber(),
            'adviser_contact' => fake()->phoneNumber(),
            'plv_participants' => $plvParticipants,
            'external_participants' => $externalParticipants,
            'total_participants' => $plvParticipants + $externalParticipants,
            'venue_requested' => fake()->randomElement($venues),
            'alternate_venue' => fake()->optional()->randomElement($venues),
            'special_requirements' => fake()->optional()->sentence(),
            'igp_requested' => fake()->boolean(30),
            'igp_details' => fake()->optional()->sentence(),
            'oc_accommodation' => fake()->optional()->sentence(),
            'oc_tsp' => fake()->optional()->randomElement(['in-house', 'outsourced']),
            'oc_driver_name' => fake()->optional()->name(),
            'oc_transportation_type' => fake()->optional()->word(),
            'oc_vehicle_plate_number' => fake()->optional()->bothify('???-####'),
            'oc_driver_contact_number' => fake()->optional()->phoneNumber(),
            'date_from' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'date_to' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'time_from' => fake()->time('H:i'),
            'time_to' => fake()->time('H:i'),
            'estimated_budget' => fake()->randomFloat(2, 5000, 50000),
            'budget_breakdown' => fake()->optional()->paragraph(),
            'additional_notes' => fake()->optional()->paragraph(),
            'fund_source_id' => \App\Models\Fund_Sources::factory(),
            'status' => fake()->randomElement($statuses),
        ];
    }

    /**
     * Indicate that the ticket is received.
     */
    public function received(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'received',
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
     * Indicate that the ticket is for revision.
     */
    public function for_revision(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'for_revision',
        ]);
    }

}
