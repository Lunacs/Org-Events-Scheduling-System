<?php

use App\Livewire\Forms\LoginForm;
use App\Models\User;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request for OSA users.
     */
    public function login(): void
    {
        $this->validate();

        // Attempt authentication
        try {
            $this->form->authenticate();
        } catch (ValidationException $e) {
            // Log failed login attempt
            TransactionLogService::logAuthEvent('login_failed', null, "Failed OSA login attempt for email: {$this->form->email}");
            throw $e;
        }

        // Check if the authenticated user has the correct role
        $user = Auth::user();
        if (!$user->isOSA()) {
            // Log unauthorized access attempt
            TransactionLogService::logAuthEvent('unauthorized_access', $user, 'Non-OSA user attempted OSA login');
            Auth::guard()->logout();
            throw ValidationException::withMessages([
                'form.email' => 'Access denied. This portal is restricted to OSA staff only. Please use the correct portal for your account type.',
            ]);
        }

        // Log successful login
        TransactionLogService::logAuthEvent('login', $user, 'OSA portal login');

        Session::regenerate();

        $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Log In</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Office of Student Affairs Portal</p>
    </div>

    <!-- Session Status -->
    <x-ui.auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-6">
        <!-- Email Address -->
        <div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400"></i>
                </div>
                <input wire:model="form.email" id="email" type="email" name="email" placeholder="Email" required
                    autofocus autocomplete="username"
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
            </div>
            <x-ui.input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="relative" x-data="{ showPassword: false }">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400"></i>
                </div>
                <input wire:model="form.password" id="password" :type="showPassword ? 'text' : 'password'"
                    name="password" placeholder="Password" required autocomplete="current-password"
                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                <button type="button" @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <i class="fas fa-eye text-gray-400 hover:text-gray-600" x-show="!showPassword"></i>
                    <i class="fas fa-eye-slash text-gray-400 hover:text-gray-600" x-show="showPassword"></i>
                </button>
            </div>
            <x-ui.input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me and Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember" class="flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate
                    class="text-sm text-blue-600 hover:text-blue-500">
                    Forgot Password?
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <div>
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-secondary hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                Log in
            </button>
        </div>
    </form>
</div>
