<div class="p-4 sm:p-6 max-w-4xl mx-auto">
    {{-- Header with Breadcrumb --}}
    <div class="mb-6">
        {{-- Breadcrumb --}}
        <nav class="flex items-center text-sm text-slate-500 dark:text-slate-400 mb-4">
            <a href="{{ route('superadmin.system-settings') }}" wire:navigate
                class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                System Settings
            </a>
            <x-mary-icon name="o-chevron-right" class="w-4 h-4 mx-2" />
            <a href="{{ route('superadmin.system-settings', ['activeTab' => 'venues']) }}" wire:navigate
                class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                Venues
            </a>
            <x-mary-icon name="o-chevron-right" class="w-4 h-4 mx-2" />
            <span class="text-slate-700 dark:text-slate-200">
                {{ $isEditing ? 'Edit Venue' : 'Add New Venue' }}
            </span>
        </nav>

        {{-- Page Title --}}
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30">
                <x-mary-icon name="{{ $isEditing ? 'o-pencil-square' : 'o-plus' }}"
                    class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    {{ $isEditing ? 'Edit Venue' : 'Add New Venue' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ $isEditing ? 'Update the venue details below.' : 'Create a new venue for event scheduling.' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Main Form Card --}}
    <form wire:submit.prevent="save">
        <x-mary-card shadow class="bg-white dark:bg-base-200 border-none">
            <div class="space-y-6">
                {{-- Section 1: Basic Information --}}
                <div class="pb-6 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-information-circle" class="w-4 h-4" />
                            Venue Information
                        </span>
                    </label>

                    <div class="grid grid-cols-1 gap-4">
                        {{-- Venue Name --}}
                        <div>
                            <x-mary-input wire:model="venueName" label="Venue Name"
                                placeholder="e.g., Main Auditorium, Conference Room A, Sports Complex" icon="o-map-pin"
                                required />
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                This will be displayed when users select a venue for their events.
                            </p>
                        </div>

                        {{-- Venue Location --}}
                        <div>
                            <x-mary-input wire:model="venueLocation" label="Location (Optional)"
                                placeholder="e.g., Building A, 2nd Floor, Room 201" icon="o-building-office-2" />
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                Provide additional location details to help event organizers find the venue.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Status --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-cog-6-tooth" class="w-4 h-4" />
                            Venue Status
                        </span>
                    </label>

                    <div class="bg-slate-50 dark:bg-base-300 rounded-xl p-4">
                        <x-mary-toggle wire:model="isActive" label="Active Venue"
                            hint="Only active venues can be selected for new event submissions" />

                        @if ($isEditing && !$isActive)
                            <div class="mt-4 bg-warning/10 border-l-4 border-warning p-4 rounded-r-lg">
                                <div class="flex items-start gap-3">
                                    <x-mary-icon name="o-exclamation-triangle"
                                        class="w-5 h-5 text-warning shrink-0 mt-0.5" />
                                    <div>
                                        <p class="font-medium text-sm text-base-content">Deactivation Notice</p>
                                        <p class="text-xs text-base-content/70 mt-1">
                                            Deactivating this venue will only prevent it from being selected for new
                                            event submissions. Existing tickets that reference this venue will not be
                                            affected.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <x-slot:actions>
                <x-mary-button label="Cancel" wire:click="cancel" class="btn-ghost" />
                <x-mary-button type="submit" label="{{ $isEditing ? 'Update Venue' : 'Create Venue' }}"
                    class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-mary-card>
    </form>
</div>
