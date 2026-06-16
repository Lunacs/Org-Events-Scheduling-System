@push('head')
    @vite('resources/js/libs/trix.js')
@endpush

<div class="p-4 sm:p-6 max-w-4xl mx-auto">
    {{-- Header with Breadcrumb --}}
    <section
        class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm mb-6">
        <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
        <div class="relative p-6 sm:p-8">
            {{-- Back Button --}}
            <button type="button" wire:click="cancel"
                class="inline-flex items-center gap-1.5 text-sm text-base-content/60 hover:text-primary transition-colors mb-4 group cursor-pointer relative z-10">
                <x-mary-icon name="o-arrow-left" class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" />
                Back to Content Sections
            </button>

            <div class="flex items-center gap-4 relative z-10">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/20">
                    <x-mary-icon name="{{ $isEditing ? 's-pencil-square' : 's-plus' }}"
                        class="w-6 h-6 text-primary" />
                </span>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-base-content">
                        {{ $isEditing ? 'Edit Content Section' : 'Add New Content Section' }}
                    </h1>
                    <p class="text-sm text-base-content/70 mt-1">
                        {{ $isEditing ? 'Update the content section details below.' : 'Create a new content section that will be displayed in the system.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Form Card --}}
    <form wire:submit.prevent="save">
        <x-mary-card shadow class="bg-white dark:bg-base-200 border-none">
            <div class="space-y-6">
                {{-- Section 1: Content Type --}}
                <div class="pb-6 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-tag" class="w-4 h-4" />
                            Content Type
                            <span class="text-error">*</span>
                        </span>
                    </label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Select the type of content you want to create. This helps organize and display content
                        appropriately.
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        @foreach ($sectionTypes as $type)
                            <label
                                class="cursor-pointer p-4 rounded-xl border-2 transition-all duration-300 ease-in-out hover:shadow-lg active:scale-95
                                {{ $sectionType === $type['id'] ? 'border-gray-800 dark:border-slate-200 bg-primary-50 dark:bg-primary-900/20 shadow-md' : 'border-slate-200 dark:border-base-300 hover:border-primary-300 dark:hover:border-primary-700' }}">
                                <input type="radio" wire:model.live="sectionType" value="{{ $type['id'] }}"
                                    class="hidden" />
                                <div class="flex flex-col items-center text-center gap-2">
                                    <div
                                        class="w-10 h-10 flex items-center justify-center rounded-lg
                                        {{ $sectionType === $type['id'] ? 'bg-primary-100 dark:bg-primary-800' : 'bg-slate-100 dark:bg-base-300' }}">
                                        <x-mary-icon
                                            name="{{ match ($type['id']) {
                                                'announcement' => 'o-megaphone',
                                                'terms_conditions' => 'o-document-check',
                                                'ticket_guidelines' => 'o-clipboard-document-list',
                                                'reschedule_guidelines' => 'o-arrow-path',
                                                'page_content' => 'o-document-text',
                                                default => 'o-document',
                                            } }}"
                                            class="w-5 h-5 {{ $sectionType === $type['id'] ? 'text-gray-800 dark:text-white' : 'text-slate-400' }}" />
                                    </div>
                                    <span
                                        class="text-xs font-medium {{ $sectionType === $type['id'] ? 'text-black dark:text-base-content' : 'text-slate-600 dark:text-slate-400' }}">
                                        {{ $type['name'] }}
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('sectionType')
                        <p class="text-error text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Section 2: Basic Information --}}
                <div class="pb-6 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-information-circle" class="w-4 h-4" />
                            Basic Information
                        </span>
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Title --}}
                        <div>
                            <x-mary-input wire:model.live="title" label="Title"
                                placeholder="e.g., Event Submission Guidelines" icon="o-document-text" required />
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                This will be displayed as the heading of the content section.
                            </p>
                        </div>

                        {{-- Section Key --}}
                        <div>
                            <x-mary-input wire:model.live="sectionKey" label="Section Key"
                                placeholder="e.g., event_guidelines" icon="o-key" required :disabled="$isEditing"
                                readonly />
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                {{ $isEditing ? 'Section key cannot be changed after creation.' : 'Unique identifier. Auto-generated from title.' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        {{-- Display Order --}}
                        <div>
                            <x-mary-input wire:model="displayOrder" label="Display Order" type="number" min="0"
                                icon="o-arrows-up-down" placeholder="0" />
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                Lower numbers appear first when multiple sections of the same type are displayed.
                            </p>
                        </div>

                        {{-- Active Status --}}
                        <div class="flex items-end pb-2">
                            <x-mary-toggle wire:model="isActive" label="Active"
                                hint="Active sections are visible in the system. Inactive sections are hidden." />
                        </div>
                    </div>
                </div>

                {{-- Section 2.5: Target Audience (only for announcements) --}}
                @if ($sectionType === 'announcement')
                    <div class="pb-6 border-b border-slate-200 dark:border-base-300" x-data="{ roles: $wire.entangle('targetRoles') }">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">
                            <span class="flex items-center gap-2">
                                <x-mary-icon name="o-user-group" class="w-4 h-4" />
                                Target Audience
                            </span>
                        </label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                            Choose which user roles should see this announcement. Leave unchecked to show to all users.
                        </p>

                        <div class="space-y-3">
                            {{-- All Users Toggle --}}
                            <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-base-300/50">
                                <input type="checkbox" id="all-users" class="checkbox checkbox-gray-600 checkbox-sm"
                                    x-bind:checked="roles.length === 0" @click="roles = []" />
                                <label for="all-users" class="flex items-center gap-2 cursor-pointer">
                                    <x-mary-icon name="o-globe-alt" class="w-4 h-4 text-base-content" />
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">All
                                        Users</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">(Show to everyone)</span>
                                </label>
                            </div>

                            {{-- Divider --}}
                            <div class="flex items-center gap-3 text-xs text-slate-400 dark:text-slate-500">
                                <div class="flex-1 border-t border-slate-200 dark:border-base-300"></div>
                                <span>or select specific roles</span>
                                <div class="flex-1 border-t border-slate-200 dark:border-base-300"></div>
                            </div>

                            {{-- Role Checkboxes --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach ($roleOptions as $role)
                                    <label
                                        class="flex items-center gap-2 p-3 rounded-lg border-2 cursor-pointer transition-all"
                                        x-bind:class="roles.includes('{{ $role['id'] }}') ?
                                            'border-gray-800 bg-gray-800/5 dark:bg-gray-800/10' :
                                            'border-slate-200 dark:border-base-300 hover:border-gray-500'">
                                        <input type="checkbox" value="{{ $role['id'] }}"
                                            x-bind:checked="roles.includes('{{ $role['id'] }}')"
                                            @change="if ($event.target.checked) { roles.push('{{ $role['id'] }}') } else { roles = roles.filter(r => r !== '{{ $role['id'] }}') }"
                                            class="checkbox checkbox-gray-600 checkbox-sm" />
                                        <div>
                                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                                {{ $role['name'] }}
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            @error('targetRoles')
                                <p class="text-error text-xs mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- Section 3: Content Editor --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-pencil" class="w-4 h-4" />
                            Content
                        </span>
                    </label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Use the rich text editor below to format your content. You can add headings, lists, links, and
                        more.
                    </p>

                    {{-- Trix Rich Text Editor --}}
                    <div class="border border-slate-200 dark:border-base-300 rounded-xl overflow-hidden bg-white dark:bg-base-100"
                        x-data="{ content: @entangle('content') }" x-on:trix-change="content = $event.target.value">

                        {{-- Trix Editor Component --}}
                        <x-trix-input id="content-editor" name="content" :value="$content" :acceptFiles="false"
                            class="min-h-[300px] p-4 focus:ring-0 focus:outline-none prose prose-slate dark:prose-invert max-w-none" />
                    </div>

                    {{-- Formatting Help --}}
                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                        <span>Shortcuts:</span>
                        <span class="px-2 py-0.5 bg-slate-100 dark:bg-base-300 rounded font-mono">Ctrl+B</span> Bold
                        <span class="px-2 py-0.5 bg-slate-100 dark:bg-base-300 rounded font-mono">Ctrl+I</span> Italic
                        <span class="px-2 py-0.5 bg-slate-100 dark:bg-base-300 rounded font-mono">Ctrl+K</span> Link
                    </div>

                    {{-- Live Preview --}}
                    @if ($content)
                        <div class="mt-4">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">
                                <span class="flex items-center gap-2">
                                    <x-mary-icon name="o-eye" class="w-4 h-4" />
                                    Live Preview
                                </span>
                            </label>
                            <div
                                class="p-4 bg-slate-50 dark:bg-base-100 border border-slate-200 dark:border-base-300 rounded-xl">
                                <div class="prose prose-slate dark:prose-invert max-w-none prose-sm">
                                    {{ h($content) }}
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
                    <x-mary-button type="submit"
                        label="{{ $isEditing ? 'Save Changes' : 'Create Content Section' }}"
                        class="btn-primary order-1 sm:order-2" spinner="save"
                        icon="{{ $isEditing ? 'o-check' : 'o-plus' }}" />
                </div>
            </x-slot:actions>
        </x-mary-card>
    </form>

    {{-- Help Card --}}
    <x-mary-card shadow class="mt-6 bg-blue-50 dark:bg-blue-900/20 border-none">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 shrink-0 flex items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-800">
                <x-mary-icon name="o-light-bulb" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
                <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Tips for Great Content</h3>
                <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                    <li>• Keep titles clear and descriptive</li>
                    <li>• Use short paragraphs for easy reading</li>
                    <li>• Use the toolbar buttons to format text (bold, italic, lists)</li>
                    <li>• Add links by selecting text and clicking the link button</li>
                    <li>• Preview your content before saving to ensure it looks right</li>
                </ul>
            </div>
        </div>
    </x-mary-card>
</div>
