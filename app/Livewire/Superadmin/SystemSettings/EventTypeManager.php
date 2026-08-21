<?php

namespace App\Livewire\Superadmin\SystemSettings;

use App\Models\Event_Type;
use App\Services\TransactionLogService;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class EventTypeManager extends Component
{
    use Toast;

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
            $this->error('Failed to delete event type: '.$e->getMessage(), position: 'toast-top');
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
