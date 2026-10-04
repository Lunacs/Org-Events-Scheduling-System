<div class="mx-auto max-w-4xl">
    {{-- Header with Breadcrumb --}}
    <x-ui.page-heading>
        <x-slot:heading>{{ $isEditing ? 'Edit Venue' : 'Add New Venue' }}</x-slot:heading>
        <x-slot:subtitle>{{ $isEditing ? 'Update the venue details below.' : 'Create a new venue for event scheduling.' }}</x-slot:subtitle>
        <x-slot:actions>
            {{-- Breadcrumb --}}
            <nav class="flex items-center text-sm text-base-content/60 mb-4 relative z-10">
                <a href="{{ route('superadmin.system-settings') }}" wire:navigate
                    class="hover:text-primary transition-colors">
                    System Settings
                </a>
                <x-ui.icon name="o-chevron-right" class="w-4 h-4 mx-2" />
                <a href="{{ route('superadmin.system-settings', ['activeTab' => 'venues']) }}" wire:navigate
                    class="hover:text-primary transition-colors">
                    Venues
                </a>
                <x-ui.icon name="o-chevron-right" class="w-4 h-4 mx-2" />
                <span class="text-base-content font-medium">
                    {{ $isEditing ? 'Edit Venue' : 'Add New Venue' }}
                </span>
            </nav>
        </x-slot:actions>
    </x-ui.page-heading>

    {{-- Main Form Card --}}
    <form wire:submit.prevent="save">
        <x-ui.card shadow class="bg-base-100 dark:bg-base-200 border-none">
            <div class="space-y-6">
                {{-- Section 1: Basic Information --}}
                <div class="pb-6 border-b border-base-300 dark:border-base-300">
                    <label class="block text-sm font-semibold text-base-content/80 mb-4">
                        <span class="flex items-center gap-2">
                            <x-ui.icon name="o-information-circle" class="w-4 h-4" />
                            Venue Information
                        </span>
                    </label>

                    <div class="grid grid-cols-1 gap-4">
                        {{-- Venue Name --}}
                        <div>
                            <x-ui.input wire:model="venueName" label="Venue Name"
                                placeholder="e.g., Main Auditorium, Conference Room A, Sports Complex" icon="o-map-pin"
                                required />
                            <p class="text-xs text-base-content/60 mt-1">
                                This will be displayed when users select a venue for their events.
                            </p>
                        </div>

                        {{-- Venue Location --}}
                        <div>
                            <x-ui.input wire:model="venueLocation" label="Location (Optional)"
                                placeholder="e.g., Building A, 2nd Floor, Room 201" icon="o-building-office-2" />
                            <p class="text-xs text-base-content/60 mt-1">
                                Provide additional location details to help event organizers find the venue.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Status --}}
                <div>
                    <label class="block text-sm font-semibold text-base-content/80 mb-3">
                        <span class="flex items-center gap-2">
                            <x-ui.icon name="o-cog-6-tooth" class="w-4 h-4" />
                            Venue Status
                        </span>
                    </label>

                    <div class="bg-base-200 dark:bg-base-300 rounded-xl p-4">
                        <x-ui.toggle wire:model="isActive" label="Active Venue"
                            hint="Only active venues can be selected for new event submissions" />

                        @if ($isEditing && !$isActive)
                            <div class="mt-4 bg-warning/10 border border-warning/30 p-4 rounded-field">
                                <div class="flex items-start gap-3">
                                    <x-ui.icon name="o-exclamation-triangle"
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
                <x-ui.button label="Cancel" wire:click="cancel" class="btn-ghost" />
                <x-ui.button type="submit" label="{{ $isEditing ? 'Update Venue' : 'Create Venue' }}"
                    class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-ui.card>
    </form>
</div>
