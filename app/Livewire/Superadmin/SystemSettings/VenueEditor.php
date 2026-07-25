<?php

namespace App\Livewire\Superadmin\SystemSettings;

use App\Models\User;
use App\Models\Venue;
use App\Notifications\SystemSettingsUpdatedNotification;
use App\Services\TransactionLogService;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class VenueEditor extends Component
{
    use Toast;

    public $venueId = null;

    public $venueName = '';

    public $venueLocation = '';

    public $isActive = true;

    public $isEditing = false;

    #[Title('Venue Editor')]
    #[Layout('components.layouts.superadmin')]
    public function mount($id = null)
    {
        if ($id) {
            $venue = Venue::findOrFail($id);
            $this->venueId = $venue->venue_id;
            $this->venueName = $venue->venue_name;
            $this->venueLocation = $venue->venue_location ?? '';
            $this->isActive = (bool) $venue->is_active;
            $this->isEditing = true;
        }
    }

    public function render()
    {
        return view('livewire.superadmin.system-settings.venue-editor');
    }

    public function save()
    {
        $rules = [
            'venueName' => 'required|string|max:255',
            'venueLocation' => 'nullable|string|max:500',
            'isActive' => 'boolean',
        ];

        // Add unique validation for venue name
        if ($this->isEditing) {
            $rules['venueName'] .= '|unique:venues,venue_name,'.$this->venueId.',venue_id';
        } else {
            $rules['venueName'] .= '|unique:venues,venue_name';
        }

        $this->validate($rules, [
            'venueName.required' => 'Venue name is required.',
            'venueName.unique' => 'This venue name already exists.',
            'venueLocation.max' => 'Location must not exceed 500 characters.',
        ]);

        DB::beginTransaction();
        try {
            if ($this->isEditing) {
                $venue = Venue::find($this->venueId);
                $originalName = $venue->venue_name;
                $originalLocation = $venue->venue_location;
                $originalStatus = $venue->is_active;
                $changes = [];

                if ($originalName !== $this->venueName) {
                    $changes[] = "Name: {$originalName} → {$this->venueName}";
                }
                if ($originalLocation !== $this->venueLocation) {
                    $oldLocation = $originalLocation ?: '(empty)';
                    $newLocation = $this->venueLocation ?: '(empty)';
                    $changes[] = "Location: {$oldLocation} → {$newLocation}";
                }
                if ((bool) $originalStatus !== $this->isActive) {
                    $oldStatus = $originalStatus ? 'Active' : 'Inactive';
                    $newStatus = $this->isActive ? 'Active' : 'Inactive';
                    $changes[] = "Status: {$oldStatus} → {$newStatus}";
                }

                $venue->venue_name = $this->venueName;
                $venue->venue_location = $this->venueLocation ?: null;
                $venue->is_active = $this->isActive;
                $venue->save();

                if (! empty($changes)) {
                    TransactionLogService::logVenueOperation('updated', $venue, $changes);
                }

                DB::commit();
                $this->clearCache();
                $this->success('Venue updated successfully!', position: 'toast-bottom');
            } else {
                $venue = new Venue;
                $venue->venue_name = $this->venueName;
                $venue->venue_location = $this->venueLocation ?: null;
                $venue->is_active = $this->isActive;
                $venue->save();

                TransactionLogService::logVenueOperation('created', $venue);

                DB::commit();

                // Send notification to superadmins
                $superadmins = User::where('role_id', User::getRoleId('superadmin'))->get();
                foreach ($superadmins as $admin) {
                    $admin->notify(new SystemSettingsUpdatedNotification(
                        'venue',
                        $venue->venue_name,
                        'created',
                        auth()->user()
                    ));
                }

                $this->clearCache();
                $this->success('Venue created successfully!', position: 'toast-bottom');
            }

            return $this->redirect(route('superadmin.system-settings', ['activeTab' => 'venues']), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Venue operation failed', ['error' => $e->getMessage()]);
            $this->error('Failed to save venue: '.$e->getMessage(), position: 'toast-bottom');
        }
    }

    public function cancel()
    {
        return $this->redirect(route('superadmin.system-settings', ['activeTab' => 'venues']), navigate: true);
    }

    protected function clearCache()
    {
        Cache::forget('venues');
    }
}
