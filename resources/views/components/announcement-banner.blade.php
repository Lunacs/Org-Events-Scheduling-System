{{--
    Announcement Banner Component
    Displays active CMS announcements in a sticky banner at the top of the page
    Filters announcements based on the current user's role

    Usage: <x-announcement-banner />
--}}

@php
    $announcements = \App\Models\ContentSection::getActiveByTypeForUser('announcement', auth()->user());
@endphp

@if ($announcements->isNotEmpty())
    <div x-data="{ dismissed: JSON.parse(localStorage.getItem('dismissed_announcements') || '[]') }" class="announcement-banner-container">
        @foreach ($announcements as $announcement)
            <div x-cloak x-show="!dismissed.includes('{{ $announcement->section_key }}')"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                class="announcement-banner bg-primary/10 border-b border-primary/20 px-4 py-3">
                <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <x-mary-icon name="o-megaphone" class="w-5 h-5 text-primary shrink-0 mt-0.5" />
                        <div class="flex-1 min-w-0">
                            @if ($announcement->title)
                                <p class="font-semibold text-sm text-primary mb-1">{{ $announcement->title }}</p>
                            @endif
                            <div
                                class="prose prose-sm prose-slate dark:prose-invert max-w-none text-base-content/80 line-clamp-2">
                                {{ h($announcement->content) }}
                            </div>
                        </div>
                    </div>
                    <button
                        @click="dismissed.push('{{ $announcement->section_key }}'); localStorage.setItem('dismissed_announcements', JSON.stringify(dismissed))"
                        class="btn btn-ghost btn-sm btn-circle shrink-0" title="Dismiss announcement">
                        <x-mary-icon name="o-x-mark" class="w-4 h-4 text-gray-700 dark:text-gray-200" />
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@endif
