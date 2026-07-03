<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FundSourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //All fund sources here
        $fundSources = [
            ['source_name' => 'N/A'],
            ['source_name' => 'Organizational Funds'],
            ['source_name' => 'University/Appropriate Government Funds'],
        ];
        foreach ($fundSources as $fundSource) {
            \App\Models\Fund_Sources::create($fundSource);
        }
    }
}
