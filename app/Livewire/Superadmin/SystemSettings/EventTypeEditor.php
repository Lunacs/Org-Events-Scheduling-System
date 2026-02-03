<?php

namespace App\Livewire\Superadmin\SystemSettings;

use App\Models\Event_Type;
use App\Models\User;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class EventTypeEditor extends Component
{
    use Toast;

    public $eventTypeId = null;
    public $typeName = '';
    public $description = '';
    public $documentaryRequirements = '';

    public $isEditing = false;

    #[Title('Event Type Editor')]
    #[Layout('components.layouts.superadmin')]

    public function mount($id = null)
    {
        if ($id) {
            $eventType = Event_Type::findOrFail($id);
            $this->eventTypeId = $eventType->event_type_id;
            $this->typeName = $eventType->type_name;
            $this->description = $eventType->description ?? '';
            $this->documentaryRequirements = $eventType->documentary_requirements?->toHtml() ?? '';
            $this->isEditing = true;
        }
    }

    public function render()
    {
        return view('livewire.superadmin.system-settings.event-type-editor');
    }

    public function save()
    {
        $rules = [
            'typeName' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'documentaryRequirements' => 'nullable|string',
        ];

        // Add unique validation for type name
        if ($this->isEditing) {
            $rules['typeName'] .= '|unique:event__types,type_name,' . $this->eventTypeId . ',event_type_id';
        } else {
            $rules['typeName'] .= '|unique:event__types,type_name';
        }

        $this->validate($rules, [
            'typeName.required' => 'Event type name is required.',
            'typeName.unique' => 'This event type name already exists.',
        ]);

        DB::beginTransaction();
        try {
            if ($this->isEditing) {
                $eventType = Event_Type::find($this->eventTypeId);
                $originalName = $eventType->type_name;
                $originalDescription = $eventType->description;
                $originalRequirements = $eventType->documentary_requirements?->toHtml() ?? '';
                $changes = [];

                if ($originalName !== $this->typeName) {
                    $changes[] = "Name: {$originalName} → {$this->typeName}";
                }
                if ($originalDescription !== $this->description) {
                    $changes[] = "Description updated";
                }
                if ($originalRequirements !== $this->documentaryRequirements) {
                    $changes[] = "Documentary requirements updated";
                }

                $eventType->type_name = $this->typeName;
                $eventType->description = $this->description ?: 'Event type description';
                $eventType->documentary_requirements = $this->documentaryRequirements;
                $eventType->save();

                if (!empty($changes)) {
                    TransactionLogService::logEventTypeOperation('updated', $eventType, $changes);
                }

                DB::commit();
                $this->clearCache();
                $this->success('Event type updated successfully!', position: 'toast-bottom');
            } else {
                $eventType = new Event_Type();
                $eventType->type_name = $this->typeName;
                $eventType->description = $this->description ?: 'Event type description';
                $eventType->documentary_requirements = $this->documentaryRequirements;
                $eventType->save();

                TransactionLogService::logEventTypeOperation('created', $eventType);

                DB::commit();

                // Send notification to superadmins
                $superadmins = User::where('role_id', User::getRoleId('superadmin'))->get();
                foreach ($superadmins as $admin) {
                    $admin->notify(new \App\Notifications\SystemSettingsUpdatedNotification(
                        'event_type',
                        $eventType->type_name,
                        'created',
                        auth()->user()
                    ));
                }

                $this->clearCache();
                $this->success('Event type created successfully!', position: 'toast-bottom');
            }

            return $this->redirect(route('superadmin.system-settings', ['activeTab' => 'event-types']), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Event type operation failed', ['error' => $e->getMessage()]);
            $this->error('Failed to save event type: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function cancel()
    {
        return $this->redirect(route('superadmin.system-settings', ['activeTab' => 'event-types']), navigate: true);
    }

    protected function clearCache()
    {
        Cache::forget('event_types');
    }
}
