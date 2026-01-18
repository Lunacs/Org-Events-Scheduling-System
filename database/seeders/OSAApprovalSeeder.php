<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OSAApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tickets and OSA users
        $tickets = Ticket::all();
        $osaUsers = User::where('role_id', User::getRoleId('osa'))->get();

        if ($tickets->isEmpty() || $osaUsers->isEmpty()) {
            $this->command->warn('No tickets or OSA users found. Skipping OSA approval seeder.');
            return;
        }

        // Updated approval data to include all decision types from the extended enum
        $approvalData = [
            'approved' => 'Approved. All requirements are met.',
            'pending' => 'Pending review by the committee.',
            'for_revision' => 'Please revise the proposal and resubmit.',
            'forwarded' => 'Forwarded to GSO for venue and facilities review.',
            'revision_requested' => 'Revision requested. Please update the event details.',
        ];

        foreach ($tickets as $ticket) {
            // Match decision to ticket status for realistic data
            $decision = match ($ticket->status) {
                'approved' => 'approved',
                'for_revision' => 'for_revision',
                'gso_review' => 'forwarded',
                'amended' => 'revision_requested',
                'received' => 'pending',
                'pending_osa_approval' => 'pending',
                'completed' => 'approved',
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
