<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Run seeders in order of dependencies
        $this->call([
            CourseSeeder::class,
            StudentOrganizationSeeder::class,
            OfficeSeeder::class,
            UserSeeder::class,
            EventTypeSeeder::class,
            TicketSeeder::class,
            EventSeeder::class,
            EventSchedulesSeeder::class,
            OSAApprovalSeeder::class,
            OfficeApprovalSeeder::class,
            AttachmentSeeder::class,
            TransactionLogsSeeder::class,
        ]);
    }
}
