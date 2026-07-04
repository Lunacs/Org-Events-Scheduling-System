import * as FilePond from 'filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';

// Import FilePond styles
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';

// Register plugins once at module level.
// IMPORTANT: ImageExifOrientation must be registered BEFORE ImagePreview
// so it can correct EXIF rotation data before the preview is rendered.
FilePond.registerPlugin(
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize,
    FilePondPluginImageExifOrientation,
    FilePondPluginImagePreview,
);

/**
 * HOW FILEPOND TYPE VALIDATION WORKS (plugin v1.2.9 source):
 *
 * There are TWO validation hooks:
 *
 *  1. ALLOW_HOPPER_ITEM  — fires when file is first added (drop / browse).
 *     Uses validateFile(file, acceptedTypes) WITHOUT the custom typeDetector.
 *     Checks file.type (browser MIME) against acceptedTypes.
 *
 *  2. LOAD_FILE — fires just before the item is loaded.
 *     Uses validateFile(file, acceptedTypes, typeDetector).
 *     The custom typeDetector IS used here.
 *
 * Because ALLOW_HOPPER_ITEM skips the detector, we MUST NOT rely on the
 * detector alone. Instead we use `image/*` wildcards in acceptedTypes so
 * that the hopper check uses mimeTypeMatchesWildCard() and passes for any
 * image/... MIME type the browser reports.
 *
 * We also set fileValidateTypeDetectType so that LOAD_FILE falls back to
 * extension-based detection when the browser reports an empty file.type.
 *
 * CRITICAL: The HTML <input accept="..."> attribute is mapped directly to
 * acceptedFileTypes by the plugin (SET_ATTRIBUTE_TO_OPTION_MAP filter).
 * That's why we removed the `accept` attribute from the Blade input — to
 * prevent the specific MIME list (without wildcards) from being merged in
 * and overriding our wildcard-based config.
 */

// Accepted types — uses wildcards so ALLOW_HOPPER_ITEM passes via
// mimeTypeMatchesWildCard() without needing the custom detector.
const ACCEPTED_MIME_TYPES = [
    'image/*',                 // covers image/jpeg, image/png, image/jpg, etc.
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
];

// Extension → MIME type for the LOAD_FILE fallback detector.
// Used when file.type is empty (can happen on drag-drop in some environments).
const EXT_TO_MIME = {
    pdf:  'application/pdf',
    doc:  'application/msword',
    docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    jpg:  'image/jpeg',
    jpeg: 'image/jpeg',
    png:  'image/png',
    xls:  'application/vnd.ms-excel',
    xlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
};

// Human-readable labels for the error message "Expects … or …"
const TYPE_LABEL_MAP = {
    'image/*':            '.jpg / .jpeg / .png',
    'application/pdf':    '.pdf',
    'application/msword': '.doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document': '.docx',
    'application/vnd.ms-excel': '.xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': '.xlsx',
};

/**
 * Custom type detector — called by LOAD_FILE with the typeDetector argument.
 * When file.type is empty (some OS/browser combos on drag-drop), infer the
 * MIME type from the file extension so the LOAD_FILE check still passes.
 * When file.type is present, pass it through unchanged.
 */
function detectFileType(file, browserType) {
    return new Promise((resolve, reject) => {
        if (browserType) {
            // Browser provided a type — trust it; ALLOW_HOPPER_ITEM already passed.
            resolve(browserType);
            return;
        }
        // Fallback: infer from extension
        const ext = file.name.split('.').pop().toLowerCase();
        const mime = EXT_TO_MIME[ext];
        if (mime) {
            resolve(mime);
        } else {
            reject();
        }
    });
}

function registerFilePondComponent() {
    window.Alpine.data('filepondComponent', (wireModelData) => ({
        pond: null,
        uploadedFileIds: wireModelData,

        init() {
            this.$nextTick(() => this.initFilePond());
        },

        initFilePond() {
            if (this.pond) {
                return; // already initialised
            }

            const input = this.$refs.input;
            if (!input) {
                return;
            }

            const maxFiles = parseInt(input.dataset.maxFiles) || 25;

            const initialFiles = Array.isArray(this.uploadedFileIds)
                ? this.uploadedFileIds.map(id => ({
                    source: id,
                    options: { type: 'limbo' } // 'limbo' triggers the restore endpoint
                }))
                : [];

            this.pond = FilePond.create(input, {
                files: initialFiles,
                allowMultiple: true,
                maxFiles,
                maxFileSize: (input.dataset.maxSizeMb || '10') + 'MB',

                // Wildcard-based list so ALLOW_HOPPER_ITEM passes for images.
                // Extensions are NOT supported in acceptedFileTypes (plugin v1.2.9).
                acceptedFileTypes: ACCEPTED_MIME_TYPES,

                // Fallback detector for LOAD_FILE when file.type is empty.
                fileValidateTypeDetectType: detectFileType,

                // Human-readable labels in the "File is of invalid type" error.
                fileValidateTypeLabelExpectedTypesMap: TYPE_LABEL_MAP,

                credits: false,

                server: {
                    process: {
                        url: input.dataset.processUrl,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            'Accept': 'application/json',
                        },
                        onload: (response) => {
                            const id = response;
                            if (!Array.isArray(this.uploadedFileIds)) {
                                this.uploadedFileIds = [];
                            }
                            this.uploadedFileIds.push(id);
                            return id;
                        },
                        onerror: (response) => {
                            try {
                                const data = JSON.parse(response);
                                if (data.errors && data.errors.filepond) {
                                    return data.errors.filepond[0].replace('The filepond ', 'The file ');
                                }
                                return data.message || 'Error during upload';
                            } catch (e) {
                                return 'Error during upload';
                            }
                        },
                    },
                    revert: {
                        url: input.dataset.revertUrl,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            'Accept': 'application/json',
                        },
                        onload: (id) => {
                            if (Array.isArray(this.uploadedFileIds)) {
                                this.uploadedFileIds = this.uploadedFileIds.filter((item) => item !== id);
                            }
                        },
                    },
                    restore: input.dataset.restoreUrl,
                },

                labelFileProcessingError: (error) => {
                    return error.body || error || 'Error during upload';
                },

                labelIdle: `<div class="flex flex-col items-center justify-center gap-1 py-4">
                    <x-mary-icon name="o-arrow-up-tray" class="w-8 h-8 text-base-content/40 mb-2" />
                    <p class="text-base sm:text-lg font-bold text-base-content">
                        Choose a file or drag & drop
                    </p>
                    <p class="text-xs sm:text-sm text-base-content/50 mb-4">
                        JPEG, PNG, PDF, and format, up to ${input.dataset.maxSizeMb || '10'}MB
                    </p>
                    <span class="filepond--label-action btn btn-outline btn-sm bg-base-100">Browse File</span>
                </div>`.replace('<x-mary-icon name="o-arrow-up-tray" class="w-8 h-8 text-base-content/40 mb-2" />', '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-base-content/40 mb-2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>'),
            });
        },

        destroy() {
            if (this.pond) {
                this.pond.destroy();
                this.pond = null;
            }
        },
    }));
}

// Ensure component is registered regardless of Vite loading order vs Livewire v3 Alpine injection
if (window.Alpine) {
    registerFilePondComponent();
} else {
    document.addEventListener('alpine:init', registerFilePondComponent);
}
