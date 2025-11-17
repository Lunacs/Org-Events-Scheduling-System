<div>
    <x-mary-card progressIndicator shadow>
        <div class="flex justify-between mb-5">
            <h2 class="font-bold text-xl">Event Types</h2>
            <x-mary-button icon="o-plus" class="btn-accent"
                wire:click="$set('addEventTypeModalOpen', true)">Add</x-mary-button>
        </div>

        @if (count($eventTypes) > 0)
            <ul class="space-y-2">
                @foreach ($eventTypes as $eventType)
                    <li class="flex items-center justify-between p-2 border rounded-lg">
                        <p>{{ $eventType->type_name }}</p>
                        <div class="flex gap-1">
                            <x-mary-button size="xs" icon="o-pencil-square" class="btn-ghost"
                                wire:click="openEditModal({{ $eventType->event_type_id }})"
                                wire:loading.attr="disabled">
                            </x-mary-button>
                            <x-mary-button size="xs" icon="o-trash" class="btn-ghost text-red-600"
                                wire:click="openDeleteModal({{ $eventType->event_type_id }})"
                                wire:loading.attr="disabled">
                            </x-mary-button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-center py-4 text-gray-500">
                <x-mary-icon name="o-tag" class="w-8 h-8 mx-auto mb-2" />
                <p>No event types found</p>
            </div>
        @endif
    </x-mary-card>

    {{-- Add Event Type Modal --}}
    <x-mary-modal wire:model="addEventTypeModalOpen" title="Add Event Type" subtitle="Create a new event type"
        separator with-close-button close-on-escape>
        <form wire:submit.prevent="addEventType" class="space-y-4">
            <x-mary-input wire:model="newEventTypeName" label="Event Type Name" placeholder="e.g., Workshop"
                icon="o-tag" />
            <x-mary-textarea wire:model="newEventTypeDescription" label="Description (Optional)" rows="3"
                placeholder="Brief description of the event type" />
        </form>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.addEventTypeModalOpen = false; $wire.resetAddEventTypeForm()" />
            <x-mary-button label="Create Event Type" wire:click="addEventType" class="btn-primary"
                spinner="addEventType" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Edit Event Type Modal --}}
    @if ($editingEventTypeId)
        <x-mary-modal wire:model="editEventTypeModalOpen" title="Edit Event Type"
            subtitle="Update event type information" separator with-close-button close-on-escape>
            <form wire:submit.prevent="editEventType" class="space-y-4">
                <x-mary-input wire:model="eventTypeName" label="Event Type Name" placeholder="e.g., Workshop"
                    icon="o-tag" />
                <x-mary-textarea wire:model="eventTypeDescription" label="Description (Optional)" rows="3"
                    placeholder="Brief description of the event type" />
            </form>

            <x-slot:actions>
                <x-mary-button label="Cancel"
                    @click="$wire.editEventTypeModalOpen = false; $wire.resetEventTypeForm()" />
                <x-mary-button label="Update Event Type" wire:click="editEventType" class="btn-primary"
                    spinner="editEventType" />
            </x-slot:actions>
        </x-mary-modal>
    @endif

    {{-- Delete Event Type Modal --}}
    @if ($deletingEventTypeId)
        <x-mary-modal wire:model="deleteModalOpen" title="Delete Event Type" subtitle="Confirm deletion"
            separator with-close-button close-on-escape>
            <div class="space-y-4">
                <div class="alert alert-warning">
                    <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6" />
                    <span>
                        Are you sure you want to delete <strong>{{ $deletingEventTypeName }}</strong>?
                    </span>
                </div>

                @if ($hasAssociatedEvents)
                    <div class="alert alert-error">
                        <x-mary-icon name="o-x-circle" class="w-6 h-6" />
                        <span>
                            This event type cannot be deleted because it is being used by existing events.
                        </span>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" @click="$wire.deleteModalOpen = false; $wire.resetDeleteModal()" />
                <x-mary-button label="Delete" wire:click="confirmDelete" class="btn-error"
                    :disabled="$hasAssociatedEvents" spinner="confirmDelete" />
            </x-slot:actions>
        </x-mary-modal>
    @endif
</div>

