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
                            <button type="button" class="link link-neutral font-medium"
                                wire:click="previewAttachment({{ $attachment->attachment_id }})">
                                {{ $attachment->file_name }}
                            </button>
                            <p class="text-sm text-base-content/70">
                                {{ $attachment->file_type ? strtoupper($attachment->file_type) : (strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION)) ?: 'FILE') }}
                            </p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm"
                        wire:click="downloadAttachment({{ $attachment->attachment_id }})">
                        Download
                    </button>
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
