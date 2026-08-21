<?php

use App\Livewire\Forms\LoginForm;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        // Attempt authentication
        try {
            $this->form->authenticate();
        } catch (ValidationException $e) {
            // Log failed login attempt
            TransactionLogService::logAuthEvent('login_failed', null, "Failed login attempt for email: {$this->form->email}");
            throw $e;
        }

        $user = Auth::user();

        // Check if email is verified (especially for student orgs)
        if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail()) {
            // Log email verification required
            TransactionLogService::logAuthEvent('email_verification_required', $user, 'Login blocked - email not verified');

            // Send verification email (queued, non-blocking)
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Exception $e) {
                \Log::error('Failed to queue verification email', [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
                // Continue to verification page even if queueing fails
            }

            // Redirect to email verification page
            $this->redirect(route('verification.notice'), navigate: true);
            return;
        }

        // Log successful login
        TransactionLogService::logAuthEvent('login', $user, 'System login');

        Session::regenerate();

        // Redirect based on user role using the model's helper method
        $this->redirectIntended(default: route($user->getDashboardRoute(), absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6 sm:mb-10 text-center">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content tracking-tight mb-2">Welcome Back
        </h1>
        <p class="text-sm sm:text-base text-base-content/60">Please enter your credentials to access your
            account</p>
    </div>

    <!-- Session Status -->
    <x-ui.auth-session-status class="mb-6" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <!-- Email Address -->
        <div class="space-y-1">
            <label for="email" class="text-sm font-semibold text-base-content/80 ml-1">Email
                Address</label>
            <div class="relative group">
                <div
                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors text-base-content/40 group-focus-within:text-accent">
                    <i class="fas fa-envelope transition-colors"></i>
                </div>
                <input wire:model="form.email" id="email" type="email" name="email"
                    placeholder="name@plv.edu.ph" required autofocus autocomplete="username"
                    class="block w-full pl-11 pr-4 py-3.5 bg-base-200/50 border border-base-300 rounded-xl text-base-content placeholder-base-content/40 focus:ring-2 focus:ring-accent/30 focus:border-accent focus:bg-base-100 transition-all duration-200 sm:text-sm" />
            </div>
            <x-ui.input-error :messages="$errors->get('form.email')" class="mt-1.5 ml-1" />
        </div>

        <!-- Password -->
        <div class="space-y-1">
            <div class="flex items-center px-1">
                <label for="password" class="text-sm font-semibold text-base-content/80">Password</label>
            </div>
            <div class="relative group" x-data="{ showPassword: false }">
                <div
                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors text-base-content/40 group-focus-within:text-accent">
                    <i class="fas fa-lock transition-colors"></i>
                </div>
                <input wire:model="form.password" id="password" :type="showPassword ? 'text' : 'password'"
                    name="password" placeholder="••••••••" required autocomplete="current-password"
                    class="block w-full pl-11 pr-12 py-3.5 bg-base-200/50 border border-base-300 rounded-xl text-base-content placeholder-base-content/40 focus:ring-2 focus:ring-accent/30 focus:border-accent focus:bg-base-100 transition-all duration-200 sm:text-sm" />
                <button type="button" x-cloak @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-base-content/40 hover:text-base-content/70 transition-colors">
                    <x-ui.icon name="o-eye" class="w-5 h-5" x-show="showPassword" />
                    <x-ui.icon name="o-eye-slash" class="w-5 h-5" x-show="!showPassword" />
                </button>
            </div>
            <x-ui.input-error :messages="$errors->get('form.password')" class="mt-1.5 ml-1" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between px-1">
            <label for="remember" class="inline-flex items-center cursor-pointer group">
                <div class="relative flex items-center">
                    <input type="checkbox" wire:model="form.remember" id="remember"
                        class="checkbox checkbox-primary w-5 h-5 border-base-300 rounded-md" />
                </div>
                <span
                    class="ml-2.5 text-sm font-medium text-base-content/70 group-hover:text-base-content transition-colors">Remember
                    Me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs font-bold text-primary hover:text-accent transition-colors duration-300 ease-in-out"
                    href="{{ route('password.request') }}" wire:navigate>
                    Forgot password?
                </a>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit" wire:loading.attr="disabled"
                class="relative w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-xl shadow-lg shadow-primary/20 text-base font-bold text-white bg-primary hover:bg-primary/85 active:bg-primary/70 focus:outline-none focus:ring-4 focus:ring-accent/40 transition-all duration-200 active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden group">
                <span wire:loading.remove wire:target="login" class="flex items-center">
                    Sign In
                    <i class="fas fa-arrow-right ml-2 text-sm group-hover:translate-x-1 transition-transform"></i>
                </span>
                <div wire:loading wire:target="login" class="flex items-center gap-3">
                    <span class="loading loading-spinner loading-sm"></span>
                    Authenticating...
                </div>
            </button>
        </div>
    </form>
</div>
