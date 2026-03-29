<?php

use App\Models\Ticket;
use App\Services\TransactionLogService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatically mark tickets as completed after their event date has passed
// Schedule::call(function () {
//     $yesterday = now()->subDay()->endOfDay();

//     // Find all approved tickets where the event end date has passed
//     $completedTickets = Ticket::where('status', 'approved')
//         ->whereNotNull('date_to')
//         ->where('date_to', '<', $yesterday)
//         ->get();

//     $count = 0;
//     $ticketsList = [];

//     foreach ($completedTickets as $ticket) {
//         $ticket->update(['status' => 'completed']);
//         $count++;
//         $ticketsList[] = "#{$ticket->ticket_number} ({$ticket->title})";

//         // Log each ticket completion using TransactionLogService for superadmin visibility
//         TransactionLogService::logTicketOperation(
//             'completed',
//             $ticket,
//             ['Previous Status' => 'approved', 'New Status' => 'completed', 'Reason' => 'Event date passed']
//         );
//     }

//     // Log the batch operation summary
//     if ($count > 0) {
//         TransactionLogService::logSystemOperation(
//             'auto_complete_tickets',
//             "Automatically completed {$count} ticket(s) after event dates passed: ".implode(', ', $ticketsList)
//         );
//     }
// })->daily()->at('00:00')->name('complete-past-tickets')->description('Mark tickets as completed after event dates pass');
