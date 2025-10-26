<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfficeApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get approved tickets and offices
        $approvedTickets = \App\Models\Ticket::where('status', 'approved')->get();
        $offices = \App\Models\Office::all();
        $gsoUsers = \App\Models\User::where('role', 'gso')->get();

        if ($approvedTickets->isEmpty() || $offices->isEmpty()) {
            $this->command->warn('No approved tickets or offices found. Skipping office approval seeder.');
            return;
        }

        $approvalRemarks = [
            'approved' => [
                'Venue is available and reserved for the requested date.',
                'Security arrangements have been coordinated.',
                'Budget allocation approved.',
                'All technical requirements noted and approved.',
            ],
            'pending' => [
                'Please submit a detailed floor plan for the event setup.',
                'Pending review and coordination.',
                'Awaiting final confirmation.',
            ],
        ];

        foreach ($approvedTickets as $ticket) {
            // Create 2-3 office approvals per approved ticket (different offices)
            $numberOfApprovals = rand(2, 3);
            $selectedOffices = $offices->random(min($numberOfApprovals, $offices->count()));

            foreach ($selectedOffices as $office) {
                $decision = fake()->randomElement(['approved', 'approved', 'pending']); // 66% approved
                $remarks = fake()->randomElement($approvalRemarks[$decision]);

                \App\Models\Office_Approval::create([
                    'ticket_id' => $ticket->ticket_id,
                    'office_id' => $office->office_id,
                    'user_id' => $gsoUsers->isNotEmpty() ? $gsoUsers->random()->user_id : null,
                    'decision' => $decision,
                    'remarks' => $remarks,
                ]);
            }
        }

        $this->command->info('Created office approvals for approved tickets');
    }
}
