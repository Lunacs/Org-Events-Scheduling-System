<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;
use App\Models\User;
use App\Services\TransactionLogService;

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
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
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

        return match ($user->role) {
            User::ROLE_SUPERADMIN => 'superadmin.login',
            User::ROLE_OSA => 'admin.login',
            User::ROLE_GSO => 'gso.login',
            User::ROLE_STUDENT_ORG => 'student-org.login',
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
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required
                autofocus autocomplete="username" disabled />
            <x-ui.input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('New Password')" />
            <x-password-input wire:model="password" id="password" class="mt-1" name="password" required
                autocomplete="new-password" placeholder="Enter new password" />
            <x-ui.input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-password-input wire:model="password_confirmation" id="password_confirmation" class="mt-1"
                name="password_confirmation" required autocomplete="new-password" placeholder="Confirm new password" />
            <x-ui.input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="w-full sm:w-auto justify-center">
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</div>
