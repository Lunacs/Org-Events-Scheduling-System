<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get approved tickets to create events for them
        $approvedTickets = \App\Models\Ticket::where('status', 'approved')->get();
        $eventTypes = \App\Models\Event_Type::all();

        if ($approvedTickets->isEmpty()) {
            $this->command->warn('No approved tickets found. Skipping event seeder.');
            return;
        }

        $notes = [
            'Please ensure all participants are registered before the event.',
            'Equipment setup required 2 hours before the event starts.',
            'Refreshments will be provided for all attendees.',
            'COVID-19 safety protocols must be strictly followed.',
            'Sound system and microphones needed for the event.',
            'Event marshals assigned for crowd management.',
        ];

        foreach ($approvedTickets as $ticket) {
            \App\Models\Event::create([
                'ticket_id' => $ticket->ticket_id,
                'event__type_id' => $ticket->event_type_id,
                'notes' => fake()->optional(0.7)->randomElement($notes),
            ]);
        }

        $this->command->info('Created events for ' . $approvedTickets->count() . ' approved tickets');
    }
}
