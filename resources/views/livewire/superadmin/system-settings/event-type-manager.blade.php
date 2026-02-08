<div>
    <x-mary-card shadow class="border-none bg-slate-50/50 dark:bg-base-100/50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-100">Event Classifications</h2>
                <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">Categorize and label system
                    events</p>
            </div>
            <x-mary-button icon="o-plus" label="New Classification" class="btn-primary btn-sm shadow-sm w-full sm:w-auto"
                link="{{ route('superadmin.event-type.create') }}" wire:navigate />
        </div>

        @if (count($eventTypes) > 0)
            <div class="space-y-3">
                @foreach ($eventTypes as $eventType)
                    <div
                        class="group flex items-center justify-between p-3 sm:p-4 bg-white dark:bg-base-200 border border-slate-200 dark:border-base-300 rounded-xl hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-md transition-all duration-200 gap-3">
                        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                            <div
                                class="w-10 h-10 shrink-0 flex items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                                <x-mary-icon name="o-tag" class="w-5 h-5 sm:w-6 sm:h-6" />
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="font-semibold text-slate-900 dark:text-white line-clamp-1 text-sm sm:text-base">
                                    {{ $eventType->type_name }}</p>
                                @if ($eventType->description)
                                    <p
                                        class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 line-clamp-1 mt-0.5">
                                        {{ $eventType->description }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div
                            class="flex gap-1 sm:gap-2 shrink-0 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                            <x-mary-button size="xs" icon="o-pencil-square"
                                class="btn-ghost btn-sm text-slate-400 dark:text-slate-500 hover:text-primary-600 dark:hover:text-primary-400"
                                link="{{ route('superadmin.event-type.edit', $eventType->event_type_id) }}"
                                wire:navigate title="Edit Event Type">
                            </x-mary-button>
                            <x-mary-button size="xs" icon="o-trash"
                                class="btn-ghost btn-sm text-slate-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400"
                                wire:click="openDeleteModal({{ $eventType->event_type_id }})"
                                wire:loading.attr="disabled" title="Delete Event Type">
                            </x-mary-button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="text-center py-12 bg-white dark:bg-base-200 border border-dashed border-slate-300 dark:border-base-300 rounded-xl">
                <x-mary-icon name="o-tag" class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-slate-600" />
                <p class="text-slate-500 dark:text-slate-400 font-medium">No event types defined</p>
                <p class="text-slate-400 dark:text-slate-500 text-sm mt-1">Define event categories to help structure
                    your scheduling.</p>
            </div>
        @endif
    </x-mary-card>

    {{-- Delete Event Type Modal --}}
    @if ($deletingEventTypeId)
        <x-mary-modal wire:model="deleteModalOpen" title="Delete Event Type" subtitle="Confirm deletion" separator
            with-close-button close-on-escape>
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
                <x-mary-button label="Delete" wire:click="confirmDelete" class="btn-error" :disabled="$hasAssociatedEvents"
                    spinner="confirmDelete" />
            </x-slot:actions>
        </x-mary-modal>
    @endif
</div>
