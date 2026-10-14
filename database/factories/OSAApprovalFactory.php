<?php

namespace Database\Factories;

use App\Models\OSA_Approval;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OSA_Approval>
 */
class OSAApprovalFactory extends Factory
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
            'forwarded',
        ];

        $approvalRemarks = [
            'Approved. All requirements are met.',
            'Event aligns with university policies and guidelines.',
            'Please revise the budget proposal and resubmit.',
            'The proposed date conflicts with another major event.',
            'Additional safety measures are required.',
            'Need more details about the target participants.',
            'Needs revision due to incomplete documentation.',
            'Pending review by the committee.',
            'Event objectives are well-defined and achievable.',
            'Please coordinate with the General Services Office for venue setup.',
        ];

        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory()->state(fn () => ['role_id' => User::getRoleId('osa')]),
            'decision' => fake()->randomElement($decisions),
            'remarks' => fake()->optional(0.8)->randomElement($approvalRemarks),
        ];
    }

    /**
     * Indicate that the approval decision is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision' => 'approved',
            'remarks' => 'Approved. All requirements are met.',
        ]);
    }

    /**
     * Indicate that the approval decision is for revision.
     */
    public function for_revision(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision' => 'for_revision',
            'remarks' => 'Needs revision due to incomplete documentation.',
        ]);
    }

    /**
     * Indicate that the approval is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision' => 'pending',
            'remarks' => 'Pending review by the committee.',
        ]);
    }

    /**
     * Indicate that the approval needs revision.
     */
    public function needRevision(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision' => 'for_revision',
            'remarks' => 'Please revise the proposal and resubmit.',
        ]);
    }
}
