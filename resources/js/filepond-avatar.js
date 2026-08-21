import * as FilePond from 'filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';
import Cropper from 'cropperjs';

// Import styles
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
import 'cropperjs/dist/cropper.css';

// Register plugins
FilePond.registerPlugin(
    FilePondPluginImageExifOrientation,
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize,
    FilePondPluginImagePreview,
    FilePondPluginImageResize,
    FilePondPluginImageTransform,
);

// Expose Cropper globally for Alpine component usage
window.Cropper = Cropper;
window.FilePond = FilePond;

/**
 * Avatar-specific FilePond Alpine.js component.
 */
function registerFilepondAvatarComponent() {
    window.Alpine.data('filepondAvatarComponent', () => ({
        pond: null,

        init() {
            this.$nextTick(() => this.initFilePond());
            
            // Listen for reset events from Livewire
            this.$wire.on('clear-avatar-upload', () => {
                this.clearPond();
            });

            // Listen for browse triggers from parent click
            window.addEventListener('trigger-avatar-browse', () => {
                if (this.pond) {
                    this.pond.browse();
                }
            });
        },

        initFilePond() {
            if (this.pond) {
                return;
            }

            const input = this.$refs.avatarInput;
            if (!input) {
                return;
            }

            const processUrl = input.dataset.processUrl;
            const revertUrl = input.dataset.revertUrl;
            const $wire = this.$wire;
            const component = this;

            this.pond = FilePond.create(input, {
                allowMultiple: false,
                maxFiles: 1,
                maxFileSize: '5MB',
                name: 'avatar',

                // Only accept images
                acceptedFileTypes: ['image/jpeg', 'image/png', 'image/webp'],

                // Client-side resize before upload
                allowImageResize: true,
                imageResizeTargetWidth: 500,
                imageResizeTargetHeight: 500,
                imageResizeMode: 'cover',
                imageResizeUpscale: false,

                // Transform: apply resize + convert to WebP
                allowImageTransform: true,
                imageTransformOutputMimeType: 'image/webp',
                imageTransformOutputQuality: 80,
                imageTransformOutputStripImageHead: true,

                // Enable circular image preview
                allowImagePreview: true,
                imagePreviewHeight: 128,
                imagePreviewMinHeight: 128,
                imagePreviewMaxHeight: 128,
                
                stylePanelLayout: 'compact circle',
                styleLoadIndicatorPosition: 'center center',
                styleProgressIndicatorPosition: 'center center',
                styleButtonRemoveItemPosition: 'left bottom',
                styleButtonProcessItemPosition: 'right bottom',

                credits: false,

                // Track upload status
                onprocessfilestart: () => {
                    if (component.$parent) {
                        component.$parent.isUploading = true;
                    }
                },
                onprocessfileabort: () => {
                    if (component.$parent) {
                        component.$parent.isUploading = false;
                    }
                },
                onaddfile: (error, file) => {
                    if (error) {
                        return;
                    }
                    
                    // If file is already cropped, let it pass
                    if (file.getMetadata('isCropped')) {
                        if (component.$parent) {
                            component.$parent.hasFile = true;
                        }
                        return;
                    }

                    // Otherwise, intercept and remove it to open crop modal
                    const originalFile = file.file;
                    component.pond.removeFile(file.id);

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        window.dispatchEvent(new CustomEvent('open-crop-modal', {
                            detail: {
                                imageSrc: e.target.result,
                                file: originalFile
                            }
                        }));
                    };
                    reader.readAsDataURL(originalFile);
                },
                onremovefile: () => {
                    if (component.$parent) {
                        component.$parent.hasFile = false;
                        component.$parent.isUploading = false;
                    }
                    $wire.clearUploadedAvatar();
                },

                server: {
                    process: {
                        url: processUrl,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            'Accept': 'application/json',
                        },
                        onload: (response) => {
                            if (component.$parent) {
                                component.$parent.isUploading = false;
                            }
                            const path = response;
                            $wire.setUploadedAvatarPath(path);
                            return path;
                        },
                        onerror: (response) => {
                            if (component.$parent) {
                                component.$parent.isUploading = false;
                            }
                            let message = 'Upload failed';
                            try {
                                const data = JSON.parse(response);
                                message = data.errors?.avatar?.[0] || data.message || message;
                            } catch (e) {}

                            // Dispatch global toast event
                            window.dispatchEvent(new CustomEvent('toast-show', {
                                detail: {
                                    type: 'error',
                                    message: message,
                                    position: 'toast-top toast-end',
                                    icon: 'o-x-circle',
                                    css: 'alert-error',
                                    timeout: 3000
                                }
                            }));
                            return message;
                        },
                    },
                    revert: {
                        url: revertUrl,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            'Accept': 'application/json',
                        },
                        onload: () => {
                            $wire.clearUploadedAvatar();
                        },
                    },
                },
            });
        },

        clearPond() {
            if (this.pond) {
                this.pond.removeFiles();
            }
        },

        destroy() {
            if (this.pond) {
                this.pond.destroy();
                this.pond = null;
            }
        },
    }));
}

// Register regardless of Alpine load order
if (window.Alpine) {
    registerFilepondAvatarComponent();
} else {
    document.addEventListener('alpine:init', registerFilepondAvatarComponent);
}
