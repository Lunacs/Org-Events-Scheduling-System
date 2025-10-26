<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OSAApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tickets and OSA users
        $tickets = \App\Models\Ticket::all();
        $osaUsers = \App\Models\User::where('role_id', \App\Models\User::ROLE_OSA)->get();

        if ($tickets->isEmpty() || $osaUsers->isEmpty()) {
            $this->command->warn('No tickets or OSA users found. Skipping OSA approval seeder.');
            return;
        }

        $approvalData = [
            'approved' => 'Approved. All requirements are met.',
            'rejected' => 'Rejected due to incomplete documentation.',
            'pending' => 'Pending review by the committee.',
            'need_revision' => 'Please revise the proposal and resubmit.',
        ];

        foreach ($tickets as $ticket) {
            // Create OSA approval based on ticket status
            $decision = match($ticket->status) {
                'approved' => 'approved',
                'rejected' => 'rejected',
                'pending' => 'pending',
                default => 'pending',
            };

            \App\Models\OSA_Approval::create([
                'ticket_id' => $ticket->ticket_id,
                'user_id' => $osaUsers->random()->user_id,
                'decision' => $decision,
                'remarks' => $approvalData[$decision] ?? 'Under review.',
            ]);
        }

        $this->command->info('Created OSA approvals for ' . $tickets->count() . ' tickets');
    }
}
