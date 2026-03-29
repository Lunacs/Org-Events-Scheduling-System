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
        // PLV Student Organizations based on AY 2025-2026
        $acad_organizations = [
            [
                'org_code' => 'ACES',
                'org_name' => 'Association of Civil Engineering Students',
                'course_id' => \App\Models\Course::where('course_code', 'BSCE')->first()?->course_id ?? 1,
                'adviser_name' => 'Mc. Lorenz M. Castillo',
                'status' => 'active',
            ],
            [
                'org_code' => 'AEES',
                'org_name' => 'Association of Electrical Engineering Students',
                'course_id' => \App\Models\Course::where('course_code', 'BSEE')->first()?->course_id ?? 2,
                'adviser_name' => 'Cristen Kate Celestial',
                'status' => 'active',
            ],
            [
                'org_code' => 'AJFEx',
                'org_name' => 'Association of Junior Finance Executives',
                'course_id' => \App\Models\Course::where('course_code', 'BSBA FM')->first()?->course_id ?? 3,
                'adviser_name' => 'Lailani A. Santos',
                'status' => 'active',
            ],
            [
                'org_code' => 'BACSTAGE',
                'org_name' => 'Bachelor of Arts in Communication Studies - Theater Arts Grand Ensemble',
                'course_id' => \App\Models\Course::where('course_code', 'BAC')->first()?->course_id ?? 7,
                'adviser_name' => 'Renalyn T. Alvaran',
                'status' => 'active',
            ],
            [
                'org_code' => 'BASEEC',
                'org_name' => 'Believers And Seekers of Excellence among Early Childhood Educators',
                'course_id' => \App\Models\Course::where('course_code', 'BECED')->first()?->course_id ?? 9,
                'adviser_name' => 'Sharon D. Tarantan',
                'status' => 'active',
            ],
            [
                'org_code' => 'BPS',
                'org_name' => 'Blue Pencil Society',
                'course_id' => \App\Models\Course::where('course_code', 'BSED ENGLISH')->first()?->course_id ?? 1,
                'adviser_name' => 'John Dominic T. De Jesus',
                'status' => 'active',
            ],
            [
                'org_code' => 'GAME',
                'org_name' => 'Group of Aspiring Mathematics Educators √',
                'course_id' => \App\Models\Course::where('course_code', 'BSED MATHEMATICS')->first()?->course_id ?? 6,
                'adviser_name' => 'Cherry Mae M. Belloso',
                'status' => 'active',
            ],
            [
                'org_code' => 'JMA',
                'org_name' => 'Junior Marketing Association',
                'course_id' => \App\Models\Course::where('course_code', 'BSBA MM')->first()?->course_id ?? 7,
                'adviser_name' => 'Mary Charmaine G. Cruz',
                'status' => 'active',
            ],
            [
                'org_code' => 'JPIA',
                'org_name' => 'Junior Philippine Institute of Accountants - PLV',
                'course_id' => \App\Models\Course::where('course_code', 'BSA')->first()?->course_id ?? 3,
                'adviser_name' => 'Arden Mar S. Llanto',
                'status' => 'active',
            ],
            [
                'org_code' => 'JPMAP',
                'org_name' => 'Junior People Management Association of the Philippines - PLV',
                'course_id' => \App\Models\Course::where('course_code', 'BSBA HRDM')->first()?->course_id ?? 1,
                'adviser_name' => 'Hernani S. Saluna',
                'status' => 'active',
            ],
            [
                'org_code' => 'JSWAP',
                'org_name' => 'Junior Social Workers Association of the Philippines',
                'course_id' => \App\Models\Course::where('course_code', 'BSSW')->first()?->course_id ?? 1,
                'adviser_name' => 'Brandon Louise Encarnacion',
                'status' => 'active',
            ],
            [
                'org_code' => 'PSYCHSOC',
                'org_name' => 'Psychology Society',
                'course_id' => \App\Models\Course::where('course_code', 'BSP')->first()?->course_id ?? 1,
                'adviser_name' => 'Mary Camille Delima',
                'status' => 'active',
            ],
            [
                'org_code' => 'SADAFIL',
                'org_name' => 'Samahan ng mga Nagpapakadalubhasa sa Filipino',
                'course_id' => \App\Models\Course::where('course_code', 'BSED FILIPINO')->first()?->course_id ?? 1,
                'adviser_name' => 'Erden Gutierrez',
                'status' => 'active',
            ],
            [
                'org_code' => 'SCIRE',
                'org_name' => 'Society of Competitive Individuals Reaching for Excellence',
                'course_id' => \App\Models\Course::where('course_code', 'BSED SCIENCE')->first()?->course_id ?? 1,
                'adviser_name' => 'Christian T, Pantoja',
                'status' => 'active',
            ],
            [
                'org_code' => 'SÍNAG BÁNWA',
                'org_name' => 'Samahan ng mga Iskolar ng Araling Panlipunan para sa Bayan',
                'course_id' => \App\Models\Course::where('course_code', 'BSED SOCSTUD')->first()?->course_id ?? 1,
                'adviser_name' => 'Arvin Nikko Tacorda',
                'status' => 'active',
            ],
            [
                'org_code' => 'UPAS',
                'org_name' => 'Union of Public Administration Students',
                'course_id' => \App\Models\Course::where('course_code', 'BSPA')->first()?->course_id ?? 1,
                'adviser_name' => 'Ronhel S. Patricio',
                'status' => 'active',
            ],
            [
                'org_code' => 'VITS',
                'org_name' => 'Valenzuela Information Technology Society',
                'course_id' => \App\Models\Course::where('course_code', 'BSIT')->first()?->course_id ?? 1,
                'adviser_name' => 'Ruffa May Monis',
                'status' => 'active',
            ],
        ];

        $nonacad_organizations = [
            [
                'org_code' => 'AKLAT',
                'org_name' => 'Association for Knowledge, Learning, and Teaching',
                'course_id' => \App\Models\Course::where('course_code', '')->first()?->course_id ?? 1,
                'adviser_name' => 'Ma. Angelica C. Concepcion',
                'status' => 'active',
            ],
            [
                'org_code' => 'BRIDGE',
                'org_name' => 'Barangay Research Initiative Directed toward General welfare and Economic sustainability',
                'course_id' => \App\Models\Course::where('course_code', '')->first()?->course_id ?? 1,
                'adviser_name' => 'Mark Anthony Rebuya',
                'status' => 'active',
            ],
            [
                'org_code' => 'CDT',
                'org_name' => 'PLV Cultural Dance Troupe',
                'course_id' => \App\Models\Course::where('course_code', '')->first()?->course_id ?? 1,
                'adviser_name' => 'Noeme Grace H. Garcia',
                'status' => 'active',
            ],
            [
                'org_code' => 'DC',
                'org_name' => 'PLV Dance Company',
                'course_id' => \App\Models\Course::where('course_code', '')->first()?->course_id ?? 1,
                'adviser_name' => 'Wenzel Kyne Fernandez',
                'status' => 'active',
            ],
            [
                'org_code' => 'GOGH',
                'org_name' => 'PLV - Guild of Golden Hands',
                'course_id' => \App\Models\Course::where('course_code', '')->first()?->course_id ?? 1,
                'adviser_name' => 'Floraida S. Castañar-Alano',
                'status' => 'active',
            ],
            [
                'org_code' => 'RCYC',
                'org_name' => 'PLV- Red Cross Youth Council',
                'course_id' => \App\Models\Course::where('course_code', '')->first()?->course_id ?? 1,
                'adviser_name' => 'Lei Ann Lopez',
                'status' => 'active',
            ],
            [
                'org_code' => 'RS',
                'org_name' => 'Rover Scout',
                'course_id' => \App\Models\Course::where('course_code', '')->first()?->course_id ?? 1,
                'adviser_name' => 'Eduardo J. Andaya',
                'status' => 'active',
            ],
            [
                'org_code' => 'SHIELD',
                'org_name' => 'Student Helping in Imminent Events and Life-threatening Disasters',
                'course_id' => \App\Models\Course::where('course_code', '')->first()?->course_id ?? 1,
                'adviser_name' => 'Salvador P. Villete',
                'status' => 'active',
            ],
            [
                'org_code' => 'SINGERS',
                'org_name' => 'PLV Singers',
                'course_id' => \App\Models\Course::where('course_code', '')->first()?->course_id ?? 1,
                'adviser_name' => 'Arion Sanchez',
                'status' => 'active',
            ],
            [
                'org_code' => 'VTHK',
                'org_name' => 'Virulent Tigers Haud Ka\'Bu',
                'course_id' => \App\Models\Course::where('course_code', '')->first()?->course_id ?? 1,
                'adviser_name' => 'Jayron M. Garcia',
                'status' => 'active',
            ],
        ];

        foreach ($acad_organizations as $acad_org) {
            \App\Models\Student_Organization::create($acad_org);
        }

        foreach ($nonacad_organizations as $nonacad_org) {
            \App\Models\Student_Organization::create($nonacad_org);
        }

        $this->command->info('Created student organizations (acad and non acad).');
    }
}
