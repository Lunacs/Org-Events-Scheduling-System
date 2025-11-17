<?php

namespace App\Livewire\Superadmin\SystemSettings;

use App\Models\Office;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast;

    #[Title('Superadmin - System Settings')]
    #[Layout('components.layouts.superadmin')]

    // Office Settings
    public $defaultOfficeId = '';

    public $crossOfficeApprovals = false;

    // Cache duration
    protected $cacheDuration = 10;

    public function render()
    {
        return view('livewire.superadmin.system-settings.index')->with([
            'offices' => $this->getOffices(),
            'settings' => $this->getSettings(),
        ]);
    }

    protected function getOffices()
    {
        return Cache::remember('offices', $this->cacheDuration, function () {
            return Office::orderBy('office_name')->get();
        });
    }

    protected function getSettings()
    {
        return Cache::remember('system_settings', $this->cacheDuration, function () {
            return [
                'default_office_id' => 1, // Default office ID
                'cross_office_approvals' => false, // Cross office approvals disabled by default
            ];
        });
    }


    public function updateSettings()
    {
        $this->validate([
            'defaultOfficeId' => 'required|exists:offices,office_id',
            'crossOfficeApprovals' => 'boolean',
        ]);

        // Update settings (in a real app, you'd store these in a settings table)
        // For now, we'll just update the cache

        $this->clearSettingsCache();
        $this->success('Settings updated successfully!', position: 'toast-top');
    }

    protected function clearSettingsCache()
    {
        Cache::forget('system_settings');
    }

    public function refreshCache()
    {
        Cache::forget('event_types');
        Cache::forget('organizations');
        Cache::forget('courses');
        Cache::forget('offices');
        Cache::forget('system_settings');

        $this->dispatch('refresh-cache');
        $this->success('Cache refreshed successfully!', position: 'toast-top');
    }
}
