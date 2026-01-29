<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //All venues here
        $venues = [
            ['venue_name' => 'PLV Gymnasium', 'venue_location' => 'PLV Main Campus', 'is_active' => false],
            ['venue_name' => 'PLV Auditorium', 'venue_location' => 'PLV Main Campus - Main Building'],
            ['venue_name' => 'PLV Conference Hall', 'venue_location' => 'PLV Main Campus - COED Building'],
            ['venue_name' => 'PLV Open Field - Annex', 'venue_location' => 'PLV Annex Campus'],
            ['venue_name' => 'PLV Open Field - Main', 'venue_location' => 'PLV Main Campus'],
            ];
        foreach ($venues as $venue) {
            \App\Models\Venue::create($venue);
        }
    }
}
