<?php

namespace App\Livewire;

use App\Traits\WithProfilePhoto;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class AvatarSelector extends Component
{
    use Toast;
    use WithProfilePhoto;

    public $selectedStyle;

    public $selectedSeed;

    protected const AVAILABLE_STYLES = [
        'big-ears' => 'Big Ears',
    ];

    protected const AVATAR_SEEDS = [
        'felix',
        'aneka',
        'bob',
        'charlie',
        'david',
        'emma',
        'frank',
        'grace',
        'hannah',
        'ivan',
        'julia',
        'kevin',
        'laura',
        'mike',
        'nina',
        'oliver',
        'peter',
        'quinn',
        'rachel',
        'sam',
        'tina',
        'uma',
        'victor',
        'wendy',
    ];

    protected const DEFAULT_STYLE = 'big-ears';
    protected const DEFAULT_SEED = 'felix';

    public function mount()
    {
        $user = Auth::user();
        $this->selectedStyle = $user->avatar_style ?? self::DEFAULT_STYLE;
        $this->selectedSeed = $user->avatar_seed ?? self::DEFAULT_SEED;
    }

    public function getAvatarOptionsProperty()
    {
        // Generate flat array of avatar options
        $options = [];

        foreach (self::AVAILABLE_STYLES as $styleId => $styleName) {
            foreach (self::AVATAR_SEEDS as $seed) {
                $options[] = [
                    'style' => $styleId,
                    'seed' => $seed,
                    'id' => "{$styleId}-{$seed}",
                ];
            }
        }

        return $options;
    }

    /**
     * When a photo is uploaded, validate it but don't save yet.
     * Just show a preview and wait for explicit save.
     */
    public function updatedPhoto()
    {
        // Only validate the photo, don't save yet
        $this->validate(
            ['photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240']],
            [
                'photo.image' => 'The file must be an image.',
                'photo.mimes' => 'The image must be a JPG, PNG, or WebP file.',
                'photo.max' => 'The image must not exceed 10MB.',
            ]
        );
        // Photo preview will be shown automatically via $photo->temporaryUrl()

        // Dispatch event to trigger avatar re-initialization
        $this->dispatch('photo-uploaded');
    }

    /**
     * Cancel the photo upload and clear the preview.
     */
    public function cancelPhotoUpload()
    {
        $this->photo = null;
    }

    /**
     * Save the uploaded photo and switch to using it.
     */
    public function saveUploadedPhoto()
    {
        if (!$this->photo) {
            $this->error('No photo to save.', position: 'toast-top');
            return;
        }

        $this->saveProfilePhoto();
        $this->dispatch('avatar-updated');
        $this->js('window.dispatchEvent(new CustomEvent("avatar-changed"))');
    }

    /**
     * Switch to using the uploaded photo as avatar.
     */
    public function useUploadedPhoto()
    {
        $user = Auth::user();

        if (!$user->avatar) {
            $this->error('No uploaded photo found.', position: 'toast-top');
            return;
        }

        $user->update(['avatar_preference' => 'uploaded']);

        $this->success('Now using your uploaded photo!', position: 'toast-top');
        $this->dispatch('avatar-updated');
        $this->js('window.dispatchEvent(new CustomEvent("avatar-changed"))');
    }

    public function saveAvatar($style, $seed)
    {
        // Validate inputs
        if (!isset(self::AVAILABLE_STYLES[$style]) || !in_array($seed, self::AVATAR_SEEDS, true)) {
            $this->error('Invalid avatar selection.', position: 'toast-top');
            return;
        }

        $user = Auth::user();

        $user->update([
            'avatar_style' => $style,
            'avatar_seed' => $seed,
            'avatar_preference' => 'dicebear', // Switch to DiceBear avatar
        ]);

        // Update local state to reflect saved values
        $this->selectedStyle = $style;
        $this->selectedSeed = $seed;

        $this->success('Avatar updated successfully!', position: 'toast-top');

        // Dispatch event to all navigation components (including Volt components)
        $this->dispatch('avatar-updated');

        // Also dispatch a global browser event for JavaScript
        $this->js('window.dispatchEvent(new CustomEvent("avatar-changed"))');
    }

    public function render()
    {
        return view('livewire.avatar-selector', [
            'avatarOptions' => $this->avatarOptions,
            'user' => Auth::user(),
        ]);
    }
}
