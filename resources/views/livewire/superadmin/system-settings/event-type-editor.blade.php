@push('head')
    @vite('resources/js/libs/trix.js')
@endpush

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
                <x-mary-icon name="o-chevron-right" class="w-4 h-4 mx-2" />
                <a href="{{ route('superadmin.system-settings', ['activeTab' => 'event-types']) }}" wire:navigate
                    class="hover:text-primary transition-colors">
                    Event Types
                </a>
                <x-mary-icon name="o-chevron-right" class="w-4 h-4 mx-2" />
                <span class="text-base-content font-medium">
                    {{ $isEditing ? 'Edit Event Type' : 'Add New Event Type' }}
                </span>
            </nav>

            <div class="flex items-center gap-4 relative z-10">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/20">
                    <x-mary-icon name="{{ $isEditing ? 's-pencil-square' : 's-plus' }}"
                        class="w-6 h-6 text-primary" />
                </span>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-base-content">
                        {{ $isEditing ? 'Edit Event Type' : 'Add New Event Type' }}
                    </h1>
                    <p class="text-sm text-base-content/70 mt-1">
                        {{ $isEditing ? 'Update the event type details below.' : 'Create a new event type for event categorization.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Form Card --}}
    <form wire:submit.prevent="save">
        <x-mary-card shadow class="bg-white dark:bg-base-200 border-none">
            <div class="space-y-6">
                {{-- Section 1: Basic Information --}}
                <div class="pb-6 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-information-circle" class="w-4 h-4" />
                            Basic Information
                        </span>
                    </label>

                    <div class="grid grid-cols-1 gap-4">
                        {{-- Type Name --}}
                        <div>
                            <x-mary-input wire:model="typeName" label="Event Type Name"
                                placeholder="e.g., Workshop, Seminar, Competition" icon="o-tag" required />
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                This will be used to categorize events in the system.
                            </p>
                        </div>

                        {{-- Description --}}
                        <div>
                            <x-mary-textarea wire:model="description" label="Description (Optional)"
                                placeholder="Brief description of this event type and when it should be used"
                                rows="3" />
                        </div>
                    </div>
                </div>

                {{-- Section 2: Documentary Requirements --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-clipboard-document-list" class="w-4 h-4" />
                            Documentary Requirements
                        </span>
                    </label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Define the documents required for this event type. These will be shown to student organizations
                        when submitting event tickets.
                    </p>

                    {{-- Help Text --}}
                    <div class="bg-info/10 border-l-4 border-info p-4 rounded-r-lg mb-4">
                        <div class="flex items-start gap-3">
                            <x-mary-icon name="o-information-circle" class="w-5 h-5 text-info shrink-0 mt-0.5" />
                            <div>
                                <p class="font-medium text-sm text-base-content">Formatting Tips</p>
                                <p class="text-xs text-base-content/70 mt-1">
                                    Use the toolbar to create bulleted lists for requirements. Example: Event Proposal,
                                    Budget Breakdown, Venue Request Form, etc.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Trix Rich Text Editor --}}
                    <div class="border border-slate-200 dark:border-base-300 rounded-xl overflow-hidden bg-white dark:bg-base-100"
                        x-data="{ content: @entangle('documentaryRequirements') }" x-on:trix-change="content = $event.target.value">

                        <x-trix-input id="documentary-requirements-editor" name="documentary_requirements"
                            :value="$documentaryRequirements"
                            class="min-h-[250px] p-4 focus:ring-0 focus:outline-none prose prose-slate dark:prose-invert max-w-none" />
                    </div>

                    {{-- Live Preview --}}
                    @if ($documentaryRequirements)
                        <div class="mt-4">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">
                                <span class="flex items-center gap-2">
                                    <x-mary-icon name="o-eye" class="w-4 h-4" />
                                    Preview
                                </span>
                            </label>
                            <div
                                class="p-4 bg-slate-50 dark:bg-base-100 border border-slate-200 dark:border-base-300 rounded-xl">
                                <div class="prose prose-slate dark:prose-invert max-w-none prose-sm">
                                    {{ h($documentaryRequirements) }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Form Actions --}}
            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <x-mary-button label="Cancel" wire:click="cancel" class="btn-ghost order-2 sm:order-1"
                        icon="o-x-mark" />
                    <x-mary-button type="submit" label="{{ $isEditing ? 'Save Changes' : 'Create Event Type' }}"
                        class="btn-primary order-1 sm:order-2" spinner="save"
                        icon="{{ $isEditing ? 'o-check' : 'o-plus' }}" />
                </div>
            </x-slot:actions>
        </x-mary-card>
    </form>
</div>
