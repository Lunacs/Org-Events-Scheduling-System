<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Student_Organization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = Course::limit(3)->get();

        // Seed student organizations here
        Student_Organization::create([
            'org_code' => 'VITS',
            'org_name' => 'Valenzuela Information Technology Society',
            'course_id' => $course->first()->course_id,
            'adviser_name' => 'Ruffa May Monis',
        ]);
    }
}
