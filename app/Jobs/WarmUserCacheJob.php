<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Cache\LayoutCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WarmUserCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Warm up Notification Count
        LayoutCacheService::getUnreadNotificationCount($this->user->user_id, function () {
            return $this->user->unreadNotifications()->count();
        });

        // Additional cache warming logic for specific roles can be added here
        // e.g. Navigation Cache, Dashboard Cache, etc.
    }
}
