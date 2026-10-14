<div>
    {{-- Page Header --}}
    <x-ui.page-heading title="FAQ Management" subtitle="Manage frequently asked questions displayed on the public FAQ page">
        <x-slot:actions>
            <x-ui.button icon="o-plus" label="Add FAQ" class="btn-primary shadow-sm w-full sm:w-auto"
            link="{{ route('superadmin.faq.create') }}" wire:navigate />
        </x-slot:actions>
    </x-ui.page-heading>

    {{-- Filters Card --}}
    <x-ui.card shadow class="border-none bg-base-200/50 dark:bg-base-100/50 mb-6">
        <div class="flex flex-col lg:flex-row gap-4">
            {{-- Search --}}
            <div class="flex-1">
                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search questions, answers…"
                    icon="o-magnifying-glass" clearable class="w-full" />
            </div>

            {{-- Category Filter --}}
            <div class="w-full lg:w-48">
                <select wire:model.live="filterCategory"
                    class="select select-bordered w-full bg-base-100 dark:bg-base-200">
                    <option value="">All Categories</option>
                    <option value="__none__">Uncategorized</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="w-full lg:w-36">
                <select wire:model.live="filterStatus" class="select select-bordered w-full bg-base-100 dark:bg-base-200">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            {{-- Clear Filters --}}
            @if ($search || $filterCategory || $filterStatus)
                <x-ui.button icon="o-x-mark" label="Clear" wire:click="clearFilters"
                    class="btn-ghost btn-sm self-center" />
            @endif
        </div>
    </x-ui.card>

    {{-- FAQ List --}}
    <x-ui.card shadow class="border-none bg-base-200/50 dark:bg-base-100/50">
        @if ($faqs->count() > 0)
            <div class="space-y-3" wire:sort="handleSort">
                @foreach ($faqs as $faq)
                    <div wire:key="faq-{{ $faq->id }}" wire:sort:item="{{ $faq->id }}"
                        class="group flex flex-col sm:flex-row sm:items-start justify-between p-4 bg-base-100 dark:bg-base-200 border border-base-300 dark:border-base-300 rounded-xl hover:border-primary/40  transition duration-200 gap-4">
                        {{-- FAQ Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start gap-3">
                                {{-- Drag Handle --}}
                                <div wire:sort:handle
                                    class="cursor-grab active:cursor-grabbing w-8 h-8 shrink-0 flex items-center justify-center rounded-lg bg-base-200 dark:bg-base-300 hover:bg-primary/20 dark:hover:bg-primary/30 transition-colors"
                                    title="Drag to reorder">
                                    <x-ui.icon name="o-bars-3" class="w-4 h-4 text-base-content/60" />
                                </div>

                                <div class="flex-1 min-w-0">
                                    {{-- Question --}}
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p
                                            class="font-semibold text-base-content text-sm sm:text-base line-clamp-2">
                                            {{ $faq->question }}
                                        </p>
                                        @if (!$faq->is_active)
                                            <span
                                                class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-base-200 text-base-content/60">
                                                Inactive
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Answer Preview --}}
                                    <p class="text-xs sm:text-sm text-base-content/60 mt-1 line-clamp-2">
                                        {{ Str::limit(strip_tags($faq->answer), 150) }}
                                    </p>

                                    {{-- Category Badge --}}
                                    @if ($faq->category)
                                        <div class="mt-2">
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-secondary/10  text-secondary ">
                                                <x-ui.icon name="o-tag" class="w-3 h-3" />
                                                {{ $faq->category }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div
                            class="flex items-center gap-1 sm:gap-2 shrink-0 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity self-end sm:self-center">
                            {{-- Toggle Active Status --}}
                            <x-ui.button size="xs" :icon="$faq->is_active ? 'o-eye' : 'o-eye-slash'"
                                class="btn-ghost btn-sm {{ $faq->is_active ? 'text-success hover:text-success' : 'text-base-content/60 hover:text-base-content/70' }}"
                                wire:click="toggleActive({{ $faq->id }})" wire:loading.attr="disabled"
                                title="{{ $faq->is_active ? 'Deactivate' : 'Activate' }}" />

                            {{-- Edit --}}
                            <x-ui.button size="xs" icon="o-pencil-square"
                                class="btn-ghost btn-sm text-base-content/60 hover:text-primary-600"
                                link="{{ route('superadmin.faq.edit', $faq->id) }}" wire:navigate title="Edit" />

                            {{-- Delete --}}
                            <x-ui.button size="xs" icon="o-trash"
                                class="btn-ghost btn-sm text-base-content/60 hover:text-error"
                                wire:click="openDeleteModal({{ $faq->id }})" wire:loading.attr="disabled"
                                title="Delete" />
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($faqs->hasPages())
                <x-tickets.ticket-pagination :tickets="$faqs" label="questions" />
            @endif
        @else
            {{-- Empty State --}}
            <div
                class="text-center py-12 bg-base-100 dark:bg-base-200 border border-dashed border-base-300 dark:border-base-300 rounded-xl">
                <x-ui.icon name="o-question-mark-circle"
                    class="w-12 h-12 mx-auto mb-3 text-base-content/40" />
                <p class="text-base-content/60 font-medium">No FAQs found</p>
                <p class="text-base-content/60 text-sm mt-1">
                    @if ($search || $filterCategory || $filterStatus)
                        Try adjusting your filters or search terms.
                    @else
                        Add your first FAQ to help users find answers quickly.
                    @endif
                </p>
                @if (!$search && !$filterCategory && !$filterStatus)
                    <x-ui.button icon="o-plus" label="Add First FAQ" class="btn-primary btn-sm mt-4"
                        link="{{ route('superadmin.faq.create') }}" wire:navigate />
                @endif
            </div>
        @endif
    </x-ui.card>

    {{-- Delete Confirmation Modal --}}
    @if ($deletingFaqId)
        <x-ui.modal-dialog wire:model="deleteModalOpen" title="Delete FAQ" subtitle="Confirm deletion" separator
            with-close-button close-on-escape>
            <div class="space-y-4">
                <div class="alert alert-warning">
                    <x-ui.icon name="o-exclamation-triangle" class="w-6 h-6" />
                    <span>
                        Are you sure you want to delete this FAQ?
                    </span>
                </div>

                <div class="bg-base-200 dark:bg-base-300 rounded-lg p-4">
                    <p class="text-sm font-medium text-base-content/80">
                        {{ $deletingFaqQuestion }}
                    </p>
                </div>

                <p class="text-sm text-base-content/60">
                    This action cannot be undone. The FAQ will be permanently removed from the system.
                </p>
            </div>

            <x-slot:actions>
                <x-ui.button label="Cancel" @click="$wire.deleteModalOpen = false" class="btn-ghost" />
                <x-ui.button label="Delete FAQ" wire:click="confirmDelete" class="btn-error"
                    spinner="confirmDelete" />
            </x-slot:actions>
        </x-ui.modal-dialog>
    @endif
</div>
