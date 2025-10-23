<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed courses here
        Course::create([
            'course_code' => 'BSIT',
            'course_name' => 'Bachelor of Science in Information Technology',
        ]);
    }
}
