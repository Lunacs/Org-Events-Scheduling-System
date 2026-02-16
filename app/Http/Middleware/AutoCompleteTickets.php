<?php

namespace App\Http\Middleware;

use App\Models\Ticket;
use App\Services\TransactionLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AutoCompleteTickets
{
    /**
     * Handle an incoming request.
     * Just passes the request through — the heavy work happens in terminate().
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Runs AFTER the response has been sent to the browser.
     * Users feel zero delay.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Only run once per day (cache lock expires after 23 hours)
        if (Cache::has('auto_complete_tickets_last_run')) {
            return;
        }

        Cache::put('auto_complete_tickets_last_run', now()->toDateTimeString(), now()->addHours(23));
        $this->autoCompleteTickets();
    }

    /**
     * Find and mark approved tickets as completed when their event date has passed.
     */
    protected function autoCompleteTickets(): void
    {
        try {
            $yesterday = now()->subDay()->endOfDay();

            // Find all approved tickets where the event end date has passed
            $completedTickets = Ticket::where('status', 'approved')
                ->where(function ($query) use ($yesterday) {
                    // Tickets where date_to has passed
                    $query->where(function ($q) use ($yesterday) {
                        $q->whereNotNull('date_to')
                            ->where('date_to', '<', $yesterday);
                    })
                        // OR tickets approved for over 1 week (no reschedule)
                        ->orWhere(function ($q) {
                            $q->where('updated_at', '<', now()->subWeek());
                        });
                })
                ->get();

            if ($completedTickets->isEmpty()) {
                return;
            }

            $count = 0;
            $ticketsList = [];

            foreach ($completedTickets as $ticket) {
                $reason = $ticket->date_to && $ticket->date_to < $yesterday
                    ? 'Event date passed'
                    : 'Approved for over 1 week without rescheduling';

                $ticket->update(['status' => 'completed']);
                $count++;
                $ticketsList[] = "#{$ticket->ticket_number} ({$ticket->title})";

                TransactionLogService::logTicketOperation(
                    'completed',
                    $ticket,
                    [
                        'Previous Status' => 'approved',
                        'New Status' => 'completed',
                        'Reason' => $reason,
                    ]
                );
            }

            if ($count > 0) {
                TransactionLogService::logSystemOperation(
                    'auto_complete_tickets',
                    "Automatically completed {$count} ticket(s): " . implode(', ', $ticketsList)
                );
            }

            Log::info("AutoCompleteTickets: Completed {$count} ticket(s).");
        } catch (\Throwable $e) {
            Log::error('AutoCompleteTickets middleware error: ' . $e->getMessage());
        }
    }
}
