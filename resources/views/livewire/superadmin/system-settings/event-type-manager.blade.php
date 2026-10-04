<div>
    <x-ui.card shadow class="border-none bg-base-200/50 dark:bg-base-100/50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-base-content">Event Classifications</h2>
                <p class="text-[10px] sm:text-xs text-base-content/60 mt-0.5">Categorize and label system
                    events</p>
            </div>
            <x-ui.button icon="o-plus" label="New Classification" class="btn-primary btn-sm shadow-sm w-full sm:w-auto"
                link="{{ route('superadmin.event-type.create') }}" wire:navigate />
        </div>

        @if (count($eventTypes) > 0)
            <div class="space-y-3">
                @foreach ($eventTypes as $eventType)
                    <div
                        class="group flex items-center justify-between p-3 sm:p-4 bg-base-100 dark:bg-base-200 border border-base-300 dark:border-base-300 rounded-xl hover:border-primary/40  transition duration-200 gap-3">
                        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                            <div
                                class="w-10 h-10 shrink-0 flex items-center justify-center rounded-lg bg-primary/10  text-primary ">
                                <x-ui.icon name="o-tag" class="w-5 h-5 sm:w-6 sm:h-6" />
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="font-semibold text-base-content line-clamp-1 text-sm sm:text-base">
                                    {{ $eventType->type_name }}</p>
                                @if ($eventType->description)
                                    <p
                                        class="text-[10px] sm:text-xs text-base-content/60 line-clamp-1 mt-0.5">
                                        {{ $eventType->description }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div
                            class="flex gap-1 sm:gap-2 shrink-0 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                            <x-ui.button size="xs" icon="o-pencil-square"
                                class="btn-ghost btn-sm text-base-content/60 hover:text-primary-600 "
                                link="{{ route('superadmin.event-type.edit', $eventType->event_type_id) }}"
                                wire:navigate title="Edit Event Type">
                            </x-ui.button>
                            <x-ui.button size="xs" icon="o-trash"
                                class="btn-ghost btn-sm text-base-content/60 hover:text-error "
                                wire:click="openDeleteModal({{ $eventType->event_type_id }})"
                                wire:loading.attr="disabled" title="Delete Event Type">
                            </x-ui.button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="text-center py-12 bg-base-100 dark:bg-base-200 border border-dashed border-base-300 dark:border-base-300 rounded-xl">
                <x-ui.icon name="o-tag" class="w-12 h-12 mx-auto mb-3 text-base-content/40" />
                <p class="text-base-content/60 font-medium">No event types defined</p>
                <p class="text-base-content/60 text-sm mt-1">Define event categories to help structure
                    your scheduling.</p>
            </div>
        @endif
    </x-ui.card>

    {{-- Delete Event Type Modal --}}
    @if ($deletingEventTypeId)
        <x-ui.modal-dialog wire:model="deleteModalOpen" title="Delete Event Type" subtitle="Confirm deletion" separator
            with-close-button close-on-escape>
            <div class="space-y-4">
                <div class="alert alert-warning">
                    <x-ui.icon name="o-exclamation-triangle" class="w-6 h-6" />
                    <span>
                        Are you sure you want to delete <strong>{{ $deletingEventTypeName }}</strong>?
                    </span>
                </div>

                @if ($hasAssociatedEvents)
                    <div class="alert alert-error">
                        <x-ui.icon name="o-x-circle" class="w-6 h-6" />
                        <span>
                            This event type cannot be deleted because it is being used by existing events.
                        </span>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-ui.button label="Cancel" @click="$wire.deleteModalOpen = false; $wire.resetDeleteModal()" />
                <x-ui.button label="Delete" wire:click="confirmDelete" class="btn-error" :disabled="$hasAssociatedEvents"
                    spinner="confirmDelete" />
            </x-slot:actions>
        </x-ui.modal-dialog>
    @endif
</div>
