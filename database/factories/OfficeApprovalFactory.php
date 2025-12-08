<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Office_Approval>
 */
class OfficeApprovalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $decisions = [
            'approved',
            'for_revision',
            'pending',
            'conditional',
        ];

        $officeRemarks = [
            'Venue is available and reserved for the requested date.',
            'Security arrangements have been coordinated.',
            'Budget allocation approved by Finance Office.',
            'Please submit a detailed floor plan for the event setup.',
            'Conditional approval pending submission of safety plan.',
            'Needs Revision due to venue unavailability on the requested date.',
            'Approved with the condition of proper waste management.',
            'Electrical and technical requirements noted and approved.',
            'Catering services must comply with health and safety standards.',
            'Parking arrangements have been approved for the event.',
        ];

        return [
            'ticket_id' => \App\Models\Ticket::factory(),
            'office_id' => \App\Models\Office::factory(),
            'user_id' => \App\Models\User::factory()->state(['role' => 'gso']),
            'decision' => fake()->randomElement($decisions),
            'remarks' => fake()->optional(0.8)->randomElement($officeRemarks),
        ];
    }

    /**
     * Indicate that the approval decision is approved.
     */
    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'decision' => 'approved',
            'remarks' => 'Approved. All office requirements are met.',
        ]);
    }

    /**
     * Indicate that the approval decision is for revision.
     */
    public function for_revision(): static
    {
        return $this->state(fn(array $attributes) => [
            'decision' => 'for_revision',
            'remarks' => 'Needs revision due to resource constraints.',
        ]);
    }

    /**
     * Indicate that the approval is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'decision' => 'pending',
            'remarks' => 'Pending review and coordination.',
        ]);
    }

    /**
     * Indicate that the approval is conditional.
     */
    public function conditional(): static
    {
        return $this->state(fn(array $attributes) => [
            'decision' => 'conditional',
            'remarks' => 'Conditional approval pending additional requirements.',
        ]);
    }
}
