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
            $scheduleDate = $ticket && $ticket->date_from 
                ? $ticket->date_from 
                : now()->addDays(rand(7, 60))->format('Y-m-d');
            
            $scheduleVenue = $ticket && $ticket->venue_requested
                ? $ticket->venue_requested
                : fake()->randomElement($venues);

            // Generate realistic event times
            $startTime = fake()->randomElement(['08:00', '09:00', '10:00', '13:00', '14:00']);
            $endTime = fake()->randomElement(['12:00', '15:00', '16:00', '17:00', '18:00']);

            \App\Models\Event_Schedule::create([
                'event_id' => $event->event_id,
                'start_date' => $scheduleDate,
                'end_date' => $ticket && $ticket->date_to ? $ticket->date_to : $scheduleDate,
                'start_time' => $ticket && $ticket->time_from ? $ticket->time_from : $startTime,
                'end_time' => $ticket && $ticket->time_to ? $ticket->time_to : $endTime,
                'venue' => $scheduleVenue,
                'status' => fake()->randomElement($statuses),
                'remarks' => fake()->optional(0.6)->randomElement($remarks),
            ]);
        }

        $this->command->info('Created event schedules for ' . $events->count() . ' events');
    }
}
