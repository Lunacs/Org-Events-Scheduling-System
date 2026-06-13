<div class="p-4 sm:p-6 max-w-4xl mx-auto">
    {{-- Header with Breadcrumb --}}
    <section
        class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm mb-6">
        <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
        <div class="relative p-6 sm:p-8">
            {{-- Breadcrumb --}}
            <nav class="flex items-center text-sm text-base-content/60 mb-4 relative z-10">
                <a href="{{ route('superadmin.dashboard') }}" wire:navigate
                    class="hover:text-primary transition-colors">
                    Dashboard
                </a>
                <x-mary-icon name="o-chevron-right" class="w-4 h-4 mx-2" />
                <a href="{{ route('superadmin.faqs') }}" wire:navigate
                    class="hover:text-primary transition-colors">
                    FAQ Management
                </a>
                <x-mary-icon name="o-chevron-right" class="w-4 h-4 mx-2" />
                <span class="text-base-content font-medium">
                    {{ $isEditing ? 'Edit FAQ' : 'Add New FAQ' }}
                </span>
            </nav>

            <div class="flex items-center gap-4 relative z-10">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/20">
                    <x-mary-icon name="{{ $isEditing ? 's-pencil-square' : 's-plus' }}"
                        class="w-6 h-6 text-primary" />
                </span>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-base-content">
                        {{ $isEditing ? 'Edit FAQ' : 'Add New FAQ' }}
                    </h1>
                    <p class="text-sm text-base-content/70 mt-1">
                        {{ $isEditing ? 'Update the FAQ details below.' : 'Create a new frequently asked question.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Form Card --}}
    <form wire:submit.prevent="save">
        <x-mary-card shadow class="bg-white dark:bg-base-200 border-none">
            <div class="space-y-6">
                {{-- Section 1: Question & Answer --}}
                <div class="pb-6 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-question-mark-circle" class="w-4 h-4" />
                            Question & Answer
                        </span>
                    </label>

                    <div class="space-y-4">
                        {{-- Question --}}
                        <div>
                            <x-mary-input wire:model="question" label="Question"
                                placeholder="e.g., How long does the approval process take?" icon="o-chat-bubble-left"
                                required />
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                Enter a clear, concise question that users commonly ask.
                            </p>
                        </div>

                        {{-- Answer --}}
                        <div>
                            <x-mary-textarea wire:model="answer" label="Answer"
                                placeholder="Provide a detailed answer to the question..." rows="6" required />
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                HTML formatting is supported. Provide a helpful, complete answer.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Organization --}}
                <div class="pb-6 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-tag" class="w-4 h-4" />
                            Organization
                        </span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Category with Searchable Combobox --}}
                        <div x-data="{ open: @entangle('showCategoryDropdown') }" @click.away="open = false; $wire.closeCategoryDropdown()"
                            class="relative">
                            <label class="label">
                                <span class="label-text font-medium">Category (Optional)</span>
                            </label>

                            {{-- Search Input --}}
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="categorySearch"
                                    wire:focus="openCategoryDropdown" @focus="open = true"
                                    placeholder="Search or type new category..."
                                    class="input input-bordered w-full bg-white dark:bg-base-300 pr-10" />

                                {{-- Clear button --}}
                                @if ($categorySearch)
                                    <button type="button" wire:click="clearCategory"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 btn btn-ghost btn-xs btn-circle">
                                        <x-mary-icon name="o-x-mark" class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>

                            {{-- Dropdown --}}
                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute z-50 mt-1 w-full bg-white dark:bg-base-300 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">

                                {{-- Filtered Categories List --}}
                                @if (count($this->filteredCategories) > 0)
                                    @foreach ($this->filteredCategories as $cat)
                                        <div
                                            class="group flex items-center hover:bg-base-200 dark:hover:bg-base-200/50">
                                            @if ($categoryMode === 'editing' && $editingCategory === $cat)
                                                {{-- Edit Mode --}}
                                                <div class="flex-1 flex items-center gap-2 p-2">
                                                    <input type="text" wire:model="editCategoryName"
                                                        wire:keydown.enter.prevent="saveEditCategory"
                                                        wire:keydown.escape="cancelEditCategory"
                                                        class="input input-bordered input-sm flex-1 bg-white dark:bg-base-200"
                                                        autofocus />
                                                    <button type="button" wire:click="saveEditCategory"
                                                        class="btn btn-xs btn-success btn-square">
                                                        <x-mary-icon name="o-check" class="w-3 h-3" />
                                                    </button>
                                                    <button type="button" wire:click="cancelEditCategory"
                                                        class="btn btn-xs btn-ghost btn-square">
                                                        <x-mary-icon name="o-x-mark" class="w-3 h-3" />
                                                    </button>
                                                </div>
                                            @else
                                                {{-- View Mode --}}
                                                <button type="button"
                                                    wire:click="selectCategory('{{ $cat }}')"
                                                    class="flex-1 text-left px-3 py-2 text-sm {{ $category === $cat ? 'font-semibold text-primary' : '' }}">
                                                    {{ $cat }}
                                                </button>
                                                <div
                                                    class="flex items-center gap-1 pr-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <button type="button"
                                                        wire:click="startEditCategory('{{ $cat }}')"
                                                        class="btn btn-xs btn-ghost btn-square text-info hover:bg-info/20"
                                                        title="Edit">
                                                        <x-mary-icon name="o-pencil" class="w-3 h-3" />
                                                    </button>
                                                    <button type="button"
                                                        wire:click="deleteCategory('{{ $cat }}')"
                                                        wire:confirm="Delete '{{ $cat }}'? All FAQs using this will become uncategorized."
                                                        class="btn btn-xs btn-ghost btn-square text-error hover:bg-error/20"
                                                        title="Delete">
                                                        <x-mary-icon name="o-trash" class="w-3 h-3" />
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                {{-- Create New Option (shown when search doesn't match exactly) --}}
                                @if ($categorySearch && !$this->isExactMatch)
                                    <div class="border-t border-base-300">
                                        <button type="button" wire:click="createNewCategory"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-sm text-success hover:bg-success/10">
                                            <x-mary-icon name="o-plus-circle" class="w-4 h-4" />
                                            Create "<strong>{{ $categorySearch }}</strong>"
                                        </button>
                                    </div>
                                @endif

                                {{-- Empty State --}}
                                @if (count($this->filteredCategories) === 0 && !$categorySearch)
                                    <div class="px-3 py-4 text-center text-sm text-slate-400">
                                        <x-mary-icon name="o-inbox" class="w-6 h-6 mx-auto mb-1 opacity-50" />
                                        <p>No categories yet</p>
                                    </div>
                                @endif
                            </div>

                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                Type to search or create new. Hover items to edit/delete.
                            </p>
                        </div>

                        {{-- Display Order --}}
                        <div class="block">
                            {{-- <x-mary-input type="number" wire:model="displayOrder" label="Display Order" placeholder="0"
                                icon="o-arrows-up-down" min="0" /> --}}
                            <label class="label">
                                <span class="label-text font-medium">Display Order </span>
                            </label>
                            <input type="number" class="input w-full" required placeholder="0" min="1"
                                title="Must be between be 1 to 10" wire:model="displayOrder" label="Display Order" />
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                Lower numbers appear first. Use to control FAQ ordering.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Section 3: Status --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-cog-6-tooth" class="w-4 h-4" />
                            Status
                        </span>
                    </label>

                    <div class="bg-slate-50 dark:bg-base-300 rounded-xl p-4">
                        <x-mary-toggle wire:model="isActive" label="Active FAQ"
                            hint="Only active FAQs are displayed on the public FAQ page" />

                        @if ($isEditing && !$isActive)
                            <div class="mt-4 bg-warning/10 border-l-4 border-warning p-4 rounded-r-lg">
                                <div class="flex items-start gap-3">
                                    <x-mary-icon name="o-exclamation-triangle"
                                        class="w-5 h-5 text-warning shrink-0 mt-0.5" />
                                    <div>
                                        <p class="font-medium text-sm text-base-content">Hidden from Public</p>
                                        <p class="text-xs text-base-content/70 mt-1">
                                            This FAQ will not be visible on the public FAQ page until activated.
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
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <x-mary-button label="Cancel" wire:click="cancel" class="btn-ghost order-2 sm:order-1" />
                    <x-mary-button type="submit" label="{{ $isEditing ? 'Update FAQ' : 'Create FAQ' }}"
                        class="btn-primary order-1 sm:order-2" spinner="save" />
                </div>
            </x-slot:actions>
        </x-mary-card>
    </form>

    {{-- Preview Card (for editing) --}}
    @if ($question || $answer)
        <div class="mt-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                <x-mary-icon name="o-eye" class="w-4 h-4" />
                Preview
            </h3>
            <x-mary-card shadow class="bg-white dark:bg-base-200 border-none">
                <div class="collapse collapse-plus bg-base-100 dark:bg-base-300 border border-base-300 rounded-xl">
                    <input type="checkbox" checked />
                    <div class="collapse-title text-base font-semibold text-base-content pr-12">
                        <span class="flex items-start gap-3">
                            <x-mary-icon name="o-question-mark-circle"
                                class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" />
                            {{ $question ?: 'Your question will appear here...' }}
                        </span>
                    </div>
                    <div class="collapse-content">
                        <div class="pt-2 pl-8 text-base-content/80 prose prose-sm max-w-none">
                            {!! $answer ?: '<em class="text-slate-400">Your answer will appear here...</em>' !!}
                        </div>
                    </div>
                </div>
            </x-mary-card>
        </div>
    @endif
</div>
