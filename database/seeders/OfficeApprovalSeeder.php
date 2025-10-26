<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Office_Approval;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OfficeApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gsoOffice = Office::where('office_code', 'GSO')->first();

        if (! $gsoOffice) {
            $gsoOffice = Office::first();
        }

        if (! $gsoOffice) {
            return;
        }

        $gsoUser = User::where('role_id', User::ROLE_GSO)->whereNotNull('office_id')->first();


        if (! $gsoUser->office_id) {
            $gsoUser->office_id = $gsoOffice->office_id;
            $gsoUser->save();
        }

        $tickets = Ticket::whereIn('ticket_number', ['TKT-001', 'TKT-002', 'TKT-003', 'TKT-004', 'TKT-005'])
            ->get()
            ->keyBy('ticket_number');

        $approvals = [
            ['ticket_number' => 'TKT-001', 'decision' => 'pending', 'remarks' => 'Awaiting venue inspection.', 'updated_at' => Carbon::now()->subMinutes(15)],
            ['ticket_number' => 'TKT-002', 'decision' => 'approved', 'remarks' => 'Equipment availability confirmation pending.', 'updated_at' => Carbon::now()->subHour()],
            ['ticket_number' => 'TKT-003', 'decision' => 'rejected', 'remarks' => 'Logistics coordination in progress.', 'updated_at' => Carbon::now()->subHours(2)],
            ['ticket_number' => 'TKT-004', 'decision' => 'approved', 'remarks' => 'Approved within SLA.', 'updated_at' => Carbon::today()->addHours(9)],
            ['ticket_number' => 'TKT-005', 'decision' => 'approved', 'remarks' => 'Requirements incomplete.', 'updated_at' => Carbon::today()->addHours(10)],
        ];

        foreach ($approvals as $data) {
            $ticket = $tickets[$data['ticket_number']] ?? null;

            if (! $ticket) {
                continue;
            }

            $approval = Office_Approval::updateOrCreate(
                [
                    'ticket_id' => $ticket->ticket_id,
                    'office_id' => $gsoOffice->office_id,
                ],
                [
                    'user_id' => $gsoUser->user_id,
                    'decision' => $data['decision'],
                    'remarks' => $data['remarks'],
                ]
            );

            $approval->updated_at = $data['updated_at'];
            $approval->save();
        }
    }
}
