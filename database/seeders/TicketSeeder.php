<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $users = \App\Models\User::where('role', 'student_org')->get();
        $eventTypes = \App\Models\Event_Type::all();

        if ($users->isEmpty() || $eventTypes->isEmpty()) {
            $this->command->warn('No users or event types found. Skipping ticket seeder.');
            return;
        }

        // Create realistic tickets with different statuses
        $ticketData = [
            [
                'title' => 'Annual Tech Summit 2024',
                'description' => 'A comprehensive technology summit featuring keynote speakers, workshops, and networking sessions for IT and Computer Science students.',
                'venue-requested' => 'University Auditorium',
                'status' => 'approved',
            ],
            [
                'title' => 'Cultural Festival Celebration',
                'description' => 'Celebrating diversity through cultural performances, traditional food, and art exhibitions from different regions.',
                'venue-requested' => 'Main Gymnasium',
                'status' => 'approved',
            ],
            [
                'title' => 'Leadership Training Workshop',
                'description' => 'Interactive workshop on leadership skills, team building, and organizational management for student leaders.',
                'venue-requested' => 'Conference Hall A',
                'status' => 'pending',
            ],
            [
                'title' => 'Community Outreach Program',
                'description' => 'Outreach program to provide educational assistance and learning materials to underprivileged children in local communities.',
                'venue-requested' => 'Outdoor Field',
                'status' => 'pending',
            ],
            [
                'title' => 'Sports Week Competition',
                'description' => 'Inter-department sports competition featuring basketball, volleyball, badminton, and other athletic events.',
                'venue-requested' => 'Sports Complex',
                'status' => 'approved',
            ],
            [
                'title' => 'Environmental Awareness Campaign',
                'description' => 'Campus-wide campaign promoting environmental sustainability, waste management, and green initiatives.',
                'venue-requested' => 'College Quadrangle',
                'status' => 'pending',
            ],
            [
                'title' => 'Career Development Workshop',
                'description' => 'Workshop featuring industry professionals discussing career paths, resume building, and job interview techniques.',
                'venue-requested' => 'Conference Hall B',
                'status' => 'approved',
            ],
            [
                'title' => 'Music and Arts Festival',
                'description' => 'Showcasing student talents in music, dance, painting, and performing arts.',
                'venue-requested' => 'Student Center',
                'status' => 'rejected',
            ],
            [
                'title' => 'Innovation Challenge Competition',
                'description' => 'Hackathon and innovation competition for students to present technology-based solutions to real-world problems.',
                'venue-requested' => 'IT Building - Computer Lab',
                'status' => 'pending',
            ],
            [
                'title' => 'Charity Fundraising Event',
                'description' => 'Fundraising event to support scholarships for underprivileged students through various activities and donations.',
                'venue-requested' => 'Multipurpose Hall',
                'status' => 'approved',
            ],
        ];

        foreach ($ticketData as $index => $data) {
            $user = $users->random();
            $eventType = $eventTypes->random();
            
            \App\Models\Ticket::create([
                'ticket_number' => 'TKT-' . date('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'user_id' => $user->user_id,
                'event_type_id' => $eventType->event_type_id,
                'title' => $data['title'],
                'description' => $data['description'],
                'venue-requested' => $data['venue-requested'],
                'date-requested' => now()->addDays(rand(7, 90)),
                'status' => $data['status'],
            ]);
        }

        $this->command->info('Created 10 realistic event tickets with various statuses');
    }
}
