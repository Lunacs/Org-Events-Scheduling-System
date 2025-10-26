<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $notes = [
            'Please ensure all participants are registered before the event.',
            'Equipment setup required 2 hours before the event starts.',
            'Refreshments will be provided for all attendees.',
            'COVID-19 safety protocols must be strictly followed.',
            'Photography and videography permitted with prior approval.',
            'Parking arrangements have been coordinated with security.',
            'Sound system and microphones needed for the event.',
            'Emergency medical team on standby during the event.',
            'Registration desk will be set up at the main entrance.',
            'Event marshals assigned for crowd management.',
        ];

        return [
            'ticket_id' => \App\Models\Ticket::factory(),
            'event__type_id' => \App\Models\Event_Type::factory(),
            'notes' => fake()->optional(0.7)->randomElement($notes),
        ];
    }
}
