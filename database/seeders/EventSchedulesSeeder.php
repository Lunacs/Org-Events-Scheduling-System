<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSchedulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all events to create schedules for them
        $events = \App\Models\Event::all();

        if ($events->isEmpty()) {
            $this->command->warn('No events found. Skipping event schedules seeder.');
            return;
        }

        $venues = [
            'University Auditorium',
            'Main Gymnasium',
            'Conference Hall A',
            'Conference Hall B',
            'Student Center',
            'Outdoor Field',
            'Multipurpose Hall',
            'Library Function Room',
            'College Quadrangle',
            'Covered Court',
        ];

        $statuses = ['approved', 'approved', 'approved', 'pending', 'rejected'];

        $remarks = [
            'All preparations are complete.',
            'Venue has been reserved and confirmed.',
            'Equipment check completed successfully.',
            'Event concluded successfully with full attendance.',
            'Outstanding event with positive feedback.',
        ];

        foreach ($events as $event) {
            // Get the ticket's requested date and venue
            $ticket = $event->ticket;
            $scheduleDate = $ticket && $ticket->getAttribute('date-requested') 
                ? $ticket->getAttribute('date-requested') 
                : now()->addDays(rand(7, 60));
            
            $scheduleVenue = $ticket && $ticket->getAttribute('venue-requested')
                ? $ticket->getAttribute('venue-requested')
                : fake()->randomElement($venues);

            \App\Models\Event_Schedule::create([
                'event_id' => $event->event_id,
                'schedule_date' => $scheduleDate,
                'schedule_venue' => $scheduleVenue,
                'status' => fake()->randomElement($statuses),
                'remarks' => fake()->optional(0.6)->randomElement($remarks),
            ]);
        }

        $this->command->info('Created event schedules for ' . $events->count() . ' events');
    }
}
