<div>
    {{-- Main Card --}}
    <x-ui.card shadow class="border-none bg-slate-50/50 dark:bg-base-100/50">
        {{-- Header Section - User-friendly with clear labels --}}
        <div class="flex flex-col gap-4 mb-6">
            {{-- Title and Description --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <x-ui.icon name="o-document-text" class="w-6 h-6 text-primary-500" />
                        Content Sections
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Manage announcements, terms & conditions, requirements, and other editable content displayed
                        throughout the system.
                    </p>
                </div>
                <a href="{{ route('superadmin.content-section.create') }}" wire:navigate
                    class="btn btn-primary shadow-sm w-full md:w-auto gap-2">
                    <x-ui.icon name="o-plus" class="w-5 h-5" />
                    Add New Content
                </a>
            </div>

            {{-- Search and Filter Bar --}}
            <div
                class="flex flex-col sm:flex-row gap-3 p-4 bg-white dark:bg-base-200 rounded-xl border border-slate-200 dark:border-base-300">
                <div class="flex-1">
                    <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search by title or key..."
                        icon="o-magnifying-glass" class="input-sm" clearable />
                </div>
                <div class="sm:w-48">
                    <x-ui.select wire:model.live="filterType" :options="$sectionTypes" placeholder="All Content Types"
                        option-value="id" option-label="name" class="select-sm" />
                </div>
            </div>
        </div>

        {{-- Content Sections List --}}
        @if (count($contentSections) > 0)
            <div class="space-y-3">
                @foreach ($contentSections as $section)
                    <div
                        class="group flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white dark:bg-base-200 border border-slate-200 dark:border-base-300 rounded-xl hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-md transition-all duration-200 gap-4">
                        {{-- Left Section - Icon, Title, and Info --}}
                        <div class="flex items-start gap-4 min-w-0 flex-1">
                            {{-- Type Icon with Color --}}
                            <div
                                class="w-12 h-12 shrink-0 flex items-center justify-center rounded-xl {{ $section->type_color }}">
                                <x-ui.icon name="{{ $section->type_icon }}" class="w-6 h-6" />
                            </div>

                            {{-- Content Info --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <p class="font-semibold text-slate-900 dark:text-white text-sm sm:text-base">
                                        {{ $section->title }}
                                    </p>
                                    {{-- Status Badge --}}
                                    @if ($section->is_active)
                                        <span class="badge badge-success badge-sm gap-1">
                                            <x-ui.icon name="o-check-circle" class="w-3 h-3" />
                                            Active
                                        </span>
                                    @else
                                        <span class="badge badge-ghost badge-sm gap-1">
                                            <x-ui.icon name="o-x-circle" class="w-3 h-3" />
                                            Inactive
                                        </span>
                                    @endif
                                </div>

                                {{-- Type Label and Key --}}
                                <div
                                    class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <span
                                        class="px-2 py-0.5 rounded-full {{ $section->type_color }} text-xs font-medium">
                                        {{ $section->type_label }}
                                    </span>
                                    <span
                                        class="font-mono text-[10px] px-2 py-0.5 bg-slate-100 dark:bg-base-300 rounded">
                                        {{ $section->section_key }}
                                    </span>
                                </div>

                                {{-- Content Preview --}}
                                @if ($section->content)
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 line-clamp-1">
                                        {{ Str::limit(strip_tags($section->content), 80) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Right Section - Actions --}}
                        <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                            {{-- Preview Button --}}
                            <x-ui.button size="sm" icon="o-eye"
                                class="btn-ghost text-slate-400 dark:text-slate-500 hover:text-primary-600 dark:hover:text-primary-400"
                                wire:click="openPreview({{ $section->id }})" tooltip="Preview content" />

                            {{-- Toggle Active Button --}}
                            <x-ui.button size="sm" icon="{{ $section->is_active ? 'o-eye-slash' : 'o-eye' }}"
                                class="btn-ghost text-slate-400 dark:text-slate-500 hover:text-warning-600 dark:hover:text-warning-400"
                                wire:click="toggleActive({{ $section->id }})"
                                tooltip="{{ $section->is_active ? 'Deactivate' : 'Activate' }}" />

                            {{-- Edit Button - Navigate to edit page --}}
                            <a href="{{ route('superadmin.content-section.edit', $section->id) }}" wire:navigate
                                class="btn btn-sm btn-ghost text-slate-400 dark:text-slate-500 hover:text-primary-600 dark:hover:text-primary-400"
                                title="Edit content">
                                <x-ui.icon name="o-pencil-square" class="w-5 h-5" />
                            </a>

                            {{-- Delete Button --}}
                            <x-ui.button size="sm" icon="o-trash"
                                class="btn-ghost text-slate-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400"
                                wire:click="openDeleteModal({{ $section->id }})" tooltip="Delete" />
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div
                class="text-center py-16 bg-white dark:bg-base-200 border border-dashed border-slate-300 dark:border-base-300 rounded-xl">
                <div
                    class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-slate-100 dark:bg-base-300">
                    <x-ui.icon name="o-document-text" class="w-8 h-8 text-slate-400 dark:text-slate-500" />
                </div>
                <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-200 mb-2">No Content Sections Yet</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-6 max-w-md mx-auto">
                    Create content sections like announcements, terms & conditions, and documentary requirements that
                    can be displayed throughout your system.
                </p>
                <a href="{{ route('superadmin.content-section.create') }}" wire:navigate class="btn btn-primary gap-2">
                    <x-ui.icon name="o-plus" class="w-5 h-5" />
                    Create Your First Content Section
                </a>
            </div>
        @endif
    </x-ui.card>

    {{-- Preview Modal (Keep this as a modal since it's just viewing) --}}
    @if ($previewSection)
        <x-ui.modal-dialog wire:model="previewModalOpen" title="{{ $previewSection->title }}" subtitle="Content Preview"
            separator box-class="max-w-3xl" with-close-button close-on-escape>
            <div class="p-4 bg-slate-50 dark:bg-base-200 rounded-xl">
                {{-- Section Info --}}
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-slate-200 dark:border-base-300">
                    <div
                        class="w-10 h-10 flex items-center justify-center rounded-lg {{ $previewSection->type_color }}">
                        <x-ui.icon name="{{ $previewSection->type_icon }}" class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ $previewSection->type_label }}</p>
                        <p class="text-xs text-slate-400 font-mono">{{ $previewSection->section_key }}</p>
                    </div>
                    @if ($previewSection->is_active)
                        <span class="badge badge-success badge-sm ml-auto">Active</span>
                    @else
                        <span class="badge badge-ghost badge-sm ml-auto">Inactive</span>
                    @endif
                </div>

                {{-- Content Display --}}
                <div class="prose prose-slate dark:prose-invert max-w-none">
                    {{ h($previewSection->content ?: '<p class="text-slate-400 italic">No content yet.</p>') }}
                </div>
            </div>

            <x-slot:actions>
                <x-ui.button label="Close" @click="$wire.closePreview()" />
                <a href="{{ route('superadmin.content-section.edit', $previewSection->id) }}" wire:navigate
                    class="btn btn-primary gap-2">
                    <x-ui.icon name="o-pencil-square" class="w-4 h-4" />
                    Edit This Content
                </a>
            </x-slot:actions>
        </x-ui.modal-dialog>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($deletingId)
        <x-ui.modal-dialog wire:model="deleteModalOpen" title="Delete Content Section"
            subtitle="This action cannot be undone" separator with-close-button close-on-escape>
            <div class="space-y-4">
                <div
                    class="flex items-start gap-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/50 rounded-xl">
                    <div
                        class="w-10 h-10 shrink-0 flex items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <x-ui.icon name="o-exclamation-triangle" class="w-5 h-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="font-medium text-red-800 dark:text-red-200">
                            Are you sure you want to delete this content section?
                        </p>
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                            "<strong>{{ $deletingTitle }}</strong>" will be permanently removed and this action cannot
                            be undone.
                        </p>
                    </div>
                </div>

                <div
                    class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/50 rounded-xl">
                    <div class="flex items-start gap-3">
                        <x-ui.icon name="o-information-circle"
                            class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                        <p class="text-sm text-amber-700 dark:text-amber-300">
                            If this content is currently displayed somewhere in the system, it will no longer appear
                            after deletion.
                        </p>
                    </div>
                </div>
            </div>

            <x-slot:actions>
                <x-ui.button label="Cancel" @click="$wire.deleteModalOpen = false; $wire.resetDeleteModal()"
                    class="btn-ghost" />
                <x-ui.button label="Delete Permanently" wire:click="confirmDelete" class="btn-error"
                    spinner="confirmDelete" icon="o-trash" />
            </x-slot:actions>
        </x-ui.modal-dialog>
    @endif
</div>
