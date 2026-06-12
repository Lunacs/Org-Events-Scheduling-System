<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Trait for handling profile photo uploads with compression.
 * Used by Profile components across all user roles.
 */
trait WithProfilePhoto
{
    use WithFileUploads;

    /**
     * The uploaded photo file.
     */
    public $photo;

    /**
     * Validation rules for the photo upload.
     */
    protected function photoValidationRules(): array
    {
        return [
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120', // 5MB max
            ],
        ];
    }

    /**
     * Validation messages for photo upload.
     */
    protected function photoValidationMessages(): array
    {
        return [
            'photo.image' => 'The file must be an image.',
            'photo.mimes' => 'The image must be a JPG, PNG, or WebP file.',
            'photo.max' => 'The image must not exceed 5MB.',
        ];
    }

    /**
     * Save the uploaded profile photo with compression.
     * Compresses to 500x500 max dimensions and 80% quality.
     */
    public function saveProfilePhoto(): ?string
    {
        if (! $this->photo instanceof TemporaryUploadedFile) {
            return null;
        }

        $this->validate(
            $this->photoValidationRules(),
            $this->photoValidationMessages()
        );

        $user = Auth::user();

        // Delete old photo if exists
        $diskName = config('filesystems.default') === 's3' ? 's3' : 'public';
        if ($user->avatar && Storage::disk($diskName)->exists($user->avatar)) {
            Storage::disk($diskName)->delete($user->avatar);
        }

        // Generate unique filename
        $filename = 'profile-photos/'.$user->user_id.'_'.time().'.webp';

        // Read, resize, and compress the image
        // Use get() instead of getRealPath() because S3 temp files aren't local
        $image = Image::read($this->photo->get());

        // Resize to max 500x500 while maintaining aspect ratio
        $image->scaleDown(500, 500);

        // Encode as WebP with 80% quality for compression
        $encoded = $image->toWebp(80);

        // Store the compressed image
        $diskName = config('filesystems.default') === 's3' ? 's3' : 'public';
        Storage::disk($diskName)->put($filename, (string) $encoded);

        // Update user avatar and set preference to uploaded
        $user->update([
            'avatar' => $filename,
            'avatar_preference' => 'uploaded',
        ]);

        // Reset the photo property
        $this->photo = null;

        $this->success('Profile photo uploaded successfully!', position: 'toast-top');

        return $filename;
    }

    /**
     * Delete the current profile photo and revert to DiceBear avatar.
     */
    public function deleteProfilePhoto(): void
    {
        $user = Auth::user();

        // Actually delete the file
        $diskName = config('filesystems.default') === 's3' ? 's3' : 'public';
        if ($user->avatar && Storage::disk($diskName)->exists($user->avatar)) {
            Storage::disk($diskName)->delete($user->avatar);
        }

        // Clear avatar and set preference to dicebear
        $user->update([
            'avatar' => null,
            'avatar_preference' => 'dicebear',
        ]);

        $this->dispatch('avatar-updated');
        $this->success('Profile photo removed. Using avatar instead.', position: 'toast-top');
    }

    /**
     * Get the current profile photo URL or null.
     */
    public function getProfilePhotoUrl(): ?string
    {
        $user = Auth::user();
        $diskName = config('filesystems.default') === 's3' ? 's3' : 'public';
        $disk = Storage::disk($diskName);

        if ($user->avatar && $disk->exists($user->avatar)) {
            if ($diskName === 's3') {
                return $disk->temporaryUrl($user->avatar, now()->addMinutes(30));
            }

            return $disk->url($user->avatar);
        }

        return null;
    }
}
