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
    <div class="mb-7">
        <h1 class="text-2xl font-semibold tracking-[-0.015em] text-base-content">Sign in</h1>
        <p class="mt-1.5 text-sm text-base-content/70">Use your PLV email address and password.</p>
    </div>

    <x-ui.auth-session-status class="mb-5" :status="session('status')" />

    <form wire:submit="login" class="space-y-5" novalidate>
        {{-- Email --}}
        <div>
            <label for="email" class="mb-1.5 block text-[0.8125rem] font-medium text-base-content">Email address</label>
            <label @class(['input w-full', 'input-error' => $errors->has('form.email')])>
                <x-ui.icon name="o-envelope" class="size-4 shrink-0 text-base-content/60" aria-hidden="true" />
                <input wire:model="form.email" id="email" type="email" name="email" inputmode="email"
                    placeholder="name@plv.edu.ph" required autofocus autocomplete="username" spellcheck="false"
                    @error('form.email') aria-invalid="true" aria-describedby="email-error" @enderror
                    class="min-w-0 grow" />
            </label>
            <x-ui.input-error :messages="$errors->get('form.email')" id="email-error" class="mt-1.5" />
        </div>

        {{-- Password --}}
        <div x-data="{ showPassword: false }">
            <div class="mb-1.5 flex items-center justify-between gap-3">
                <label for="password" class="text-[0.8125rem] font-medium text-base-content">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-[0.8125rem] font-medium text-primary hover:underline"
                        href="{{ route('password.request') }}" wire:navigate>
                        Forgot password?
                    </a>
                @endif
            </div>
            <label @class(['input w-full', 'input-error' => $errors->has('form.password')])>
                <x-ui.icon name="o-lock-closed" class="size-4 shrink-0 text-base-content/60" aria-hidden="true" />
                <input wire:model="form.password" id="password" :type="showPassword ? 'text' : 'password'"
                    type="password" name="password" required autocomplete="current-password"
                    @error('form.password') aria-invalid="true" aria-describedby="password-error" @enderror
                    class="min-w-0 grow" />
                <button type="button" @click="showPassword = !showPassword"
                    class="-me-1 rounded-field p-1 text-base-content/60 transition-colors hover:text-base-content"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'" :aria-pressed="showPassword">
                    <x-ui.icon name="o-eye" class="size-5" x-show="!showPassword" aria-hidden="true" />
                    <x-ui.icon name="o-eye-slash" class="size-5" x-show="showPassword" x-cloak aria-hidden="true" />
                </button>
            </label>
            <x-ui.input-error :messages="$errors->get('form.password')" id="password-error" class="mt-1.5" />
        </div>

        {{-- Remember me --}}
        <label for="remember" class="flex w-fit cursor-pointer items-center gap-2.5">
            <input type="checkbox" wire:model="form.remember" id="remember" class="checkbox checkbox-primary checkbox-sm" />
            <span class="text-sm text-base-content/80">Keep me signed in on this device</span>
        </label>

        <button type="submit" wire:loading.attr="disabled" wire:target="login" class="btn btn-primary btn-block h-11">
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                <span class="loading loading-spinner loading-sm" aria-hidden="true"></span>
                Signing in…
            </span>
        </button>
    </form>

    <p class="mt-7 border-t border-base-300 pt-5 text-center text-xs text-base-content/70">
        Need an account for your organization? Contact the Office of Student Affairs at
        <a href="mailto:plv.osa.official@gmail.com" class="font-medium text-primary hover:underline">plv.osa.official@gmail.com</a>.
    </p>
</div>
