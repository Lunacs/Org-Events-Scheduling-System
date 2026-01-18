<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::where('email', 'gso@plv.edu.ph')->first();

        if (! $user) {
            return;
        }

        $logs = [
            [
                'action' => 'Approved venue booking for Annual Conference',
                'details' => 'Approval processed via dashboard seeder.',
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'action' => 'For Revision equipment request for Workshop Series',
                'details' => 'Equipment limitations noted during review.',
                'created_at' => Carbon::now()->subHours(4),
            ],
            [
                'action' => 'Sent feedback to OSA regarding Sports Tournament',
                'details' => 'Follow-up sent through communication module.',
                'created_at' => Carbon::now()->subDay(),
            ],
        ];

        foreach ($logs as $log) {
            $record = \App\Models\Transaction_Logs::updateOrCreate(
                [
                    'user_id' => $user->user_id,
                    'action' => $log['action'],
                ],
                [
                    'details' => $log['details'],
                ]
            );

            $record->created_at = $log['created_at'];
            $record->updated_at = $log['created_at'];
            $record->save();
        }
    }
}
