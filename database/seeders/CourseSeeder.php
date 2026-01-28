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
        ///TODO: Edit course-names that corresponds to their course-codes and edit department into their respective colleges
        // Create specific courses
        $courses = [
            ['course_code' => 'BSIT', 'course_name' => 'Bachelor of Science in Information Technology', 'department' => 'College of Information Technology'],
            ['course_code' => 'BSPA', 'course_name' => 'Bachelor of Science in Information Technology', 'department' => 'College of Information Technology'],
            ['course_code' => 'BSEE', 'course_name' => 'Bachelor of Science in ', 'department' => 'College of Engineering'],
            ['course_code' => 'BAC', 'course_name' => 'Bachelor of Science in ', 'department' => 'College of Engineering'],
            ['course_code' => 'BSSW', 'course_name' => 'Bachelor of Science in ', 'department' => 'College of Engineering'],
            ['course_code' => 'BSCE', 'course_name' => 'Bachelor of Science in Civil Engineering', 'department' => 'College of Engineering'],
            ['course_code' => 'BSBA FM', 'course_name' => 'Bachelor of Science in Business Administration ', 'department' => 'College of Business Administration'],
            ['course_code' => 'BSBA MM', 'course_name' => 'Bachelor of Science in Business Administration ', 'department' => 'College of Business Administration'],
            ['course_code' => 'BSBA HRDM', 'course_name' => 'Bachelor of Science in Business Administration ', 'department' => 'College of Business Administration'],
            ['course_code' => 'BSA', 'course_name' => 'Bachelor of Science in Accountancy', 'department' => 'College of Business Administration'],
            ['course_code' => 'BSED ENGLISH', 'course_name' => 'Bachelor of Secondary Education ', 'department' => 'College of Education'],
            ['course_code' => 'BSED MATHEMATICS', 'course_name' => 'Bachelor of Secondary Education ', 'department' => 'College of Education'],
            ['course_code' => 'BSED FILIPINO', 'course_name' => 'Bachelor of Secondary Education ', 'department' => 'College of Education'],
            ['course_code' => 'BSED SCIENCE', 'course_name' => 'Bachelor of Secondary Education ', 'department' => 'College of Education'],
            ['course_code' => 'BSED SOCSTUD', 'course_name' => 'Bachelor of Secondary Education ', 'department' => 'College of Education'],
            ['course_code' => 'BECED', 'course_name' => 'Bachelor of Early Childhood Education', 'department' => 'College of Education'],
            ['course_code' => 'NON-ACAD', 'course_name' => 'Bachelor of Early Childhood Education', 'department' => 'College of Education'],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
