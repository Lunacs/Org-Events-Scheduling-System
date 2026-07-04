@props([
    'wireModel' => 'newAttachments',
    'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx',
    'inputId' => 'file-upload-dropzone',
    'constraintsId' => 'file-upload-constraints',
    'maxFiles' => 25,
    'maxSizeMb' => 10,
])

<div class="space-y-3">
    <div
        x-data="{
            isDragging: false,
            handleDragOver(event) {
                event.preventDefault();
                this.isDragging = true;
            },
            handleDragLeave() {
                this.isDragging = false;
            },
            handleDrop(event) {
                event.preventDefault();
                this.isDragging = false;

                const input = this.$refs.fileInput;
                if (! input || ! event.dataTransfer.files.length) {
                    return;
                }

                const dt = new DataTransfer();
                dt.items.add(event.dataTransfer.files[0]);
                input.files = dt.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            },
        }"
        class="relative"
    >
        <label
            for="{{ $inputId }}"
            x-on:dragover="handleDragOver($event)"
            x-on:dragleave="handleDragLeave()"
            x-on:drop="handleDrop($event)"
            x-bind:class="isDragging
                ? 'border-primary bg-primary/5 ring-2 ring-primary/20'
                : 'border-base-300 hover:border-primary/50 hover:bg-base-200/50'"
            class="flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed bg-base-100 px-4 py-8 sm:py-10 cursor-pointer transition-all duration-200 focus-within:ring-2 focus-within:ring-primary/30 focus-within:border-primary"
        >
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                <x-mary-icon name="o-cloud-arrow-up" class="h-6 w-6 text-primary" />
            </div>

            <div class="text-center">
                <p class="text-sm sm:text-base text-base-content">
                    <span class="font-medium text-primary">Click to upload</span>
                    <span class="text-base-content/70"> or drag and drop</span>
                </p>
                <p class="mt-1 text-xs text-base-content/60">
                    Add files one at a time — up to {{ $maxFiles }} files total
                </p>
            </div>

            <input
                id="{{ $inputId }}"
                x-ref="fileInput"
                type="file"
                wire:model="{{ $wireModel }}"
                accept="{{ $accept }}"
                aria-describedby="{{ $constraintsId }}"
                aria-label="Upload event documents"
                class="sr-only"
            />
        </label>

        <div
            wire:loading.flex
            wire:target="{{ $wireModel }}"
            class="absolute inset-0 hidden items-center justify-center rounded-xl bg-base-100/80 backdrop-blur-sm"
        >
            <div class="flex flex-col items-center gap-2">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="text-sm font-medium text-base-content">Uploading file…</p>
            </div>
        </div>
    </div>

    <div id="{{ $constraintsId }}" class="rounded-lg border-l-4 border-info bg-info/10 p-4">
        <div class="flex items-start gap-2">
            <x-mary-icon name="s-information-circle" class="mt-0.5 h-5 w-5 shrink-0 text-info" />
            <div class="text-sm text-base-content/80">
                <p class="font-medium text-base-content">File requirements</p>
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    <li>Accepted: PDF, DOC/DOCX, JPG/PNG, XLS/XLSX</li>
                    <li>Maximum {{ $maxSizeMb }} MB per file</li>
                    <li>Up to {{ $maxFiles }} files per ticket</li>
                </ul>
            </div>
        </div>
    </div>
</div>
