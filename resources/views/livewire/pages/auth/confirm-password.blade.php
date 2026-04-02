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
    <div class="mb-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-secondary/10 text-secondary rounded-full mb-4">
            <i class="fas fa-shield-alt text-2xl"></i>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-2">Confirm Access</h1>
        <p class="text-base text-gray-500 dark:text-gray-400">
            {{ __('This is a secure area. Please confirm your password to continue.') }}
        </p>
    </div>

    <form wire:submit="confirmPassword" class="space-y-6">
        <!-- Password -->
        <div class="space-y-1">
            <label for="password" class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">Password</label>
            <div class="relative group" x-data="{ showPassword: false }">
                <div
                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors group-focus-within:text-secondary">
                    <i class="fas fa-lock text-gray-400 group-focus-within:text-secondary transition-colors"></i>
                </div>
                <input wire:model="password" id="password" :type="showPassword ? 'text' : 'password'" name="password"
                    placeholder="••••••••" required autocomplete="current-password"
                    class="block w-full pl-11 pr-12 py-3.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-secondary/20 focus:border-secondary focus:bg-white dark:focus:bg-gray-900 transition-all duration-200 sm:text-sm" />
                <button type="button" x-cloak @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <x-mary-icon name="o-eye" class="w-5 h-5" x-show="showPassword" />
                    <x-mary-icon name="o-eye-slash" class="w-5 h-5" x-show="!showPassword" />
                </button>
            </div>
            <x-ui.input-error :messages="$errors->get('password')" class="mt-1.5 ml-1" />
        </div>

        <div class="pt-2">
            <button type="submit" wire:loading.attr="disabled"
                class="relative w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-xl shadow-lg shadow-secondary/20 text-base font-bold hover:!bg-[oklch(50%_0.202_261.294)] hover:!border-[oklch(50%_0.202_261.294)] active:!bg-[oklch(40%_0.202_261.294)] active:!border-[oklch(40%_0.202_261.294)] text-white bg-secondary hover:bg-secondary-focus focus:outline-none focus:ring-4 focus:ring-secondary/30 transition-all duration-200 active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden group">
                <span wire:loading.remove wire:target="confirmPassword" class="flex items-center">
                    {{ __('Confirm Password') }}
                    <i class="fas fa-unlock-alt ml-2 text-sm group-hover:scale-110 transition-transform"></i>
                </span>
                <span wire:loading wire:target="confirmPassword" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Verifying...
                </span>
            </button>
        </div>
    </form>
</div>
