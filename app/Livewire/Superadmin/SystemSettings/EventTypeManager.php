<?php

namespace App\Livewire\Superadmin\SystemSettings;

use App\Models\Event_Type;
use App\Models\User;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class EventTypeManager extends Component
{
    use Toast;

    // Add form data
    public $newEventTypeName = '';

    public $newEventTypeDescription = '';

    public $addEventTypeModalOpen = false;

    // Edit form data
    public $editingEventTypeId = null;

    public $eventTypeName = '';

    public $eventTypeDescription = '';

    public $editEventTypeModalOpen = false;

    // Delete data
    public $deletingEventTypeId = null;

    public $deletingEventTypeName = '';

    public $hasAssociatedEvents = false;

    public $deleteModalOpen = false;

    // Cache duration
    protected $cacheDuration = 10;

    public function render()
    {
        return view('livewire.superadmin.system-settings.event-type-manager', [
            'eventTypes' => $this->getEventTypes(),
        ]);
    }

    protected function getEventTypes()
    {
        return Cache::remember('event_types', $this->cacheDuration, function () {
            return Event_Type::orderBy('created_at', 'desc')->get();
        });
    }

    public function addEventType()
    {
        $this->validate([
            'newEventTypeName' => 'required|string|max:255|unique:event__types,type_name',
            'newEventTypeDescription' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $eventType = Event_Type::create([
                'type_name' => $this->newEventTypeName,
                'description' => $this->newEventTypeDescription ?? 'Event type description',
            ]);

            // Log the event type creation
            TransactionLogService::logEventTypeOperation('created', $eventType);

            DB::commit();

            // Send notification after commit
            $superadmins = User::where('role_id', User::getRoleId('superadmin'))->get();
            foreach ($superadmins as $admin) {
                $admin->notify(new \App\Notifications\SystemSettingsUpdatedNotification(
                    'event_type',
                    $eventType->type_name,
                    'created',
                    auth()->user()
                ));
            }

            $this->reset(['newEventTypeName', 'newEventTypeDescription']);
            $this->addEventTypeModalOpen = false;
            $this->resetErrorBag();
            $this->clearEventTypesCache();
            $this->success('Event type added successfully!', position: 'toast-top');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Event type creation failed', ['error' => $e->getMessage()]);
            $this->error('Failed to create event type: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function resetAddEventTypeForm()
    {
        $this->reset(['newEventTypeName', 'newEventTypeDescription']);
        $this->addEventTypeModalOpen = false;
        $this->resetErrorBag();
    }

    public function openEditModal($eventTypeId)
    {
        $eventType = Event_Type::find($eventTypeId);
        if ($eventType) {
            $this->editingEventTypeId = $eventTypeId;
            $this->eventTypeName = $eventType->type_name;
            $this->eventTypeDescription = $eventType->description ?? '';
            $this->editEventTypeModalOpen = true;
        }
    }

    public function editEventType()
    {
        $this->validate([
            'eventTypeName' => 'required|string|max:255|unique:event__types,type_name,' . $this->editingEventTypeId . ',event_type_id',
            'eventTypeDescription' => 'nullable|string|max:500',
        ]);

        $eventType = Event_Type::find($this->editingEventTypeId);
        if ($eventType) {
            $originalName = $eventType->type_name;
            $originalDescription = $eventType->description;
            $changes = [];

            if ($originalName !== $this->eventTypeName) {
                $changes[] = "Name: {$originalName} → {$this->eventTypeName}";
            }

            if ($originalDescription !== $this->eventTypeDescription) {
                $changes[] = "Description: {$originalDescription} → {$this->eventTypeDescription}";
            }

            $eventType->update([
                'type_name' => $this->eventTypeName,
                'description' => $this->eventTypeDescription ?? 'Event type description',
            ]);

            // Log the event type update with changes
            if (! empty($changes)) {
                TransactionLogService::logEventTypeOperation('updated', $eventType, $changes);
                $this->success('Event type updated successfully!', position: 'toast-top');
            } else {
                $this->info('Nothing Updated!', position: 'toast-top');
            }

            $this->reset(['editingEventTypeId', 'eventTypeName', 'eventTypeDescription']);
            $this->editEventTypeModalOpen = false;
            $this->resetErrorBag();
            $this->clearEventTypesCache();
        }
    }

    public function resetEventTypeForm()
    {
        $this->reset(['editingEventTypeId', 'eventTypeName', 'eventTypeDescription']);
        $this->editEventTypeModalOpen = false;
        $this->resetErrorBag();
    }

    public function openDeleteModal($eventTypeId)
    {
        $eventType = Event_Type::find($eventTypeId);
        if ($eventType) {
            $this->deletingEventTypeId = $eventTypeId;
            $this->deletingEventTypeName = $eventType->type_name;
            $this->hasAssociatedEvents = $eventType->events()->count() > 0;
            $this->deleteModalOpen = true;
        }
    }

    public function resetDeleteModal()
    {
        $this->reset(['deletingEventTypeId', 'deletingEventTypeName', 'hasAssociatedEvents']);
        $this->deleteModalOpen = false;
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

        DB::beginTransaction();
        try {
            // Log the event type deletion before deleting
            TransactionLogService::logEventTypeOperation('deleted', $eventType);

            $eventType->delete();

            DB::commit();

            $this->reset(['deletingEventTypeId', 'deletingEventTypeName', 'hasAssociatedEvents']);
            $this->deleteModalOpen = false;
            $this->clearEventTypesCache();
            $this->success('Event type deleted successfully!', position: 'toast-top');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Event type deletion failed', ['error' => $e->getMessage()]);
            $this->error('Failed to delete event type: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    protected function clearEventTypesCache()
    {
        Cache::forget('event_types');
        $this->dispatch('cache-cleared');
    }

    #[On('refresh-cache')]
    public function refreshCache()
    {
        Cache::forget('event_types');
    }
}
