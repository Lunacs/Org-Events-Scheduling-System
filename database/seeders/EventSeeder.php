<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tickets = Ticket::all();

        foreach ($tickets as $ticket) {
            Event::updateOrCreate(
                ['ticket_id' => $ticket->ticket_id],
                [
                    'event__type_id' => $ticket->event_type_id,
                    'notes' => 'Generated from ticket seeder for dashboard preview.',
                ]
            );
        }
    }
}
