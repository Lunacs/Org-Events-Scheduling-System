<?php

use App\Livewire\Actions\Logout;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

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
        $dashboardRoute = match ($user->role) {
            User::ROLE_SUPERADMIN => 'superadmin.dashboard',
            User::ROLE_OSA => 'admin.dashboard',
            User::ROLE_GSO => 'gso.dashboard',
            User::ROLE_STUDENT_ORG => 'student-org.dashboard',
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
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 font-medium text-sm text-red-600 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-0">
        <x-mary-button class="bg-neutral w-full sm:w-auto" wire:click="sendVerification">
            {{ __('Resend Verification Email') }}
        </x-mary-button>

        <x-mary-button class="ring-1 text-base-200 bg-white w-full sm:w-auto" wire:click="logout" type="submit">
            {{ __('Log Out') }}
        </x-mary-button>
    </div>
</div>
