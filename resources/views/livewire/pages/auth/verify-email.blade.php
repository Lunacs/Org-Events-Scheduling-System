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
    <div class="mb-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-secondary/10 text-secondary rounded-full mb-4">
            <i class="fas fa-envelope-open-text text-2xl"></i>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-2">Verify Email</h1>
        <p class="text-base text-gray-500 dark:text-gray-400">
            {{ __('Thanks for signing up! Please verify your email address by clicking on the link we just emailed to you.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div
            class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-sm font-medium text-green-600 dark:text-green-400 flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm font-medium text-red-600 dark:text-red-400 flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-4">
        <button wire:click="sendVerification" wire:loading.attr="disabled"
            class="relative w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-xl shadow-lg shadow-secondary/20 text-base font-bold hover:!bg-[oklch(50%_0.202_261.294)] hover:!border-[oklch(50%_0.202_261.294)] active:!bg-[oklch(40%_0.202_261.294)] active:!border-[oklch(40%_0.202_261.294)] text-white bg-secondary hover:bg-secondary-focus focus:outline-none focus:ring-4 focus:ring-secondary/30 transition-all duration-200 active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden group">
            <span wire:loading.remove wire:target="sendVerification" class="flex items-center">
                {{ __('Resend Verification Email') }}
                <i
                    class="fas fa-paper-plane ml-2 text-sm group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
            </span>
            <span wire:loading wire:target="sendVerification" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Sending...
            </span>
        </button>

        <button wire:click="logout"
            class="w-full flex justify-center items-center py-3 px-6 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white transition-all duration-200">
            <i class="fas fa-sign-out-alt mr-2"></i>
            {{ __('Log Out') }}
        </button>
    </div>
</div>
