<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Event_Type;
use App\Models\Fund_Sources;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $users = User::where('role_id', User::getRoleId('student-org'))->get();
        $eventTypes = Event_Type::all();
        $fundSources = Fund_Sources::all();

        if ($users->isEmpty() || $eventTypes->isEmpty() || $fundSources->isEmpty()) {
            $this->command->warn('No users, event types, or fund sources found. Skipping ticket seeder.');
            return;
        }

        // Create realistic tickets with different statuses
        $ticketData = [
            [
                'title' => 'Annual Tech Summit 2024',
                'description' => 'A comprehensive technology summit featuring keynote speakers, workshops, and networking sessions for IT and Computer Science students.',
                'venue_requested' => 'University Auditorium',
                'status' => 'amended',
            ],
            [
                'title' => 'Cultural Festival Celebration',
                'description' => 'Celebrating diversity through cultural performances, traditional food, and art exhibitions from different regions.',
                'venue_requested' => 'Main Gymnasium',
                'status' => 'approved',
            ],
            [
                'title' => 'Leadership Training Workshop',
                'description' => 'Interactive workshop on leadership skills, team building, and organizational management for student leaders.',
                'venue_requested' => 'Conference Hall A',
                'status' => 'received',
            ],
            [
                'title' => 'Community Outreach Program',
                'description' => 'Outreach program to provide educational assistance and learning materials to underprivileged children in local communities.',
                'venue_requested' => 'Outdoor Field',
                'status' => 'received',
            ],
            [
                'title' => 'Sports Week Competition',
                'description' => 'Inter-department sports competition featuring basketball, volleyball, badminton, and other athletic events.',
                'venue_requested' => 'Sports Complex',
                'status' => 'approved',
            ],
            [
                'title' => 'Environmental Awareness Campaign',
                'description' => 'Campus-wide campaign promoting environmental sustainability, waste management, and green initiatives.',
                'venue_requested' => 'College Quadrangle',
                'status' => 'gso_review',
            ],
            [
                'title' => 'Career Development Workshop',
                'description' => 'Workshop featuring industry professionals discussing career paths, resume building, and job interview techniques.',
                'venue_requested' => 'Conference Hall B',
                'status' => 'approved',
            ],
            [
                'title' => 'Music and Arts Festival',
                'description' => 'Showcasing student talents in music, dance, painting, and performing arts.',
                'venue_requested' => 'Student Center',
                'status' => 'for_revision',
            ],
            [
                'title' => 'Innovation Challenge Competition',
                'description' => 'Hackathon and innovation competition for students to present technology-based solutions to real-world problems.',
                'venue_requested' => 'IT Building - Computer Lab',
                'status' => 'for_revision',
            ],
            [
                'title' => 'Charity Fundraising Event',
                'description' => 'Fundraising event to support scholarships for underprivileged students through various activities and donations.',
                'venue_requested' => 'Multipurpose Hall',
                'status' => 'approved',
            ],
        ];

        $priorityDateOffsets = [
            'Annual Tech Summit 2024' => 2, // high priority (<= 3 days)
            'Cultural Festival Celebration' => 5, // medium priority (<= 7 days)
            'Sports Week Competition' => 10, // low priority (> 7 days)
        ];

        foreach ($ticketData as $index => $data) {
            $user = $users->random();
            $eventType = $eventTypes->random();
            $fundSource = $fundSources->random();
            $plvParticipants = rand(50, 300);
            $externalParticipants = rand(0, 100);

            $daysUntil = $priorityDateOffsets[$data['title']] ?? rand(12, 90);
            $dateFrom = Carbon::now()->addDays($daysUntil);
            $dateTo = (clone $dateFrom)->addDays(rand(0, 2));

            Ticket::create([
                'ticket_number' => 'TKT-' . date('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'user_id' => $user->user_id,
                'event_type_id' => $eventType->event_type_id,
                'title' => $data['title'],
                'description' => $data['description'],
                'proponent_contact' => '09' . rand(100000000, 999999999),
                'adviser_contact' => '09' . rand(100000000, 999999999),
                'plv_participants' => $plvParticipants,
                'external_participants' => $externalParticipants,
                'total_participants' => $plvParticipants + $externalParticipants,
                'venue_requested' => $data['venue_requested'],
                'alternate_venue' => null,
                'special_requirements' => null,
                'igp_requested' => (bool)rand(0, 1),
                'igp_details' => null,
                'oc_accommodation' => null,
                'oc_tsp' => rand(0, 1) ? 'in-house' : 'outsourced',
                'oc_driver_name' => null,
                'oc_transportation_type' => null,
                'oc_vehicle_plate_number' => null,
                'oc_driver_contact_number' => null,
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
                'time_from' => '08:00',
                'time_to' => '17:00',
                'estimated_budget' => rand(5000, 50000),
                'budget_breakdown' => 'Sample budget breakdown for ' . $data['title'],
                'additional_notes' => null,
                'fund_source_id' => $fundSource->source_id,
                'status' => $data['status'],
            ]);
        }

        $this->command->info('Created 10 realistic event tickets with various statuses');
    }
}
