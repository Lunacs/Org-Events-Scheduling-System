<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.guest')] class extends Component {
    public function mount()
    {
        // Registration is not available for public users
        // Only SuperAdmin can create OSA/GSO accounts
        // Only OSA can create Student Org accounts
        return redirect()->route('admin.login')->with('error', 'Public registration is not available. Please contact your administrator to create an account.');
    }
}; ?>

<div>
    <h1 class="text-2xl font-semibold tracking-[-0.015em] text-base-content">Registration is not available</h1>
    <p class="mt-2 text-sm text-base-content/70">
        Accounts are created by the university: the SuperAdmin creates OSA and GSO accounts, and OSA creates
        student organization accounts. Contact OSA if your organization needs access.
    </p>
    <a href="{{ route('login') }}" wire:navigate class="btn btn-primary btn-block mt-6">Go to sign in</a>
</div>
