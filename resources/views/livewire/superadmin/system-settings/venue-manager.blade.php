<div>
    <x-mary-card shadow class="border-none bg-slate-50/50 dark:bg-base-100/50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-100">Venue Management</h2>
                <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage available venues for
                    event scheduling</p>
            </div>
            <x-mary-button icon="o-plus" label="New Venue" class="btn-primary btn-sm shadow-sm w-full sm:w-auto"
                link="{{ route('superadmin.venue.create') }}" wire:navigate />
        </div>

        @if (count($venues) > 0)
            <div class="space-y-3">
                @foreach ($venues as $venue)
                    <div
                        class="group flex items-center justify-between p-3 sm:p-4 bg-white dark:bg-base-200 border border-slate-200 dark:border-base-300 rounded-xl hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-md transition-all duration-200 gap-3">
                        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                            <div
                                class="w-10 h-10 shrink-0 flex items-center justify-center rounded-lg {{ $venue->is_active ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                                <x-mary-icon name="o-map-pin" class="w-5 h-5 sm:w-6 sm:h-6" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white line-clamp-1 text-sm sm:text-base">
                                        {{ $venue->venue_name }}
                                    </p>
                                    @if (!$venue->is_active)
                                        <span
                                            class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                                @if ($venue->venue_location)
                                    <p
                                        class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 line-clamp-1 mt-0.5">
                                        <x-mary-icon name="o-building-office-2" class="w-3 h-3 inline-block mr-1" />
                                        {{ $venue->venue_location }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div
                            class="flex gap-1 sm:gap-2 shrink-0 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                            {{-- Toggle Active Status --}}
                            <x-mary-button size="xs" :icon="$venue->is_active ? 'o-eye' : 'o-eye-slash'"
                                class="btn-ghost btn-sm {{ $venue->is_active ? 'text-emerald-500 dark:text-emerald-400 hover:text-emerald-600' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600' }}"
                                wire:click="toggleActive({{ $venue->venue_id }})" wire:loading.attr="disabled"
                                title="{{ $venue->is_active ? 'Deactivate Venue' : 'Activate Venue' }}">
                            </x-mary-button>
                            {{-- Edit --}}
                            <x-mary-button size="xs" icon="o-pencil-square"
                                class="btn-ghost btn-sm text-slate-400 dark:text-slate-500 hover:text-primary-600 dark:hover:text-primary-400"
                                link="{{ route('superadmin.venue.edit', $venue->venue_id) }}" wire:navigate
                                title="Edit Venue">
                            </x-mary-button>
                            {{-- Delete --}}
                            <x-mary-button size="xs" icon="o-trash"
                                class="btn-ghost btn-sm text-slate-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400"
                                wire:click="openDeleteModal({{ $venue->venue_id }})" wire:loading.attr="disabled"
                                title="Delete Venue">
                            </x-mary-button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="text-center py-12 bg-white dark:bg-base-200 border border-dashed border-slate-300 dark:border-base-300 rounded-xl">
                <x-mary-icon name="o-map-pin" class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-slate-600" />
                <p class="text-slate-500 dark:text-slate-400 font-medium">No venues defined</p>
                <p class="text-slate-400 dark:text-slate-500 text-sm mt-1">Add venues where events can be held.</p>
            </div>
        @endif
    </x-mary-card>

    {{-- Delete Venue Modal --}}
    @if ($deletingVenueId)
        <x-mary-modal wire:model="deleteModalOpen" title="Delete Venue" subtitle="Confirm deletion" separator
            with-close-button close-on-escape>
            <div class="space-y-4">
                <div class="alert alert-warning">
                    <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6" />
                    <span>
                        Are you sure you want to delete <strong>{{ $deletingVenueName }}</strong>?
                    </span>
                </div>

                @if ($hasAssociatedTickets)
                    <div class="alert alert-error">
                        <x-mary-icon name="o-x-circle" class="w-6 h-6" />
                        <div>
                            <p class="font-semibold">This venue cannot be deleted</p>
                            <p class="text-sm mt-1">
                                This venue is being used by <strong>{{ $associatedTicketsCount }}</strong>
                                {{ $associatedTicketsCount === 1 ? 'ticket' : 'tickets' }}.
                            </p>
                            <p class="text-sm mt-2">
                                <strong>Tip:</strong> Consider deactivating the venue instead. Deactivating will prevent
                                new event submissions using this venue while preserving existing ticket data.
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" @click="$wire.deleteModalOpen = false; $wire.resetDeleteModal()" />
                <x-mary-button label="Delete" wire:click="confirmDelete" class="btn-error" :disabled="$hasAssociatedTickets"
                    spinner="confirmDelete" />
            </x-slot:actions>
        </x-mary-modal>
    @endif
</div>
