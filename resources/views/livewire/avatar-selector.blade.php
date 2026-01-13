<div wire:key="avatar-selector-{{ $user->avatar_preference ?? 'dicebear' }}-{{ $this->getProfilePhotoUrl() ? 'photo' : 'no-photo' }}"
    x-data="{
        selectedStyle: '{{ $selectedStyle }}',
        selectedSeed: '{{ $selectedSeed }}',
        currentUserStyle: '{{ auth()->user()->avatar_style ?? 'big-ears' }}',
        currentUserSeed: '{{ auth()->user()->avatar_seed ?? auth()->user()->email }}',
        currentUserPreference: '{{ auth()->user()->avatar_preference ?? 'dicebear' }}',
        hasUploadedPhoto: {{ $this->getProfilePhotoUrl() ? 'true' : 'false' }},
        selectedType: '{{ $user->avatar_preference ?? 'dicebear' }}', // 'uploaded' or 'dicebear'
    
        selectAvatar(style, seed) {
            this.selectedStyle = style;
            this.selectedSeed = seed;
            this.selectedType = 'dicebear'; // Mark as DiceBear selection
    
            // Reinitialize avatar preview after DOM updates
            this.$nextTick(() => {
                if (window.AvatarHelper) {
                    const previewImg = document.querySelector('[data-avatar-preview]');
                    if (previewImg) {
                        previewImg.dataset.avatar = `dicebear:${style}:${seed}`;
                        previewImg.dataset.initialized = 'false';
                        window.AvatarHelper.initAvatars();
                    }
                }
            });
        },
    
        selectUploadedPhoto() {
            this.selectedType = 'uploaded';
            // Don't save yet, just update the preview
        },
    
        isSelected(style, seed) {
            // Only show as selected if using DiceBear and matches current selection
            return this.selectedType === 'dicebear' &&
                this.selectedStyle === style &&
                this.selectedSeed === seed;
        },
    
        isUnchanged() {
            // Check if current selection matches what's saved in database
            if (this.selectedType === 'uploaded') {
                return this.currentUserPreference === 'uploaded';
            }
            // For DiceBear, check style and seed
            return this.currentUserPreference === 'dicebear' &&
                this.selectedStyle === this.currentUserStyle &&
                this.selectedSeed === this.currentUserSeed;
        },
    
        saveAvatar() {
            if (this.selectedType === 'uploaded') {
                $wire.useUploadedPhoto().then(() => {
                    this.currentUserPreference = 'uploaded';
                });
            } else {
                $wire.saveAvatar(this.selectedStyle, this.selectedSeed).then(() => {
                    // Update current user values after save
                    this.currentUserStyle = this.selectedStyle;
                    this.currentUserSeed = this.selectedSeed;
                    this.currentUserPreference = 'dicebear';
                });
            }
        }
    }">
    <x-mary-card title="Select Your Avatar" subtitle="Choose from {{ count($avatarOptions) }} unique avatars">
        <div class="space-y-6">
            {{-- Current Avatar Preview --}}
            <div class="flex flex-col items-center gap-4 p-6 bg-base-200 rounded-lg">
                <div class="text-center">
                    <p class="text-sm text-base-content/70 mb-3">Current Avatar</p>

                    {{-- Avatar Display --}}
                    <div class="relative inline-block group">
                        @if (!$this->getProfilePhotoUrl())
                            {{-- Only show upload option if user has NO uploaded photo --}}
                            <input type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp"
                                class="hidden" id="avatar-photo-upload" />
                        @endif

                        {{-- Avatar Container - clickable only if no photo uploaded yet --}}
                        @if (!$this->getProfilePhotoUrl())
                            <label for="avatar-photo-upload" class="cursor-pointer block">
                            @else
                                <div>
                        @endif
                        <div class="avatar placeholder">
                            <div
                                class="bg-base-300 rounded-full w-32 h-32 ring-4 ring-primary ring-offset-2 ring-offset-base-100 relative overflow-hidden">
                                @if ($photo)
                                    {{-- Temporary upload preview --}}
                                    <img src="{{ $photo->temporaryUrl() }}" alt="Upload preview"
                                        class="rounded-full w-full h-full object-cover" />
                                @else
                                    {{-- Uploaded profile photo (shown when selectedType is 'uploaded') --}}
                                    @if ($this->getProfilePhotoUrl())
                                        <img x-show="selectedType === 'uploaded'"
                                            src="{{ $this->getProfilePhotoUrl() }}" alt="Profile photo"
                                            class="rounded-full w-full h-full object-cover" />
                                    @endif
                                    {{-- DiceBear Avatar (shown when selectedType is 'dicebear' or no photo) --}}
                                    <img x-show="selectedType === 'dicebear' || !{{ $this->getProfilePhotoUrl() ? 'true' : 'false' }}"
                                        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'%3E%3C/svg%3E"
                                        :data-avatar="`dicebear:${selectedStyle}:${selectedSeed}`" data-avatar-preview
                                        alt="Current avatar" class="rounded-full w-full h-full object-cover" />
                                @endif

                                {{-- Camera Overlay - only show if no photo exists and not showing preview --}}
                                @if (!$this->getProfilePhotoUrl() && !$photo)
                                    <div
                                        class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-full">
                                        <i class="fa-solid fa-camera text-white text-2xl"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if (!$this->getProfilePhotoUrl())
                            </label>
                        @else
                    </div>
                    @endif

                    {{-- Loading indicator --}}
                    <div wire:loading wire:target="photo"
                        class="absolute inset-0 flex items-center justify-center bg-black/60 rounded-full">
                        <span class="loading loading-spinner loading-lg text-white"></span>
                    </div>
                </div>

                {{-- Upload hint or Preview actions --}}
                @if ($photo)
                    <div class="flex gap-2 mt-3 justify-center">
                        <x-mary-button wire:click="saveUploadedPhoto" icon="o-check" class="btn-success btn-sm">
                            <span wire:loading.remove wire:target="saveUploadedPhoto">Save Photo</span>
                            <span wire:loading wire:target="saveUploadedPhoto">Saving...</span>
                        </x-mary-button>
                        <x-mary-button wire:click="cancelPhotoUpload" icon="o-x-mark" class="btn-ghost btn-sm">
                            Cancel
                        </x-mary-button>
                    </div>
                    <p class="text-xs text-info mt-2">Preview - Click Save to keep this photo</p>
                @elseif (!$this->getProfilePhotoUrl())
                    {{-- Only show upload hint if no photo exists --}}
                    <p class="text-xs text-base-content/50 mt-2">Click to upload photo</p>
                @endif

                @error('photo')
                    <p class="text-error text-sm mt-2">{{ $message }}</p>
                @enderror

                {{-- Delete button - only show when uploaded photo is selected in the grid --}}
                @if ($this->getProfilePhotoUrl())
                    <div x-show="selectedType === 'uploaded'" x-cloak>
                        <x-mary-button wire:click="deleteProfilePhoto" icon="o-trash"
                            class="btn-error btn-outline btn-xs mt-3"
                            wire:confirm="Are you sure you want to delete your profile photo?">
                            Remove Photo
                        </x-mary-button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Avatar Gallery --}}
        <div>
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span>Choose Your Avatar</span>
                <span class="badge badge-neutral badge-sm">
                    <a href="https://www.dicebear.com/" target="_blank"
                        class="underline decoration-transparent transition-all duration-300 ease-in-out hover:decoration-current">
                        from dicebear
                    </a>

                </span>
            </h3>

            <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-8 gap-3">
                {{-- Uploaded Photo as first option - clicking only selects, doesn't save --}}
                @if ($this->getProfilePhotoUrl())
                    <div @click="selectUploadedPhoto()" class="cursor-pointer group">
                        <div class="relative">
                            <div :class="`avatar w-full transition-all duration-200 ${
                                                                                        selectedType === 'uploaded'
                                                                                            ? 'ring-4 ring-primary ring-offset-2 ring-offset-base-100 scale-110'
                                                                                            : 'hover:ring-2 hover:ring-base-300 hover:scale-105'
                                                                                    }`"
                                title="Your uploaded photo">
                                <div class="rounded-full w-full aspect-square">
                                    <img src="{{ $this->getProfilePhotoUrl() }}" alt="Your photo"
                                        class="rounded-full w-full h-full object-cover" />
                                </div>
                            </div>
                            <template x-if="selectedType === 'uploaded'">
                                <div
                                    class="absolute -top-1 -right-1 bg-primary text-primary-content rounded-full p-1 shadow-lg">
                                    <x-mary-icon name="o-check" class="w-4 h-4" />
                                </div>
                            </template>
                        </div>
                    </div>
                @endif

                {{-- DiceBear Avatars --}}
                @foreach ($avatarOptions as $option)
                    <div @click="selectAvatar('{{ $option['style'] }}', '{{ $option['seed'] }}')"
                        class="cursor-pointer group">
                        <div class="relative">
                            <div :class="`avatar placeholder w-full transition-all duration-200 ${
                                                                                        isSelected('{{ $option['style'] }}', '{{ $option['seed'] }}')
                                                                                            ? 'ring-4 ring-primary ring-offset-2 ring-offset-base-100 scale-110'
                                                                                            : 'hover:ring-2 hover:ring-base-300 hover:scale-105'
                                                                                    }`"
                                :title="'{{ $option['seed'] }}'.charAt(0).toUpperCase() + '{{ $option['seed'] }}'.slice(1)">
                                <div class="rounded-full w-full aspect-square bg-base-200">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'%3E%3C/svg%3E"
                                        data-avatar="dicebear:{{ $option['style'] }}:{{ $option['seed'] }}"
                                        alt="Avatar option" class="rounded-full w-full h-full object-cover" />
                                </div>
                            </div>
                            <template x-if="isSelected('{{ $option['style'] }}', '{{ $option['seed'] }}')">
                                <div
                                    class="absolute -top-1 -right-1 bg-primary text-primary-content rounded-full p-1 shadow-lg">
                                    <x-mary-icon name="o-check" class="w-4 h-4" />
                                </div>
                            </template>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
</div>

{{-- Save Button --}}
<x-slot:actions>
    <x-mary-button @click="saveAvatar()" class="btn-primary btn-block sm:btn-wide" icon="o-check"
        x-bind:disabled="isUnchanged()">
        <span wire:loading.remove wire:target="saveAvatar,useUploadedPhoto">Save Avatar</span>
        <span wire:loading wire:target="saveAvatar,useUploadedPhoto">Saving...</span>
    </x-mary-button>
</x-slot:actions>
</x-mary-card>

{{-- Reinitialize avatars --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.AvatarHelper) {
            window.AvatarHelper.initAvatars();
        }
    });

    document.addEventListener('livewire:init', () => {
        // Initialize on first load
        if (window.AvatarHelper) {
            window.AvatarHelper.initAvatars();
        }

        // Reinitialize after any Livewire update
        Livewire.hook('morph.updated', ({
            el,
            component
        }) => {
            if (window.AvatarHelper) {
                // Clear initialized flags for faster re-render
                document.querySelectorAll('[data-avatar]').forEach(img => {
                    img.dataset.initialized = 'false';
                });
                // Use setTimeout to ensure DOM is fully updated
                setTimeout(() => {
                    window.AvatarHelper.initAvatars();
                }, 100);
            }
        });

        // Listen for photo upload event and force re-init
        Livewire.on('photo-uploaded', () => {
            setTimeout(() => {
                if (window.AvatarHelper) {
                    document.querySelectorAll('[data-avatar]').forEach(img => {
                        img.dataset.initialized = 'false';
                    });
                    window.AvatarHelper.initAvatars();
                }
            }, 150);
        });
    });
</script>
</div>
