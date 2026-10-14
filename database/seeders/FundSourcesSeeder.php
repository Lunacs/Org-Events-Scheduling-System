<?php

namespace Database\Seeders;

use App\Models\Fund_Sources;
use Illuminate\Database\Seeder;

class FundSourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Organizational Funds must be source_id 1: the submit form, the ticket editor and
        // Ticket::getBudgetBreakdownLabelAttribute() label its breakdown by that id.
        $fundSources = [
            ['source_name' => 'Organizational Funds'],
            ['source_name' => 'University/Appropriate Government Funds'],
            ['source_name' => 'N/A'],
        ];
        foreach ($fundSources as $fundSource) {
            Fund_Sources::firstOrCreate($fundSource);
        }
    }
}
