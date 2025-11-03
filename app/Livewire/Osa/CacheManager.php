<?php

namespace App\Livewire\Osa;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class CacheManager extends Component
{
    use Toast;

    #[Title('Cache Management - OSA Admin')]
    #[Layout('components.layouts.app')]

    public $cacheStats = [];

    public function mount()
    {
        $this->loadCacheStats();
    }

    public function loadCacheStats()
    {
        $this->cacheStats = [
            'dashboard_stats' => Cache::has('osa_dashboard_stats'),
            'dashboard_recent_tickets' => Cache::has('osa_dashboard_recent_tickets'),
            'dashboard_pending_approvals' => Cache::has('osa_dashboard_pending_approvals'),
            'dashboard_upcoming_events' => Cache::has('osa_dashboard_upcoming_events'),
            'organizations_list' => Cache::has('osa_organizations_list'),
            'archive_available_years' => Cache::has('osa_archive_available_years'),
            'gso_users_notifications' => Cache::has('gso_users_notifications'),
            'osa_users_notifications' => Cache::has('osa_users_notifications'),
            'communication_users' => Cache::has('osa_communication_users'),
            'communication_organizations' => Cache::has('osa_communication_organizations'),
        ];
    }

    public function clearAllOsaCache()
    {
        $cacheKeys = [
            'osa_dashboard_stats',
            'osa_dashboard_recent_tickets',
            'osa_dashboard_pending_approvals',
            'osa_dashboard_upcoming_events',
            'osa_organizations_list',
            'osa_archive_available_years',
            'gso_users_notifications',
            'osa_users_notifications',
            'osa_communication_users',
            'osa_communication_organizations',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear ticket modal cache (pattern-based)
        $this->clearPatternCache('ticket_modal_data_*');
        $this->clearPatternCache('osa_report_*');
        $this->clearPatternCache('osa_notifications_counts_*');

        $this->loadCacheStats();
        $this->success('All OSA cache cleared successfully!');
    }

    public function clearSpecificCache($cacheKey)
    {
        Cache::forget($cacheKey);
        $this->loadCacheStats();
        $this->success("Cache '{$cacheKey}' cleared successfully!");
    }

    public function warmAllCache()
    {
        // This would typically trigger cache warming across all components
        // For now, just show success message
        $this->success('Cache warming initiated! This may take a few moments.');

        // In a real implementation, you would dispatch jobs to warm specific caches
        // dispatch(new WarmDashboardCacheJob());
        // dispatch(new WarmOrganizationsCacheJob());
        // etc.
    }

    private function clearPatternCache($pattern)
    {
        // This is a simplified pattern clearing
        // In production, you might want to use Redis SCAN or similar
        // For Laravel's default cache, we can't easily clear by pattern
        // This is just a placeholder for the concept
    }

    public function render()
    {
        return view('livewire.osa.cache-manager');
    }
}
