<?php

namespace App\Livewire\Superadmin\SystemSettings;

use App\Models\Event_Type;
use App\Models\Office;
use App\Services\TransactionLogService;
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

    // Event Types
    public $newEventType = '';

    // Form data
    public $editingEventTypeId = null;

    public $eventTypeName = '';

    // Delete data
    public $deletingEventTypeId = null;

    public $deletingEventTypeName = '';

    public $hasAssociatedEvents = false;

    // Office Settings
    public $defaultOfficeId = '';

    public $crossOfficeApprovals = false;

    // Cache duration
    protected $cacheDuration = 10;

    public function render()
    {
        return view('livewire.superadmin.system-settings.index')->with([
            'eventTypes' => $this->getEventTypes(),
            'offices' => $this->getOffices(),
            'settings' => $this->getSettings(),
        ]);
    }

    protected function getEventTypes()
    {
        return Cache::remember('event_types', $this->cacheDuration, function () {
            return Event_Type::orderBy('type_name')->get();
        });
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

    public function addEventType()
    {
        $this->validate([
            'newEventType' => 'required|string|max:255|unique:event__types,type_name',
        ]);

        $eventType = Event_Type::create([
            'type_name' => $this->newEventType,
            'description' => 'Event type description',
        ]);

        // Log the event type creation
        TransactionLogService::logEventTypeOperation('created', $eventType);

        $this->newEventType = '';
        $this->clearEventTypesCache();
        $this->success('Event type added successfully!', position: 'toast-top');
    }

    public function loadEventTypeForm($eventTypeId)
    {
        $eventType = Event_Type::find($eventTypeId);
        if ($eventType) {
            $this->editingEventTypeId = $eventTypeId;
            $this->eventTypeName = $eventType->type_name;
        }
    }

    public function saveEventType()
    {
        $this->validate([
            'eventTypeName' => 'required|string|max:255|unique:event__types,type_name,'.$this->editingEventTypeId.',event_type_id',
        ]);

        $eventType = Event_Type::find($this->editingEventTypeId);
        if ($eventType) {
            $originalName = $eventType->type_name;
            $changes = [];

            if ($originalName !== $this->eventTypeName) {
                $changes[] = "Name: {$originalName} → {$this->eventTypeName}";
            }

            $eventType->update([
                'type_name' => $this->eventTypeName,
            ]);

            // Log the event type update with changes
            TransactionLogService::logEventTypeOperation('updated', $eventType, $changes);

            $this->reset(['editingEventTypeId', 'eventTypeName']);
            $this->resetErrorBag();
            $this->dispatch('event-type-form-close');
            $this->clearEventTypesCache();
            $this->success('Event type updated successfully!', position: 'toast-top');
        }
    }

    public function resetEventTypeForm()
    {
        $this->reset(['editingEventTypeId', 'eventTypeName']);
        $this->resetErrorBag();
    }

    public function loadEventTypeForDeletion($eventTypeId)
    {
        $eventType = Event_Type::find($eventTypeId);
        if ($eventType) {
            $this->deletingEventTypeId = $eventTypeId;
            $this->deletingEventTypeName = $eventType->type_name;
            $this->hasAssociatedEvents = $eventType->events()->count() > 0;
        }
    }

    public function resetDeleteModal()
    {
        $this->reset(['deletingEventTypeId', 'deletingEventTypeName', 'hasAssociatedEvents']);
    }

    public function confirmDelete()
    {
        $eventType = Event_Type::find($this->deletingEventTypeId);

        if (! $eventType) {
            $this->error('Event type not found!', position: 'toast-top');
            return;
        }

        // Check if event type is being used
        if ($eventType->events()->count() > 0) {
            $this->error('Cannot delete event type that is being used by events!', position: 'toast-top');
            return;
        }

        // Log the event type deletion before deleting
        TransactionLogService::logEventTypeOperation('deleted', $eventType);

        $eventType->delete();

        $this->reset(['deletingEventTypeId', 'deletingEventTypeName', 'hasAssociatedEvents']);
        $this->dispatch('delete-modal-close');
        $this->clearEventTypesCache();
        $this->success('Event type deleted successfully!', position: 'toast-top');
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

    protected function clearEventTypesCache()
    {
        Cache::forget('event_types');
    }

    protected function clearSettingsCache()
    {
        Cache::forget('system_settings');
    }

    public function refreshCache()
    {
        Cache::forget('event_types');
        Cache::forget('offices');
        Cache::forget('system_settings');

        $this->success('Cache refreshed successfully!', position: 'toast-top');
    }
}
