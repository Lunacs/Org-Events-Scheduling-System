<div class="p-4 sm:p-6 max-w-4xl mx-auto">
    {{-- Header with Breadcrumb --}}
    <section
        class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm mb-6">
        <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
        <div class="relative p-6 sm:p-8">
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

            <div class="flex items-center gap-4 relative z-10">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/20">
                    <x-ui.icon name="{{ $isEditing ? 's-pencil-square' : 's-plus' }}"
                        class="w-6 h-6 text-primary" />
                </span>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-base-content">
                        {{ $isEditing ? 'Edit Venue' : 'Add New Venue' }}
                    </h1>
                    <p class="text-sm text-base-content/70 mt-1">
                        {{ $isEditing ? 'Update the venue details below.' : 'Create a new venue for event scheduling.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Form Card --}}
    <form wire:submit.prevent="save">
        <x-ui.card shadow class="bg-white dark:bg-base-200 border-none">
            <div class="space-y-6">
                {{-- Section 1: Basic Information --}}
                <div class="pb-6 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">
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
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                This will be displayed when users select a venue for their events.
                            </p>
                        </div>

                        {{-- Venue Location --}}
                        <div>
                            <x-ui.input wire:model="venueLocation" label="Location (Optional)"
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
                            <x-ui.icon name="o-cog-6-tooth" class="w-4 h-4" />
                            Venue Status
                        </span>
                    </label>

                    <div class="bg-slate-50 dark:bg-base-300 rounded-xl p-4">
                        <x-ui.toggle wire:model="isActive" label="Active Venue"
                            hint="Only active venues can be selected for new event submissions" />

                        @if ($isEditing && !$isActive)
                            <div class="mt-4 bg-warning/10 border-l-4 border-warning p-4 rounded-r-lg">
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
