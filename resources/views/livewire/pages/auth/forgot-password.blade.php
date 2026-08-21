<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.guest')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // Rate limiting key based on email
        $key = 'password-reset:' . strtolower($this->email);

        // Check if rate limit exceeded
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            Session::flash('error', 'Please wait ' . $seconds . ' seconds before requesting another password reset link.');
            return;
        }

        // SECURITY: Invalidate all previous reset tokens for this email
        // This ensures only the latest link is valid (prevents token backlog attacks)
        DB::table('password_reset_tokens')
            ->where('email', strtolower($this->email))
            ->delete();

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink($this->only('email'));

        // Hit the rate limiter (1 attempt allowed per 60 seconds)
        RateLimiter::hit($key, 60);

        $this->reset('email');

        // SECURITY: Always show a generic success message to prevent email enumeration
        // This prevents attackers from discovering which emails are registered
        session()->flash('status', __('If an account exists for this email address, you will receive a password reset link shortly.'));
    }
}; ?>

<div>
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-2">Reset Password</h1>
        <p class="text-base text-gray-500 dark:text-gray-400">
            {{ __('Enter your email address and we\'ll send you a link to reset your password.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-ui.auth-session-status class="mb-6" :status="session('status')" />

    @if (session('error'))
        <div
            class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm font-medium text-red-600 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-6">
        <!-- Email Address -->
        <div class="space-y-1">
            <label for="email" class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">Email
                Address</label>
            <div class="relative group">
                <div
                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors group-focus-within:text-accent">
                    <i class="fas fa-envelope text-gray-400 group-focus-within:text-accent transition-colors"></i>
                </div>
                <input wire:model="email" id="email" type="email" name="email" placeholder="name@plv.edu.ph"
                    required autofocus
                    class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-accent/30 focus:border-accent focus:bg-white dark:focus:bg-gray-900 transition-all duration-200 sm:text-sm" />
            </div>
            <x-ui.input-error :messages="$errors->get('email')" class="mt-1.5 ml-1" />
        </div>

        <div class="pt-2">
            <button type="submit" wire:loading.attr="disabled"
                class="relative w-full flex justify-center items-center py-3 px-6 border border-transparent rounded-xl shadow-lg shadow-primary/20 text-base font-bold text-white bg-primary hover:bg-primary/85 active:bg-primary/70 focus:outline-none focus:ring-4 focus:ring-accent/40 transition-all duration-200 active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden group">
                <span wire:loading.remove wire:target="sendPasswordResetLink" class="flex items-center">
                    {{ __('Email Password Reset Link') }}
                    <i
                        class="fas fa-paper-plane ml-2 text-sm group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                </span>
                <span wire:loading wire:target="sendPasswordResetLink" class="flex items-center">
                    <span class="loading loading-spinner loading-sm"></span>
                    Sending...
                </span>
            </button>
        </div>
    </form>

    <div class="mt-8 text-center">
        <a href="{{ route('login') }}"
            class="text-gray-600 dark:text-gray-300 inline-flex justify-center items-center border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 hover:border-gray-400 dark:hover:border-gray-600 transition-colors duration-300 ease-in-out rounded-xl w-full py-3 px-6"
            wire:navigate>
            Back to Login
        </a>
    </div>
</div>
