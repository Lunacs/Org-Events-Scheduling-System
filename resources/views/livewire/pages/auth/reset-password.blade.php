<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Models\User;
use App\Services\TransactionLogService;
use App\Notifications\PasswordChangedNotification;

new #[Layout('components.layouts.guest')] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset($this->only('email', 'password', 'password_confirmation', 'token'), function ($user) {
            $user
                ->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])
                ->save();

            // Log password reset
            TransactionLogService::logAuthEvent('password_changed', $user, 'Password reset via email link');

            // SECURITY: Send notification email to alert user of password change
            $user->notify(new PasswordChangedNotification(request()->ip()));

            event(new PasswordReset($user));
        });

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        // Get the user to determine role-based redirect
        $user = User::where('email', $this->email)->first();
        $redirectRoute = $this->getLoginRouteForUser($user);

        $this->redirectRoute($redirectRoute, navigate: true);
    }

    /**
     * Get the appropriate login route based on user role.
     */
    private function getLoginRouteForUser(?User $user): string
    {
        if (!$user) {
            return 'login';
        }

        return match ($user->role_id) {
            User::getRoleId('superadmin') => 'superadmin.login',
            User::getRoleId('osa') => 'admin.login',
            User::getRoleId('gso') => 'gso.login',
            User::getRoleId('student-org') => 'student-org.login',
            default => 'login',
        };
    }
}; ?>

<div>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Reset Password</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Enter your new password below</p>
    </div>

    <form wire:submit="resetPassword" class="space-y-6">
        <!-- Email Address -->
        <div>
            <x-ui.input-label for="email" :value="__('Email')" />
            <x-forms.text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email"
                required autofocus autocomplete="username" disabled />
            <x-ui.input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div x-data="{ showPassword: false }">
            <x-ui.input-label for="password" :value="__('New Password')" />
            <div class="relative">
                <x-forms.text-input wire:model.live.debounce.300ms="password" id="password"
                    class="block mt-1 w-full pr-10" ::type="showPassword ? 'text' : 'password'" name="password" required
                    autocomplete="new-password" placeholder="Enter new password" />
                <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400 transition-colors"
                    tabindex="-1">
                    <i class="fas fa-eye-slash text-sm" x-show="!showPassword"></i>
                    <i class="fas fa-eye text-sm" x-show="showPassword" style="display: none;"></i>
                </button>
            </div>

            <!-- Password Strength Indicator -->
            <div class="mt-2" x-data="{
                strength: 0,
                strengthText: 'Weak',
                strengthColor: 'bg-red-500',
                checkStrength() {
                    let pwd = $wire.password || '';
                    let score = 0;
                    if (pwd.length >= 8) score++;
                    if (/[A-Z]/.test(pwd)) score++;
                    if (/[a-z]/.test(pwd)) score++;
                    if (/[0-9]/.test(pwd)) score++;
                    if (/[^A-Za-z0-9]/.test(pwd)) score++;
            
                    this.strength = score;
                    if (score === 0) {
                        this.strengthText = 'Weak';
                        this.strengthColor = 'bg-red-500';
                    } else if (score <= 2) {
                        this.strengthText = 'Weak';
                        this.strengthColor = 'bg-red-500';
                    } else if (score === 3) {
                        this.strengthText = 'Fair';
                        this.strengthColor = 'bg-yellow-500';
                    } else if (score === 4) {
                        this.strengthText = 'Good';
                        this.strengthColor = 'bg-blue-500';
                    } else {
                        this.strengthText = 'Strong';
                        this.strengthColor = 'bg-green-500';
                    }
                }
            }" x-init="$watch('$wire.password', () => checkStrength())">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-gray-600 dark:text-gray-400">Password Strength:</span>
                    <span class="text-xs font-medium"
                        :class="{
                            'text-red-600 dark:text-red-400': strength <= 2,
                            'text-yellow-600 dark:text-yellow-400': strength === 3,
                            'text-blue-600 dark:text-blue-400': strength === 4,
                            'text-green-600 dark:text-green-400': strength === 5
                        }"
                        x-text="strengthText"></span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-300" :class="strengthColor"
                        :style="`width: ${strength * 20}%`"></div>
                </div>

                <!-- Password Requirements -->
                <ul class="mt-2 space-y-1 text-xs">
                    <li class="flex items-center"
                        :class="$wire.password && $wire.password.length >= 8 ? 'text-green-600 dark:text-green-400' :
                            'text-gray-500 dark:text-gray-400'">
                        <i class="fas fa-check-circle mr-1" x-show="$wire.password && $wire.password.length >= 8"></i>
                        <i class="far fa-circle mr-1" x-show="!$wire.password || $wire.password.length < 8"></i>
                        At least 8 characters
                    </li>
                    <li class="flex items-center"
                        :class="$wire.password && /[A-Z]/.test($wire.password) ? 'text-green-600 dark:text-green-400' :
                            'text-gray-500 dark:text-gray-400'">
                        <i class="fas fa-check-circle mr-1" x-show="$wire.password && /[A-Z]/.test($wire.password)"></i>
                        <i class="far fa-circle mr-1" x-show="!$wire.password || !/[A-Z]/.test($wire.password)"></i>
                        One uppercase letter
                    </li>
                    <li class="flex items-center"
                        :class="$wire.password && /[a-z]/.test($wire.password) ? 'text-green-600 dark:text-green-400' :
                            'text-gray-500 dark:text-gray-400'">
                        <i class="fas fa-check-circle mr-1" x-show="$wire.password && /[a-z]/.test($wire.password)"></i>
                        <i class="far fa-circle mr-1" x-show="!$wire.password || !/[a-z]/.test($wire.password)"></i>
                        One lowercase letter
                    </li>
                    <li class="flex items-center"
                        :class="$wire.password && /[0-9]/.test($wire.password) ? 'text-green-600 dark:text-green-400' :
                            'text-gray-500 dark:text-gray-400'">
                        <i class="fas fa-check-circle mr-1" x-show="$wire.password && /[0-9]/.test($wire.password)"></i>
                        <i class="far fa-circle mr-1" x-show="!$wire.password || !/[0-9]/.test($wire.password)"></i>
                        One number
                    </li>
                    <li class="flex items-center"
                        :class="$wire.password && /[^A-Za-z0-9]/.test($wire.password) ? 'text-green-600 dark:text-green-400' :
                            'text-gray-500 dark:text-gray-400'">
                        <i class="fas fa-check-circle mr-1"
                            x-show="$wire.password && /[^A-Za-z0-9]/.test($wire.password)"></i>
                        <i class="far fa-circle mr-1"
                            x-show="!$wire.password || !/[^A-Za-z0-9]/.test($wire.password)"></i>
                        One symbol (!@#$%^&*...)
                    </li>
                </ul>
            </div>
            <x-ui.input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div x-data="{ showConfirmPassword: false }">
            <x-ui.input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div class="relative">
                <x-forms.text-input wire:model.live.debounce.300ms="password_confirmation" id="password_confirmation"
                    class="block mt-1 w-full pr-10" ::type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation" required
                    autocomplete="new-password" placeholder="Confirm new password" />
                <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400 transition-colors"
                    tabindex="-1">
                    <i class="fas fa-eye-slash text-sm" x-show="!showConfirmPassword"></i>
                    <i class="fas fa-eye text-sm" x-show="showConfirmPassword" style="display: none;"></i>
                </button>
            </div>
            <x-ui.input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-ui.primary-button class="w-full sm:w-auto justify-center">
                {{ __('Reset Password') }}
            </x-ui.primary-button>
        </div>
    </form>
</div>