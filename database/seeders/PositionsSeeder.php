<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // All positions here
        $positions = [
            ['position_name' => 'Chairperson'],
            ['position_name' => 'Adviser'],
            ['position_name' => 'President'],
        ];

        foreach ($positions as $position) {
            \App\Models\Positions::create($position);
        }
    }
}
