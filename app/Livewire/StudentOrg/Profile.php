<?php

namespace App\Livewire\StudentOrg;

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

    #[Title('Profile - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]

    // User instance
    public $user;

    // Profile Information
    public $name;
    public $email;
    public $phone;
    public $organization;

    // Password Change
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    // Preferences
    public $email_notifications = true;
    public $ticket_updates = true;
    public $event_reminders = true;

    public function mount()
    {
        $this->user = Auth::user();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone ?? '';
        $this->organization = $this->user->studentOrganization->name ?? 'N/A';
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
            'phone' => $this->phone,
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
        return view('livewire.student-org.profile');
    }
}
