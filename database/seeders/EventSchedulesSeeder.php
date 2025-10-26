<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Event_Schedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventSchedulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::all();

        foreach ($events as $index => $event) {
            $scheduleDate = Carbon::today()->addDays(7 + ($index * 3))->setTime(9, 0);

            Event_Schedule::updateOrCreate(
                [
                    'event_id' => $event->event_id,
                    'schedule_date' => $scheduleDate,
                ],
                [
                    'schedule_venue' => 'Main Hall',
                    'status' => 'approved',
                    'remarks' => 'Auto-scheduled for dashboard metrics.',
                ]
            );
        }
    }
}
