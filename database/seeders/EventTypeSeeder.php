<?php

namespace Database\Seeders;

use App\Models\Event_Type;
use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['type_name' => 'Venue Booking', 'description' => 'Reserve on-campus venues for events.'],
            ['type_name' => 'Equipment', 'description' => 'Borrow AV and technical equipment.'],
            ['type_name' => 'Logistics', 'description' => 'Request personnel and logistics support.'],
            ['type_name' => 'Catering', 'description' => 'Coordinate catering needs for activities.'],
        ];

        foreach ($types as $type) {
            Event_Type::updateOrCreate(
                ['type_name' => $type['type_name']],
                ['description' => $type['description']]
            );
        }
    }
}
