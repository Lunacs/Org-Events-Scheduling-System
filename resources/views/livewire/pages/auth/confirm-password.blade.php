<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.guest')] class extends Component {
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (
            !Auth::guard('web')->validate([
                'email' => Auth::user()->email,
                'password' => $this->password,
            ])
        ) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-7">
        <h1 class="text-2xl font-semibold tracking-[-0.015em] text-base-content">Confirm Access</h1>
        <p class="mt-1.5 text-sm text-base-content/70">
            {{ __('This is a secure area. Please confirm your password to continue.') }}
        </p>
    </div>

    <form wire:submit="confirmPassword" class="space-y-6">
        <!-- Password -->
        <div>
            <label for="password" class="mb-1.5 block text-[0.8125rem] font-medium text-base-content">Password</label>
            <label class="input w-full" x-data="{ showPassword: false }">
                <i class="fas fa-lock text-base-content/60" aria-hidden="true"></i>
                <input wire:model="password" id="password" :type="showPassword ? 'text' : 'password'" name="password"
                    placeholder="••••••••" required autocomplete="current-password" class="min-w-0 grow" />
                <button type="button" @click="showPassword = !showPassword"
                    class="-me-1 rounded-field p-1 text-base-content/60 transition-colors hover:text-base-content"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'" :aria-pressed="showPassword">
                    <x-ui.icon name="o-eye" class="size-5" x-show="!showPassword" aria-hidden="true" />
                    <x-ui.icon name="o-eye-slash" class="size-5" x-show="showPassword" x-cloak aria-hidden="true" />
                </button>
            </label>
            <x-ui.input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <button type="submit" wire:loading.attr="disabled"
                class="btn btn-primary btn-block h-11">
                <span wire:loading.remove wire:target="confirmPassword" class="flex items-center">
                    {{ __('Confirm Password') }}
                    <i class="fas fa-unlock-alt ml-2 text-sm group-hover:scale-110 transition-transform"></i>
                </span>
                <span wire:loading wire:target="confirmPassword" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 " xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Verifying…
                </span>
            </button>
        </div>
    </form>
</div>
