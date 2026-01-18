<?php

namespace App\Livewire\Osa;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class Profile extends Component
{
    use Toast;

    #[Title('Profile - OSA Admin')]
    #[Layout('components.layouts.app')]

    // User instance
    public $user;

    // Profile Information
    public $name;

    public $email;

    public $phone;

    public $department;

    // Password Change
    public $current_password;

    public $new_password;

    public $new_password_confirmation;

    // Preferences
    public $email_notifications = true;

    public $ticket_updates = true;

    public $weekly_reports = false;

    public function mount()
    {
        $this->user = Auth::user();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone ?? '';
        $this->department = 'Office of Student Affairs';
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
                'unique:users,email,' . Auth::id() . ',user_id',
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
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->success('Profile updated successfully!', position: 'toast-top');
        $this->dispatch('avatar-updated');
    }

    public function updatePassword()
    {
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
    }

    public function updatePreferences()
    {
        // In a real app, you'd save these to a preferences table
        $this->success('Preferences updated successfully!', position: 'toast-top');
    }

    public function render()
    {
        return view('livewire.osa.profile');
    }
}
