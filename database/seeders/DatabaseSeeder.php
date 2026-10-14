<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reference data, RBAC and accounts, in order of dependencies.
        // Every Feature test seeds these, so keep them free of workflow data.
        $this->call([
            CourseSeeder::class,
            StudentOrganizationSeeder::class,
            OfficeSeeder::class,
            PositionsSeeder::class,
            RolesSeeder::class,
            RbacSeeder::class,
            UserSeeder::class,
            EventTypeSeeder::class,
            FundSourcesSeeder::class,
            VenueSeeder::class,
        ]);

        if (app()->environment('testing')) {
            return;
        }

        // Site content and demo data: a custom RBAC role and tickets replayed
        // through the full approval workflow.
        $this->call([
            ContentSectionSeeder::class,
            FaqSeeder::class,
            CustomRoleSeeder::class,
            TicketSeeder::class,
        ]);
    }
}
