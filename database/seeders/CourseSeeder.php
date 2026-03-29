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
            ['course_code' => 'BSIT', 'course_name' => 'Bachelor of Science in Information Technology', 'department' => 'College of Engineering and Information Technology'],
            ['course_code' => 'BSPA', 'course_name' => 'Bachelor of Science in Public Administration', 'department' => 'College of Public Administration and Governance'],
            ['course_code' => 'BSEE', 'course_name' => 'Bachelor of Science in Electrical Engineering', 'department' => 'College of Engineering and Information Technology'],
            ['course_code' => 'BAC', 'course_name' => 'Bachelor of Arts in Communication', 'department' => 'College of Arts and Sciences'],
            ['course_code' => 'BSSW', 'course_name' => 'Bachelor of Science in Social Work', 'department' => 'College of Arts and Sciences'],
            ['course_code' => 'BSCE', 'course_name' => 'Bachelor of Science in Civil Engineering', 'department' => 'College of Engineering and Information Technology'],
            ['course_code' => 'BSBA FM', 'course_name' => 'Bachelor of Science in Business Administration Major in Financial Management', 'department' => 'College of Accountancy and Business Administration'],
            ['course_code' => 'BSBA MM', 'course_name' => 'Bachelor of Science in Business Administration Major in Marketing Management', 'department' => 'College of Accountancy and Business Administration'],
            ['course_code' => 'BSBA HRDM', 'course_name' => 'Bachelor of Science in Business Administration Major in Human Resource Development Management', 'department' => 'College of Accountancy and Business Administration'],
            ['course_code' => 'BSA', 'course_name' => 'Bachelor of Science in Accountancy', 'department' => 'College of Accountancy and Business Administration'],
            ['course_code' => 'BSED ENGLISH', 'course_name' => 'Bachelor of Secondary Education Major in English', 'department' => 'College of Education'],
            ['course_code' => 'BSED MATHEMATICS', 'course_name' => 'Bachelor of Secondary Education Major in Mathematics', 'department' => 'College of Education'],
            ['course_code' => 'BSED FILIPINO', 'course_name' => 'Bachelor of Secondary Education Major in Filipino', 'department' => 'College of Education'],
            ['course_code' => 'BSED SCIENCE', 'course_name' => 'Bachelor of Secondary Education Major in Science', 'department' => 'College of Education'],
            ['course_code' => 'BSED SOCSTUD', 'course_name' => 'Bachelor of Secondary Education Major in Social Studies', 'department' => 'College of Education'],
            ['course_code' => 'BECED', 'course_name' => 'Bachelor of Early Childhood Education', 'department' => 'College of Education'],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
