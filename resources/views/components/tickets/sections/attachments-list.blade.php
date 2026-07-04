@props(['ticket'])

<div class="bg-base-100 rounded-box shadow-lg p-6">
    <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
        <x-mary-icon name="o-paper-clip" class="w-5 h-5" />
        Attachments
    </h2>
    @if ($ticket->attachments->count() > 0)
        <div class="space-y-3">
            @foreach ($ticket->attachments as $attachment)
                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div>
                            @if ($attachment->exists && $attachment->file_path)
                                <button type="button"
                                    class="hover:underline hover:cursor-pointer text-neutral font-medium transition-colors"
                                    wire:click.renderless="previewAttachment({{ $attachment->attachment_id }})">
                                    {{ $attachment->file_name }}
                                </button>
                            @elseif ($attachment->getAttribute('preview_upload_index') !== null)
                                <button type="button"
                                    class="hover:underline hover:cursor-pointer text-neutral font-medium transition-colors"
                                    wire:click.renderless="previewDraftAttachment({{ $attachment->getAttribute('preview_upload_index') }})">
                                    {{ $attachment->file_name }}
                                </button>
                            @else
                                <span class="font-medium text-base-content">{{ $attachment->file_name }}</span>
                            @endif
                            <p class="text-sm text-base-content/70">
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
                        <button type="button" class="btn btn-primary btn-sm"
                            wire:click.renderless="downloadAttachment({{ $attachment->attachment_id }})">
                            Download
                        </button>
                    @elseif ($attachment->getAttribute('preview_upload_index') !== null)
                        <button type="button" class="btn btn-primary btn-sm"
                            wire:click.renderless="downloadDraftAttachment({{ $attachment->getAttribute('preview_upload_index') }})">
                            Download
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <x-mary-icon name="o-document-text" class="w-12 h-12 text-base-content/30 mx-auto mb-3" />
            <p class="text-base-content/70">No attachments uploaded</p>
        </div>
    @endif
</div>
