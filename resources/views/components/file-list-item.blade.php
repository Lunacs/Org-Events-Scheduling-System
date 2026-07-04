@props([
    'file',
    'index',
    'showPreview' => true,
])

@php
    $fileName = $file->getClientOriginalName();
    $extension = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION) ?: 'FILE');
    $sizeKb = $file->getSize() / 1024;
    $sizeLabel = $sizeKb >= 1024
        ? number_format($sizeKb / 1024, 1) . ' MB'
        : number_format($sizeKb, 1) . ' KB';

    $iconName = match (strtolower(pathinfo($fileName, PATHINFO_EXTENSION))) {
        'pdf' => 'o-document-text',
        'doc', 'docx' => 'o-document',
        'jpg', 'jpeg', 'png' => 'o-photo',
        'xls', 'xlsx' => 'o-table-cells',
        default => 'o-paper-clip',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2 rounded-lg bg-base-200 p-3 sm:flex-row sm:items-center sm:justify-between sm:gap-3']) }}>
    {{-- File info: icon + name/meta --}}
    <div class="flex min-w-0 items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-base-100">
            <x-mary-icon :name="$iconName" class="h-5 w-5 text-base-content/70" />
        </div>

        <div class="min-w-0 flex-1">
            @if ($showPreview)
                <button
                    type="button"
                    class="link link-neutral block w-full truncate text-left text-sm font-medium"
                    wire:click.renderless="previewDraftAttachment({{ $index }})"
                >
                    {{ $fileName }}
                </button>
            @else
                <p class="truncate text-sm font-medium text-base-content">{{ $fileName }}</p>
            @endif

            <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-base-content/60">
                <span class="badge badge-ghost badge-sm">{{ $extension }}</span>
                <span>{{ $sizeLabel }}</span>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex shrink-0 flex-wrap items-center gap-2 sm:flex-nowrap">
        @if ($showPreview)
            <button
                type="button"
                class="btn btn-primary btn-sm flex-1 sm:flex-none"
                wire:click.renderless="previewDraftAttachment({{ $index }})"
            >
                <x-mary-icon name="o-eye" class="h-4 w-4" />
                <span class="sm:inline">Preview</span>
            </button>
            <button
                type="button"
                class="btn btn-ghost btn-sm flex-1 sm:flex-none"
                wire:click.renderless="downloadDraftAttachment({{ $index }})"
            >
                <x-mary-icon name="o-arrow-down-tray" class="h-4 w-4" />
                <span class="sm:inline">Download</span>
            </button>
        @endif

        <button
            type="button"
            class="btn btn-ghost btn-error btn-sm"
            wire:click="removeAttachment({{ $index }})"
            aria-label="Remove {{ $fileName }}"
        >
            <x-mary-icon name="o-trash" class="h-4 w-4" />
        </button>
    </div>
</div>
