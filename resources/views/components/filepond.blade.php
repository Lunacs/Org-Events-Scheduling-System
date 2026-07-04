@props([
    'wireModel',
    'accept' => 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'inputId' => 'filepond-upload',
    'constraintsId' => 'filepond-constraints',
    'maxFiles' => 25,
    'maxSizeMb' => 10,
])

<div class="bg-primary/5 p-4 sm:p-6 rounded-3xl">
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200">
        <!-- Header -->
        <div class="p-6 flex items-center gap-4 border-b border-base-200">
            <div class="flex-shrink-0 w-14 h-14 flex items-center justify-center rounded-full bg-base-100 shadow-md border border-base-200">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-base-content">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-base-content">Upload Files</h3>
                <p class="text-sm text-base-content/60">Select and upload the files of choice</p>
            </div>
        </div>

        <!-- Dropzone Area -->
        <div class="p-6 sm:p-8">
            <div wire:ignore x-data="filepondComponent(@entangle($wireModel))" class="space-y-3">
                {{-- Responsive overrides for FilePond's fixed-height drop zone --}}
                <style>
                    .filepond--root {
                        font-family: inherit;
                        margin-bottom: 0;
                    }
                    /* Let the drop zone grow to fit its content instead of a fixed 76px */
                    .filepond--drop-label {
                        height: auto !important;
                        min-height: 12rem;
                        padding: 2rem 1rem;
                        box-sizing: border-box;
                    }
                    .filepond--drop-label label {
                        cursor: pointer;
                        width: 100%;
                    }
                    .filepond--panel-root {
                        background-color: transparent;
                        border: 1px dashed oklch(var(--bc) / 0.3);
                        border-radius: 1rem;
                    }
                    .filepond--label-action {
                        text-decoration: none;
                        font-weight: 600;
                    }
                    /* Item panel */
                    .filepond--item-panel {
                        border-radius: 0.5rem;
                    }
                </style>
                {{-- FilePond Input — config is passed via data-* so filepond.js can read them --}}
                {{-- NOTE: No `accept` attribute here. FilePond maps the HTML accept attr directly
                     to acceptedFileTypes and uses it in ALLOW_HOPPER_ITEM *without* the custom
                     type detector, which causes valid files to be rejected. Type validation is
                     handled entirely in JS via fileValidateTypeDetectType. --}}
                <input type="file"
                       x-ref="input"
                       id="{{ $inputId }}"
                       data-max-files="{{ $maxFiles }}"
                       data-max-size-mb="{{ $maxSizeMb }}"
                       data-process-url="{{ route('upload.temp') }}"
                       data-revert-url="{{ route('upload.temp.delete') }}"
                       data-restore-url="{{ route('upload.temp.restore', '') }}/"
                       multiple />
            </div>
        </div>
    </div>
</div>
