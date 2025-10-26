<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific student organizations with realistic data
        $organizations = [
            [
                'org_code' => 'CSSO',
                'org_name' => 'Computer Science Student Organization',
                'course_id' => \App\Models\Course::where('course_code', 'BSCS')->first()?->course_id ?? 1,
                'adviser_name' => 'Dr. Maria Santos',
                'status' => 'active',
            ],
            [
                'org_code' => 'ITSO',
                'org_name' => 'Information Technology Student Organization',
                'course_id' => \App\Models\Course::where('course_code', 'BSIT')->first()?->course_id ?? 2,
                'adviser_name' => 'Engr. John Martinez',
                'status' => 'active',
            ],
            [
                'org_code' => 'ENGSOC',
                'org_name' => 'Engineering Society',
                'course_id' => \App\Models\Course::where('course_code', 'BSCpE')->first()?->course_id ?? 3,
                'adviser_name' => 'Engr. Carlos Rodriguez',
                'status' => 'active',
            ],
            [
                'org_code' => 'BUSCLUB',
                'org_name' => 'Business Administration Club',
                'course_id' => \App\Models\Course::where('course_code', 'BSBA')->first()?->course_id ?? 7,
                'adviser_name' => 'Prof. Ana Garcia',
                'status' => 'active',
            ],
            [
                'org_code' => 'EDUC-ORG',
                'org_name' => 'Education Association',
                'course_id' => \App\Models\Course::where('course_code', 'BSED')->first()?->course_id ?? 9,
                'adviser_name' => 'Dr. Rosa Cruz',
                'status' => 'active',
            ],
            [
                'org_code' => 'CULT-ARTS',
                'org_name' => 'Cultural Arts Group',
                'course_id' => \App\Models\Course::where('course_code', 'BSCS')->first()?->course_id ?? 1,
                'adviser_name' => 'Prof. Luis Fernandez',
                'status' => 'active',
            ],
            [
                'org_code' => 'ENV-ADVOC',
                'org_name' => 'Environmental Advocates',
                'course_id' => \App\Models\Course::where('course_code', 'BSCE')->first()?->course_id ?? 6,
                'adviser_name' => 'Engr. Patricia Reyes',
                'status' => 'active',
            ],
            [
                'org_code' => 'SPORTS-CLUB',
                'org_name' => 'Sports and Athletics Club',
                'course_id' => \App\Models\Course::where('course_code', 'BSBA')->first()?->course_id ?? 7,
                'adviser_name' => 'Coach Rafael Santos',
                'status' => 'active',
            ],
            [
                'org_code' => 'TECH-INNOV',
                'org_name' => 'Technology and Innovation Society',
                'course_id' => \App\Models\Course::where('course_code', 'BSCpE')->first()?->course_id ?? 3,
                'adviser_name' => 'Dr. Elena Morales',
                'status' => 'active',
            ],
            [
                'org_code' => 'ALUMNI-ORG',
                'org_name' => 'Alumni Organization',
                'course_id' => \App\Models\Course::where('course_code', 'BSCS')->first()?->course_id ?? 1,
                'adviser_name' => 'Ms. Carmen Dela Cruz',
                'status' => 'inactive',
            ],
        ];

        foreach ($organizations as $org) {
            \App\Models\Student_Organization::create($org);
        }

        $this->command->info('Created 10 student organizations (9 active, 1 inactive)');
    }
}
