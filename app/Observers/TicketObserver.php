<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Services\DashboardCacheService;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        DashboardCacheService::invalidateForTicket($ticket);
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        DashboardCacheService::invalidateForTicket($ticket);
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        DashboardCacheService::invalidateForTicket($ticket);
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        DashboardCacheService::invalidateForTicket($ticket);
    }
}
