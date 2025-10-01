<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public function mount()
    {
        // Registration is not available for public users
        // Only SuperAdmin can create OSA/GSO accounts
        // Only OSA can create Student Org accounts
        return redirect()->route('admin.login')->with('error', 'Public registration is not available. Please contact your administrator to create an account.');
    }
}; ?>

<div>
    <div class="text-center py-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Registration Not Available</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Public registration is disabled. Accounts are created by administrators only.
        </p>
        <div class="space-y-2">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                • SuperAdmin creates OSA and GSO accounts
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                • OSA creates Student Organization accounts
            </p>
        </div>
        <div class="mt-6">
            <a href="{{ route('admin.login') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                wire:navigate>
                Go to Login
            </a>
        </div>
    </div>
</div>
