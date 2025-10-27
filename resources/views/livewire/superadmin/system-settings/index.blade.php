<div x-data="{ eventTypeFormOpen: false, deleteModalOpen: false }" @event-type-form-close.window="eventTypeFormOpen = false"
    @delete-modal-close.window="deleteModalOpen = false">
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold font-heading">System Settings</h1>
            <x-mary-button icon="o-arrow-path" class="btn-outline" wire:click="refreshCache">
                Refresh Cache
            </x-mary-button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-mary-card title="Event Types">
                <form wire:submit="addEventType">
                    <div class="flex gap-2 mb-4">
                        <x-mary-input wire:model="newEventType" placeholder="New event type" class="w-full" />
                        <x-mary-button icon="o-plus" class="btn-accent" type="submit">Add</x-button>
                    </div>
                </form>

                @if (count($eventTypes) > 0)
                    <ul class="space-y-2">
                        @foreach ($eventTypes as $eventType)
                            <li class="flex items-center justify-between p-2 border rounded-lg">
                                <x-mary-list-item :item="['title' => $eventType->type_name]" value="title" icon="o-tag" />
                                <div class="flex gap-1">
                                    <x-mary-button size="xs" icon="o-pencil-square" class="btn-ghost"
                                        @click="$wire.loadEventTypeForm({{ $eventType->event_type_id }}).then(() => { eventTypeFormOpen = true })"
                                        wire:loading.attr="disabled">
                                    </x-mary-button>
                                    <x-mary-button size="xs" icon="o-trash" class="btn-ghost text-red-600"
                                        @click="$wire.loadEventTypeForDeletion({{ $eventType->event_type_id }}).then(() => { deleteModalOpen = true })"
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
                </x-card>

                <x-mary-card title="Office Settings">
                    <form wire:submit="updateSettings">
                        <div class="space-y-4">
                            <x-mary-select wire:model="defaultOfficeId" :options="$offices
                                ->map(function ($office) {
                                    return ['id' => $office->office_id, 'name' => $office->office_name];
                                })
                                ->toArray()" option-value="id"
                                option-label="name" label="Default Office" placeholder="Select default office" />

                            <x-mary-toggle wire:model="crossOfficeApprovals" label="Enable cross-office approvals" />

                            <x-mary-button type="submit" class="btn-primary w-full">
                                Update Settings
                                </x-button>
                        </div>
                    </form>
                    </x-card>

                    <x-mary-card title="Approval Workflows" class="lg:col-span-2">
                        <div class="space-y-4">
                            <x-mary-timeline-item title="Student Organization" subtitle="Submit Event Request"
                                icon="o-paper-airplane" first />
                            <x-mary-timeline-item title="OSA Review" subtitle="Initial Review & Approval"
                                icon="o-eye" />
                            <x-mary-timeline-item title="GSO Finalization" subtitle="Final Approval & Scheduling"
                                icon="o-check-badge" last />

                            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center">
                                    <x-mary-icon name="o-information-circle" class="w-5 h-5 text-blue-600 mr-2" />
                                    <p class="text-blue-800 text-sm">
                                        <strong>Note:</strong> This workflow can be customized based on your
                                        organization's
                                        requirements.
                                        Contact your system administrator for workflow modifications.
                                    </p>
                                </div>
                            </div>
                        </div>
                        </x-card>
        </div>
    </div>

    {{-- Event Type Form Drawer --}}
    <x-mary-drawer x-show="eventTypeFormOpen" title="Edit Event Type" subtitle="Update event type information" separator
        with-close-button close-on-escape class="w-11/12 lg:w-1/3" right>

        <form wire:submit="saveEventType" class="space-y-4">
            <x-mary-input wire:model="eventTypeName" label="Event Type Name" placeholder="Enter event type name"
                icon="o-tag" />
        </form>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="eventTypeFormOpen = false; $wire.call('resetEventTypeForm')" />
            <x-mary-button label="Update" wire:click="saveEventType" class="btn-primary" spinner="saveEventType"
                wire:loading.attr="disabled" />
        </x-slot:actions>
    </x-mary-drawer>

    {{-- Delete Confirmation Modal --}}
    @if ($deletingEventTypeName)
        <x-mary-modal x-model="deleteModalOpen" title="Delete Event Type Confirmation"
            subtitle="This action cannot be undone">
            <div class="space-y-4">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                        <p class="text-red-800 font-medium">Warning: This action is permanent</p>
                    </div>
                </div>

                <p class="text-gray-700">
                    Are you sure you want to delete the event type
                    <strong class="text-gray-900">{{ $deletingEventTypeName }}</strong>?
                    <br><br>
                    This will permanently remove the event type from the system.
                </p>

                @if ($hasAssociatedEvents)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-yellow-800 text-sm">
                            <strong>Cannot delete:</strong> This event type is being used by existing events.
                        </p>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" @click="deleteModalOpen = false; $wire.call('resetDeleteModal')" />
                <x-mary-button label="Delete Event Type" wire:click="confirmDelete" class="btn-error"
                    spinner="confirmDelete" :disabled="$hasAssociatedEvents" wire:loading.attr="disabled" />
            </x-slot:actions>
        </x-mary-modal>
    @endif
</div>
