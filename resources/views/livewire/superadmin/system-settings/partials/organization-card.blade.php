<div
    class="group flex items-center justify-between p-3 sm:p-4 bg-white dark:bg-base-200 border border-slate-200 dark:border-base-300 rounded-xl hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-md transition-all duration-200 gap-3 sm:gap-4">
    <div class="flex items-center gap-3 sm:gap-4 min-w-0">
        <div
            class="relative w-10 h-10 sm:w-12 sm:h-12 flex-shrink-0 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-base-300 border border-slate-200 dark:border-base-300 overflow-hidden">
            @if ($organization->logo_url)
                <img src="{{ $organization->logo_url }}" alt="{{ $organization->org_name }}"
                    class="w-full h-full object-cover">
            @else
                <x-ui.icon name="o-user-group" class="w-5 h-5 sm:w-6 sm:h-6 text-slate-400 dark:text-slate-500" />
            @endif

            {{-- Status indicator dot --}}
            <div @class([
                'absolute bottom-0 right-0 w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full border-2 border-white dark:border-base-200',
                'bg-green-500' => $organization->status === 'active',
                'bg-slate-400' => $organization->status === 'inactive',
                'bg-red-500' => $organization->status === 'suspended',
            ])></div>
        </div>
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <p class="font-semibold text-slate-900 dark:text-white line-clamp-1 text-sm sm:text-base">
                    {{ $organization->org_name }}
                </p>
                @if ($organization->status === 'suspended')
                    <span
                        class="text-[8px] sm:text-[10px] font-bold uppercase tracking-wider px-1 sm:px-1.5 py-0.5 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded border border-red-100 dark:border-red-800">Suspended</span>
                @endif
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2 mt-0.5">
                <span
                    class="text-[10px] sm:text-xs font-medium text-primary-600 dark:text-primary-400 whitespace-nowrap">{{ $organization->org_code }}</span>
                @if ($showCourse && $organization->course)
                    <span class="text-xs text-slate-300 dark:text-slate-600">•</span>
                    <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 line-clamp-1">
                        {{ $organization->course->course_name }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="flex gap-1 shrink-0 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
        <x-ui.button size="xs" icon="o-pencil-square"
            class="btn-ghost btn-sm text-slate-400 dark:text-slate-500 hover:text-primary-600 dark:hover:text-primary-400"
            wire:click="openEditOrgModal({{ $organization->org_id }})" wire:loading.attr="disabled">
        </x-ui.button>
        <x-ui.button size="xs" icon="o-trash"
            class="btn-ghost btn-sm text-slate-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400"
            wire:click="openDeleteOrgModal({{ $organization->org_id }})" wire:loading.attr="disabled">
        </x-ui.button>
    </div>
</div>
