{{--
    Content Section Display Component

    Usage:
    <x-content-section key="announcements" />
    <x-content-section key="terms_conditions" class="text-sm" />
    <x-content-section key="documentary_requirements" :show-title="false" />
--}}

@props(['key', 'showTitle' => true, 'class' => ''])

@php
    $section = \App\Models\ContentSection::getByKey($key);
@endphp

@if ($section && $section->is_active)
    <div {{ $attributes->merge(['class' => 'content-section ' . $class]) }}>
        @if ($showTitle && $section->title)
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-3">
                {{ $section->title }}
            </h3>
        @endif

        <div class="prose prose-slate dark:prose-invert max-w-none prose-p:mb-3 prose-ul:my-2 prose-li:my-0">
            {{ h($section->content) }}
        </div>
    </div>
@endif
