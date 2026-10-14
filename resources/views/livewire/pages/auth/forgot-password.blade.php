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
    <div class="mb-7">
        <h1 class="text-2xl font-semibold tracking-[-0.015em] text-base-content">Reset Password</h1>
        <p class="mt-1.5 text-sm text-base-content/70">
            {{ __('Enter your email address and we\'ll send you a link to reset your password.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-ui.auth-session-status class="mb-6" :status="session('status')" />

    @if (session('error'))
        <div
            class="mb-5 rounded-field border border-error/30 bg-error/[0.08] px-3 py-2.5 text-sm text-error" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-6">
        <!-- Email Address -->
        <div>
            <label for="email" class="mb-1.5 block text-[0.8125rem] font-medium text-base-content">Email address</label>
            <label class="input w-full">
                <i class="fas fa-envelope text-base-content/60" aria-hidden="true"></i>
                <input wire:model="email" id="email" type="email" name="email" placeholder="name@plv.edu.ph"
                    required autofocus autocomplete="email" spellcheck="false" class="min-w-0 grow" />
            </label>
            <x-ui.input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <button type="submit" wire:loading.attr="disabled"
                class="btn btn-primary btn-block h-11">
                <span wire:loading.remove wire:target="sendPasswordResetLink" class="flex items-center">
                    {{ __('Email Password Reset Link') }}
                    <i
                        class="fas fa-paper-plane ml-2 text-sm group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                </span>
                <span wire:loading wire:target="sendPasswordResetLink" class="flex items-center">
                    <span class="loading loading-spinner loading-sm"></span>
                    Sending…
                </span>
            </button>
        </div>
    </form>

    <div class="mt-8 text-center">
        <a href="{{ route('login') }}"
            class="btn btn-ghost btn-block"
            wire:navigate>
            Back to sign in
        </a>
    </div>
</div>
