<div x-data="{
    selectedStyle: '{{ $selectedStyle }}',
    selectedSeed: '{{ $selectedSeed }}',
    currentUserStyle: '{{ auth()->user()->avatar_style ?? 'big-ears' }}',
    currentUserSeed: '{{ auth()->user()->avatar_seed ?? auth()->user()->email }}',

    selectAvatar(style, seed) {
        this.selectedStyle = style;
        this.selectedSeed = seed;

        // Reinitialize avatar preview
        setTimeout(() => {
            if (window.AvatarHelper) {
                const previewImg = document.querySelector('[data-avatar-preview]');
                if (previewImg) {
                    previewImg.dataset.initialized = 'false';
                    window.AvatarHelper.initAvatars();
                }
            }
        }, 10);
    },

    isSelected(style, seed) {
        return this.selectedStyle === style && this.selectedSeed === seed;
    },

    isUnchanged() {
        return this.selectedStyle === this.currentUserStyle &&
            this.selectedSeed === this.currentUserSeed;
    },

    saveAvatar() {
        $wire.saveAvatar(this.selectedStyle, this.selectedSeed).then(() => {
            // Update current user values after save
            this.currentUserStyle = this.selectedStyle;
            this.currentUserSeed = this.selectedSeed;
        });
    }
}">
    <x-mary-card title="Select Your Avatar" subtitle="Choose from {{ count($avatarOptions) }} unique avatars">
        <div class="space-y-6">
            {{-- Current Avatar Preview --}}
            <div class="flex flex-col items-center gap-4 p-6 bg-base-200 rounded-lg">
                <div class="text-center">
                    <p class="text-sm text-base-content/70 mb-3">Current Avatar</p>
                    <div class="avatar placeholder">
                        <div
                            class="bg-base-300 rounded-full w-32 h-32 ring-4 ring-primary ring-offset-2 ring-offset-base-100">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'%3E%3C/svg%3E"
                                data-avatar="dicebear:{{ $selectedStyle }}:{{ $selectedSeed }}" data-avatar-preview
                                alt="Current avatar" class="rounded-full w-full h-full object-cover" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Avatar Gallery --}}
            <div>
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <span>Big Ears Style</span>
                    <span class="badge badge-neutral badge-sm">
                        from dicebear
                    </span>
                </h3>

                <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-8 gap-3">
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
                <span wire:loading.remove wire:target="saveAvatar">Save Avatar</span>
                <span wire:loading wire:target="saveAvatar">Saving...</span>
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
                    window.AvatarHelper.initAvatars();
                }
            });
        });
    </script>
</div>
