<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific courses
        $courses = [
            ['course_code' => 'BSCS', 'course_name' => 'Bachelor of Science in Computer Science', 'department' => 'College of Information Technology'],
            ['course_code' => 'BSIT', 'course_name' => 'Bachelor of Science in Information Technology', 'department' => 'College of Information Technology'],
            ['course_code' => 'BSCpE', 'course_name' => 'Bachelor of Science in Computer Engineering', 'department' => 'College of Engineering'],
            ['course_code' => 'BSECE', 'course_name' => 'Bachelor of Science in Electronics Engineering', 'department' => 'College of Engineering'],
            ['course_code' => 'BSME', 'course_name' => 'Bachelor of Science in Mechanical Engineering', 'department' => 'College of Engineering'],
            ['course_code' => 'BSCE', 'course_name' => 'Bachelor of Science in Civil Engineering', 'department' => 'College of Engineering'],
            ['course_code' => 'BSBA', 'course_name' => 'Bachelor of Science in Business Administration', 'department' => 'College of Business Administration'],
            ['course_code' => 'BSA', 'course_name' => 'Bachelor of Science in Accountancy', 'department' => 'College of Business Administration'],
            ['course_code' => 'BSED', 'course_name' => 'Bachelor of Secondary Education', 'department' => 'College of Education'],
            ['course_code' => 'BEED', 'course_name' => 'Bachelor of Elementary Education', 'department' => 'College of Education'],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
