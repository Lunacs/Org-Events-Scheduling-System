<?php

namespace Database\Seeders;

use App\Models\Event_Type;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentUser = User::where('role_id', User::ROLE_STUDENT_ORG)->first();

        if (! $studentUser) {
            $studentUser = User::factory()->create(['role_id' => User::ROLE_STUDENT_ORG]);
        }

        $eventTypes = Event_Type::pluck('event_type_id', 'type_name');

        if ($eventTypes->isEmpty()) {
            return;
        }

        $tickets = [
            [
                'ticket_number' => 'TKT-001',
                'title' => 'Leadership Summit 2024',
                'event_type' => 'Venue Booking',
                'sponsoring_body' => 'Student Council',
                'participants' => [220, 30],
                'start_offset' => 20,
                'duration_days' => 1,
            ],
            [
                'ticket_number' => 'TKT-002',
                'title' => 'Science Fair',
                'event_type' => 'Equipment',
                'sponsoring_body' => 'Science Club',
                'participants' => [90, 20],
                'start_offset' => 25,
                'duration_days' => 2,
            ],
            [
                'ticket_number' => 'TKT-003',
                'title' => 'Cultural Night',
                'event_type' => 'Logistics',
                'sponsoring_body' => 'Cultural Society',
                'participants' => [60, 10],
                'start_offset' => 35,
                'duration_days' => 1,
            ],
            [
                'ticket_number' => 'TKT-004',
                'title' => 'Workshop Series',
                'event_type' => 'Equipment',
                'sponsoring_body' => 'Innovation Hub',
                'participants' => [120, 40],
                'start_offset' => -1,
                'duration_days' => 1,
            ],
            [
                'ticket_number' => 'TKT-005',
                'title' => 'Community Outreach',
                'event_type' => 'Logistics',
                'sponsoring_body' => 'Community Club',
                'participants' => [80, 25],
                'start_offset' => -2,
                'duration_days' => 1,
            ],
        ];

        foreach ($tickets as $ticketData) {
            $eventTypeId = $eventTypes[$ticketData['event_type']] ?? null;

            if (! $eventTypeId) {
                continue;
            }

            $startDate = Carbon::today()->addDays($ticketData['start_offset']);
            $endDate = $startDate->copy()->addDays($ticketData['duration_days']);

            [$plvParticipants, $externalParticipants] = $ticketData['participants'];

            Ticket::updateOrCreate(
                ['ticket_number' => $ticketData['ticket_number']],
                [
                    'user_id' => $studentUser->user_id,
                    'event_type_id' => $eventTypeId,
                    'title' => $ticketData['title'],
                    'description' => $ticketData['title'] . ' request seeded for dashboard demos.',
                    'plv_participants' => $plvParticipants,
                    'external_participants' => $externalParticipants,
                    'total_participants' => $plvParticipants + $externalParticipants,
                    'sponsoring_body' => $ticketData['sponsoring_body'],
                    'venue_requested' => 'Main Hall',
                    'alternate_venue' => 'Auditorium',
                    'special_requirements' => 'Projectors and sound system',
                    'date-from' => $startDate->format('Y-m-d'),
                    'date-to' => $endDate->format('Y-m-d'),
                    'time-from' => '09:00',
                    'time-to' => '17:00',
                    'status' => 'pending',
                ]
            );
        }
    }
}
