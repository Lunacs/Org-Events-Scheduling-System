<?php

namespace App\Livewire\Gso;

use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

class Profile extends Component
{
    use Toast;

    #[Title('Profile - GSO')]
    #[Layout('components.layouts.gso-layout')]

    // User instance
    public $user;

    // Profile Information
    public $name;

    public $email;

    public $phone;

    public $office;

    public $pending_email;

    // Password Change
    public $current_password;

    public $new_password;

    public $new_password_confirmation;

    // Preferences
    public $email_notifications = true;

    public $ticket_updates = true;

    public function mount()
    {
        $this->user = Auth::user();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone ?? '';
        $this->office = $this->user->office->office_name ?? 'N/A';
        $this->pending_email = $this->user->pending_email;

        // Load notification preferences
        $this->email_notifications = $this->user->getNotificationPreference('email_notifications', true);
        $this->ticket_updates = $this->user->getNotificationPreference('ticket_updates', true);
    }

    /**
     * Listen for avatar updates and refresh user data
     */
    #[On('avatar-updated')]
    public function refreshUser(): void
    {
        $this->user = Auth::user()->fresh();
    }

    public function updateProfile()
    {
        try {
            $this->validate([
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:255',
                    'regex:/^[a-zA-Z\s\-\.]+$/',
                ],
                'email' => [
                    'required',
                    'email:rfc,dns',
                    'max:255',
                    'unique:users,email,'.Auth::id().',user_id',
                ],
                'phone' => [
                    'nullable',
                    'string',
                    'regex:/^(\+63|0)?[9]\d{9}$/',
                ],
            ], [
                'name.required' => 'Full name is required.',
                'name.min' => 'Full name must be at least 2 characters.',
                'name.max' => 'Full name must not exceed 255 characters.',
                'name.regex' => 'Full name may only contain letters, spaces, hyphens, and periods.',
                'email.required' => 'Email address is required.',
                'email.email' => 'Please provide a valid email address.',
                'email.unique' => 'This email address is already in use.',
                'phone.regex' => 'Please provide a valid Philippine mobile number (e.g., 09123456789 or +639123456789).',
            ]);

            $user = Auth::user();
            $emailChanged = $this->email !== $user->email;

            // Update name and phone immediately
            $user->update([
                'name' => $this->name,
                'phone' => $this->phone,
            ]);

            // Handle email change separately with verification
            if ($emailChanged) {
                $user->requestEmailChange($this->email);
                $this->email = $user->email; // Revert to current email
                $this->pending_email = $user->fresh()->pending_email;
                $this->success('A verification link has been sent to your new email address.', position: 'toast-top');
            } else {
                $this->success('Profile updated successfully!', position: 'toast-top');
            }

            $this->dispatch('avatar-updated');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to update profile: '.$e->getMessage());
            $this->error('Failed to update profile. Please try again.', position: 'toast-top');
        }
    }

    public function cancelEmailChange()
    {
        $user = Auth::user();
        $user->cancelEmailChange();
        $this->pending_email = null;
        $this->success('Email change has been cancelled.', position: 'toast-top');
    }

    public function updatePassword()
    {
        try {
            $this->validate([
                'current_password' => [
                    'required',
                    'current_password',
                ],
                'new_password' => [
                    'required',
                    'confirmed',
                    'different:current_password',
                    Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised(),
                ],
            ], [
                'current_password.required' => 'Current password is required.',
                'current_password.current_password' => 'The current password is incorrect.',
                'new_password.required' => 'New password is required.',
                'new_password.confirmed' => 'Password confirmation does not match.',
                'new_password.different' => 'New password must be different from current password.',
            ]);

            $user = Auth::user();
            $user->update([
                'password' => Hash::make($this->new_password),
            ]);

            // Reset password fields
            $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

            $this->success('Password updated successfully!', position: 'toast-top');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to update password: '.$e->getMessage());
            $this->error('Failed to update password. Please try again.', position: 'toast-top');
        }
    }

    public function updatePreferences()
    {
        $user = Auth::user();
        $user->update([
            'notification_preferences' => [
                'email_notifications' => (bool) $this->email_notifications,
                'ticket_updates' => (bool) $this->ticket_updates,
            ],
        ]);

        $this->dispatch('preferences-updated');
        $this->success('Preferences updated successfully!', position: 'toast-top');
    }

    public function render()
    {
        return view('livewire.gso.profile');
    }
}
