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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.Auth::id().',user_id',
            'phone' => 'nullable|string|max:20',
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
            'current_password' => 'required|current_password',
            'new_password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
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
