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
    private const LAST_RUN_CACHE_KEY = 'auto_complete_tickets_last_run';

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
        if (Cache::has(self::LAST_RUN_CACHE_KEY)) {
            return;
        }

        Cache::put(self::LAST_RUN_CACHE_KEY, now()->toDateTimeString(), now()->addHours(23));
        $this->autoCompleteTickets();
    }

    /**
     * Find and mark approved tickets as completed 1 week after the event ends.
     * This grace period allows student orgs to request rescheduling.
     */
    public function autoCompleteTickets(): void
    {
        try {
            $oneWeekAgo = now()->subWeek()->endOfDay();

            // Find all approved tickets where the event ended more than 1 week ago
            $completedTickets = Ticket::where('status', 'approved')
                ->where(function ($query) use ($oneWeekAgo) {
                    // Tickets where date_to is more than 1 week ago
                    $query->where(function ($q) use ($oneWeekAgo) {
                        $q->whereNotNull('date_to')
                            ->where('date_to', '<', $oneWeekAgo);
                    })
                        // Fallback: tickets with no date_to, using date_from + 1 week
                        ->orWhere(function ($q) use ($oneWeekAgo) {
                            $q->whereNull('date_to')
                                ->whereNotNull('date_from')
                                ->where('date_from', '<', $oneWeekAgo);
                        });
                })
                ->get();

            if ($completedTickets->isEmpty()) {
                return;
            }

            $count = 0;
            $ticketsList = [];

            foreach ($completedTickets as $ticket) {
                $reason = $this->getCompletionReason($ticket, $oneWeekAgo);

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

    private function getCompletionReason(Ticket $ticket, \Carbon\CarbonInterface $oneWeekAgo): string
    {
        if ($ticket->date_to && $ticket->date_to < $oneWeekAgo) {
            return 'Event ended over 1 week ago';
        }

        return 'Event start date passed over 1 week ago (no end date set)';
    }
}
