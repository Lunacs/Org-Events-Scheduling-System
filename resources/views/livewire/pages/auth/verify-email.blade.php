<?php

use App\Livewire\Actions\Logout;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.guest')] class extends Component {
    /**
     * Mount the component and check if user is already verified.
     */
    public function mount(): void
    {
        $user = Auth::user();

        // If user is already verified, redirect to their dashboard
        if ($user && $user->hasVerifiedEmail()) {
            $this->redirectToDashboard($user);
        }
    }

    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectToDashboard($user);
            return;
        }

        // Rate limiting key based on user ID
        $key = 'verification-email:' . $user->id;

        // Check if rate limit exceeded
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            Session::flash('error', 'Please wait ' . $seconds . ' seconds before requesting another verification email.');
            return;
        }

        // Hit the rate limiter (1 attempt allowed per 60 seconds)
        RateLimiter::hit($key, 90);

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Redirect user to their role-based dashboard.
     */
    private function redirectToDashboard($user): void
    {
        $dashboardRoute = match ($user->role_id) {
            User::getRoleId('superadmin') => 'superadmin.dashboard',
            User::getRoleId('osa') => 'admin.dashboard',
            User::getRoleId('gso') => 'gso.dashboard',
            User::getRoleId('student-org') => 'student-org.dashboard',
            default => 'dashboard',
        };

        $this->redirectIntended(default: route($dashboardRoute, absolute: false), navigate: true);
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <div class="mb-7">
        <h1 class="text-2xl font-semibold tracking-[-0.015em] text-base-content">Check your email</h1>
        <p class="mt-1.5 text-sm text-base-content/70">
            {{ __('Open the verification link we emailed to you to finish setting up your account. The link can take a few minutes to arrive; check your spam folder too.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div role="status"
            class="mb-5 flex items-center rounded-field border border-success/30 bg-success/[0.08] px-3 py-2.5 text-sm text-success">
            <i class="fas fa-check-circle mr-2" aria-hidden="true"></i>
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="mb-5 rounded-field border border-error/30 bg-error/[0.08] px-3 py-2.5 text-sm text-error" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-4">
        <button wire:click="sendVerification" wire:loading.attr="disabled"
            class="btn btn-primary btn-block h-11">
            <span wire:loading.remove wire:target="sendVerification" class="flex items-center">
                {{ __('Resend verification email') }}
                <i
                    class="fas fa-paper-plane ml-2 text-sm group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
            </span>
            <span wire:loading wire:target="sendVerification" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 " xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Sending…
            </span>
        </button>

        <button type="button" wire:click="logout" class="btn btn-ghost btn-block">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
            {{ __('Log out') }}
        </button>
    </div>
</div>
