<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departments = [
            'College of Engineering',
            'College of Arts and Sciences',
            'College of Business Administration',
            'College of Education',
            'College of Information Technology',
            'College of Nursing',
        ];

        $courses = [
            ['code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science'],
            ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
            ['code' => 'BSCpE', 'name' => 'Bachelor of Science in Computer Engineering'],
            ['code' => 'BSECE', 'name' => 'Bachelor of Science in Electronics Engineering'],
            ['code' => 'BSME', 'name' => 'Bachelor of Science in Mechanical Engineering'],
            ['code' => 'BSCE', 'name' => 'Bachelor of Science in Civil Engineering'],
            ['code' => 'BSBA', 'name' => 'Bachelor of Science in Business Administration'],
            ['code' => 'BSA', 'name' => 'Bachelor of Science in Accountancy'],
            ['code' => 'BSED', 'name' => 'Bachelor of Secondary Education'],
            ['code' => 'BEED', 'name' => 'Bachelor of Elementary Education'],
        ];

        $course = fake()->randomElement($courses);

        return [
            'course_code' => $course['code'],
            'course_name' => $course['name'],
            'department' => fake()->randomElement($departments),
        ];
    }
}
