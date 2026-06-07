<?php

namespace App\Observers;

use App\Models\Office_Approval;
use App\Services\DashboardCacheService;

class OfficeApprovalObserver
{
    /**
     * Handle the Office_Approval "created" event.
     */
    public function created(Office_Approval $approval): void
    {
        DashboardCacheService::invalidateForApproval($approval);
    }

    /**
     * Handle the Office_Approval "updated" event.
     */
    public function updated(Office_Approval $approval): void
    {
        DashboardCacheService::invalidateForApproval($approval);
    }

    /**
     * Handle the Office_Approval "deleted" event.
     */
    public function deleted(Office_Approval $approval): void
    {
        DashboardCacheService::invalidateForApproval($approval);
    }
}
