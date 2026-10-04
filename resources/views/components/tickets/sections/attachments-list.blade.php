@props(['ticket'])

<div class="rounded-box border border-base-300 bg-base-100 p-4 sm:p-5">
    <h2 class="mb-4 flex items-center gap-2 text-base font-semibold text-base-content">
        <x-ui.icon name="o-paper-clip" class="size-4 shrink-0 text-base-content/60" aria-hidden="true" />
        Attachments
        @if ($ticket->attachments->count() > 0)
            <span class="badge badge-soft badge-sm tabular-nums">{{ $ticket->attachments->count() }}</span>
        @endif
    </h2>
    @if ($ticket->attachments->count() > 0)
        <ul class="-my-1 divide-y divide-base-300">
            @foreach ($ticket->attachments as $attachment)
                <li class="flex items-center justify-between gap-3 py-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <x-ui.icon name="o-document" class="size-5 shrink-0 text-base-content/60" aria-hidden="true" />
                        <div class="min-w-0">
                            @if ($attachment->exists && $attachment->file_path)
                                <button type="button"
                                    class="block max-w-full truncate text-left text-sm font-medium text-primary hover:underline" title="Preview"
                                    wire:click.renderless="previewAttachment({{ $attachment->attachment_id }})">
                                    {{ $attachment->file_name }}
                                </button>
                            @elseif ($attachment->getAttribute('preview_upload_index') !== null)
                                <button type="button"
                                    class="block max-w-full truncate text-left text-sm font-medium text-primary hover:underline" title="Preview"
                                    wire:click.renderless="previewDraftAttachment({{ $attachment->getAttribute('preview_upload_index') }})">
                                    {{ $attachment->file_name }}
                                </button>
                            @else
                                <span class="block truncate text-sm font-medium text-base-content">{{ $attachment->file_name }}</span>
                            @endif
                            <p class="text-xs text-base-content/70">
                                @php
                                    $ext = pathinfo($attachment->file_name, PATHINFO_EXTENSION);
                                    if (!$ext && $attachment->file_type) {
                                        $mime = $attachment->file_type;
                                        if (str_contains($mime, 'spreadsheetml')) {
                                            $ext = 'xlsx';
                                        } elseif (str_contains($mime, 'wordprocessingml')) {
                                            $ext = 'docx';
                                        } elseif (str_contains($mime, 'presentationml')) {
                                            $ext = 'pptx';
                                        } elseif (str_contains($mime, 'document')) {
                                            $ext = 'pdf';
                                        } else {
                                            $ext = explode('/', $mime)[1] ?? 'file';
                                        }
                                    }
                                @endphp
                                {{ strtoupper($ext ?: 'FILE') }}
                            </p>
                        </div>
                    </div>
                    @if ($attachment->exists && $attachment->file_path)
                        <button type="button" class="btn btn-ghost btn-sm shrink-0"
                            wire:click.renderless="downloadAttachment({{ $attachment->attachment_id }})"
                            aria-label="Download {{ $attachment->file_name }}">
                            <x-ui.icon name="o-arrow-down-tray" class="size-4" aria-hidden="true" />
                            <span class="hidden sm:inline">Download</span>
                        </button>
                    @elseif ($attachment->getAttribute('preview_upload_index') !== null)
                        <button type="button" class="btn btn-ghost btn-sm shrink-0"
                            wire:click.renderless="downloadDraftAttachment({{ $attachment->getAttribute('preview_upload_index') }})"
                            aria-label="Download {{ $attachment->file_name }}">
                            <x-ui.icon name="o-arrow-down-tray" class="size-4" aria-hidden="true" />
                            <span class="hidden sm:inline">Download</span>
                        </button>
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <x-ui.empty-state compact icon="o-paper-clip" title="No attachments"
            description="Required documents uploaded with the request appear here." />
    @endif
</div>
