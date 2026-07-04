<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Student_Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student_Organization>
 */
class StudentOrganizationFactory extends Factory
{
    protected $model = Student_Organization::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orgTypes = [
            'Academic',
            'Cultural',
            'Sports',
            'Religious',
            'Environmental',
            'Service',
            'Arts',
            'Technology',
        ];

        $orgNames = [
            'Computer Society',
            'Engineering Society',
            'Business Club',
            'Education Association',
            'Cultural Arts Group',
            'Environmental Advocates',
            'Student Council',
            'Debate Society',
            'Music Club',
            'Dance Troupe',
            'Photography Society',
            'Writers Guild',
        ];

        $orgName = fake()->randomElement($orgNames);
        $orgType = fake()->randomElement($orgTypes);

        return [
            'org_code' => strtoupper(fake()->unique()->bothify('ORG-####')),
            'org_name' => $orgName,
            'course_id' => Course::factory(),
            'adviser_name' => fake()->name(),
            'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']), // 75% active
        ];
    }

    /**
     * Indicate that the organization is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the organization is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the organization is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
