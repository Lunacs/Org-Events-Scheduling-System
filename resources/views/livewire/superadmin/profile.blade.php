<div class="py-6" x-data="{
    scrollToSection(section) {
        const element = document.getElementById(section);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- Page Header --}}
        <div class="bg-gradient-to-r from-primary to-secondary rounded-box shadow-lg p-6 text-primary-content">
            <div class="flex items-center gap-4">
                <div wire:key="profile-header-avatar-{{ $user->avatar_style }}-{{ $user->avatar_seed }}">
                    <x-ui.avatar :user="$user" size="2xl" class="ring-4 ring-base-100" nav="false" />
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-3xl font-bold break-words">{{ $user->name }}</h1>
                    <p class="text-primary-content/80 mt-1 text-sm sm:text-base break-words">{{ $user->email }}</p>
                    <div class="flex gap-2 mt-2">
                        <span class="badge badge-lg bg-base-100/20 text-primary-content border-0">
                            {{ $user->role_display }}
                        </span>
                        @if ($user->email_verified_at)
                            <span class="badge badge-lg badge-success text-white">
                                <i class="fa-solid fa-check-circle mr-1"></i> Verified
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Avatar Selector (includes photo upload) --}}
                <div id="avatar">
                    <livewire:avatar-selector />
                </div>

                {{-- Profile Information --}}
                <x-ui.card title="Profile Information" subtitle="Update your account details" x-data="{
                    initialName: {{ Js::from($name) }},
                    initialEmail: {{ Js::from($email) }},
                    initialPhone: {{ Js::from($phone ?? '') }},
                    get hasChanges() {
                        return $wire.name !== this.initialName ||
                            $wire.email !== this.initialEmail ||
                            $wire.phone !== this.initialPhone;
                    }
                }"
                    @profile-updated.window="
                        initialName = $wire.name;
                        initialEmail = $wire.email;
                        initialPhone = $wire.phone;
                    ">
                    <x-slot:menu>
                        <x-ui.icon name="o-user-circle" class="w-6 h-6 text-primary" />
                    </x-slot:menu>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if ($pending_email)
                            <div
                                class="md:col-span-2 flex items-center gap-3 p-3 bg-info/10 border border-info/30 rounded-lg text-sm">
                                <x-ui.icon name="o-envelope" class="w-5 h-5 text-info shrink-0" />
                                <span class="flex-1 text-info-content dark:text-info">
                                    Verification sent to <strong>{{ $pending_email }}</strong>. Check your inbox.
                                </span>
                                <x-ui.button wire:click="cancelEmailChange" icon="o-x-mark"
                                    class="btn-ghost btn-xs text-error" tooltip="Cancel email change" />
                            </div>
                        @endif
                        <x-ui.input wire:model.live="name" label="Full Name" placeholder="Your name" icon="o-user"
                            required />

                        <x-ui.input wire:model.live="email" label="Email Address" type="email"
                            placeholder="you@example.com" icon="o-envelope" required />

                        <x-ui.input wire:model.live="phone" label="Phone Number" placeholder="09123456789"
                            icon="o-phone" />

                        <x-ui.input wire:model="department" label="Department" placeholder="System Administration"
                            icon="o-building-office" disabled />
                    </div>

                    <x-slot:actions>
                        <x-ui.button wire:click="updateProfile" icon="o-check"
                            class="btn-primary data-loading:opacity-50 data-loading:pointer-events-none"
                            x-show="hasChanges" spinner>
                            Save Changes
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.card>

                {{-- Password Change --}}
                <form wire:submit.prevent="updatePassword">
                    <x-ui.card id="password" title="Change Password"
                        subtitle="Update your password to keep your account secure" x-data="{
                            showCurrent: false,
                            showNew: false,
                            showConfirm: false,
                            get hasPasswordInput() {
                                return ($wire.current_password && $wire.current_password.length > 0) ||
                                    ($wire.new_password && $wire.new_password.length > 0) ||
                                    ($wire.new_password_confirmation && $wire.new_password_confirmation.length > 0);
                            },
                            get passwordStrength() {
                                const pw = $wire.new_password || '';
                                if (!pw) return { score: 0, label: '', color: '' };
                                let score = 0;
                                if (pw.length >= 8) score++;
                                if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++;
                                if (/\d/.test(pw)) score++;
                                if (/[^a-zA-Z0-9]/.test(pw)) score++;
                                if (pw.length >= 12) score++;

                                const levels = [
                                    { label: '', color: '' },
                                    { label: 'Weak', color: 'bg-error' },
                                    { label: 'Fair', color: 'bg-warning' },
                                    { label: 'Good', color: 'bg-info' },
                                    { label: 'Strong', color: 'bg-success' },
                                    { label: 'Very Strong', color: 'bg-success' },
                                ];
                                return { score, label: levels[score].label, color: levels[score].color };
                            },
                            get passwordsMatch() {
                                const pw = $wire.new_password || '';
                                const confirm = $wire.new_password_confirmation || '';
                                if (!pw || !confirm) return null;
                                return pw === confirm;
                            }
                        }">
                        <x-slot:menu>
                            <x-ui.icon name="o-lock-closed" class="w-6 h-6 text-warning" />
                        </x-slot:menu>

                        <div class="space-y-4">
                            {{-- Current Password with Toggle --}}
                            <div class="relative">
                                <x-ui.input wire:model="current_password" label="Current Password" ::type="showCurrent ? 'text' : 'password'"
                                    placeholder="Enter current password" icon="o-key"
                                    autocomplete="current-password" />
                                <button type="button" @click="showCurrent = !showCurrent"
                                    class="absolute right-3 top-[38px] flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                                    tabindex="-1">
                                    <i class="fas fa-eye" x-show="showCurrent" x-cloak></i>
                                    <i class="fas fa-eye-slash" x-show="!showCurrent"></i>
                                </button>
                            </div>

                            {{-- New Password Fields --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <x-ui.input wire:model.live.debounce.300ms="new_password" label="New Password"
                                        ::type="showNew ? 'text' : 'password'" placeholder="Enter new password" icon="o-lock-closed"
                                        autocomplete="new-password" />
                                    <button type="button" @click="showNew = !showNew"
                                        class="absolute right-3 top-[38px] flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                                        tabindex="-1">
                                        <i class="fas fa-eye" x-show="showNew" x-cloak></i>
                                        <i class="fas fa-eye-slash" x-show="!showNew"></i>
                                    </button>
                                </div>

                                <div class="relative">
                                    <x-ui.input wire:model.live.debounce.300ms="new_password_confirmation"
                                        label="Confirm New Password" ::type="showConfirm ? 'text' : 'password'"
                                        placeholder="Confirm new password" icon="o-lock-closed"
                                        autocomplete="new-password" />
                                    <button type="button" @click="showConfirm = !showConfirm"
                                        class="absolute right-3 top-[38px] flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                                        tabindex="-1">
                                        <i class="fas fa-eye" x-show="showConfirm" x-cloak></i>
                                        <i class="fas fa-eye-slash" x-show="!showConfirm"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Password Strength Indicator --}}
                            <div x-show="$wire.new_password && $wire.new_password.length > 0" x-collapse x-cloak>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-medium text-base-content/70">Password Strength</span>
                                        <span class="font-semibold"
                                            :class="{
                                                'text-error': passwordStrength.score <= 1,
                                                'text-warning': passwordStrength.score === 2,
                                                'text-info': passwordStrength.score === 3,
                                                'text-success': passwordStrength.score >= 4
                                            }"
                                            x-text="passwordStrength.label"></span>
                                    </div>
                                    <div class="flex gap-1">
                                        <template x-for="i in 5" :key="i">
                                            <div class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                                :class="i <= passwordStrength.score ? passwordStrength.color : 'bg-base-300'">
                                            </div>
                                        </template>
                                    </div>
                                    <ul class="text-xs space-y-1 text-base-content/70">
                                        <li :class="($wire.new_password || '').length >= 8 ? 'text-success' : ''">
                                            <i class="fa-solid fa-fw"
                                                :class="($wire.new_password || '').length >= 8 ? 'fa-check' :
                                                    'fa-circle text-[6px] align-middle'"></i>
                                            At least 8 characters
                                        </li>
                                        <li
                                            :class="/[a-z]/.test($wire.new_password || '') && /[A-Z]/.test($wire.new_password ||
                                                '') ? 'text-success' : ''">
                                            <i class="fa-solid fa-fw"
                                                :class="/[a-z]/.test($wire.new_password || '') && /[A-Z]/.test($wire
                                                        .new_password || '') ? 'fa-check' :
                                                    'fa-circle text-[6px] align-middle'"></i>
                                            Uppercase and lowercase letters
                                        </li>
                                        <li :class="/\d/.test($wire.new_password || '') ? 'text-success' : ''">
                                            <i class="fa-solid fa-fw"
                                                :class="/\d/.test($wire.new_password || '') ? 'fa-check' :
                                                    'fa-circle text-[6px] align-middle'"></i>
                                            At least one number
                                        </li>
                                        <li
                                            :class="/[^a-zA-Z0-9]/.test($wire.new_password || '') ? 'text-success' : ''">
                                            <i class="fa-solid fa-fw"
                                                :class="/[^a-zA-Z0-9]/.test($wire.new_password || '') ? 'fa-check' :
                                                    'fa-circle text-[6px] align-middle'"></i>
                                            At least one special character
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            {{-- Password Match Indicator --}}
                            <div x-show="($wire.new_password_confirmation || '').length > 0" x-collapse x-cloak>
                                <div class="flex items-center gap-2 text-sm"
                                    :class="passwordsMatch === true ? 'text-success' : 'text-error'">
                                    <i class="fa-solid"
                                        :class="passwordsMatch === true ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                    <span
                                        x-text="passwordsMatch === true ? 'Passwords match' : 'Passwords do not match'"></span>
                                </div>
                            </div>
                        </div>

                        <x-slot:actions>
                            <x-ui.button type="submit" icon="o-shield-check"
                                class="btn-warning data-loading:opacity-50 data-loading:pointer-events-none"
                                x-show="hasPasswordInput" spinner>
                                Update Password
                            </x-ui.button>
                        </x-slot:actions>
                    </x-ui.card>
                </form>

                {{-- Notification Preferences --}}
                <x-ui.card id="notifications" title="Notification Preferences"
                    subtitle="Manage how you receive updates" x-data="{
                        initialEmailNotifications: Boolean({{ Js::from($email_notifications) }}),
                        initialTicketUpdates: Boolean({{ Js::from($ticket_updates) }}),
                        get hasChanges() {
                            return Boolean($wire.email_notifications) !== this.initialEmailNotifications ||
                                Boolean($wire.ticket_updates) !== this.initialTicketUpdates;
                        }
                    }"
                    @preferences-updated.window="
                        initialEmailNotifications = Boolean($wire.email_notifications);
                        initialTicketUpdates = Boolean($wire.ticket_updates);
                    ">
                    <x-slot:menu>
                        <x-ui.icon name="o-bell" class="w-6 h-6 text-info" />
                    </x-slot:menu>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="bg-success/10 p-2 rounded-full">
                                    <x-ui.icon name="o-envelope" class="w-5 h-5 text-success" />
                                </div>
                                <div>
                                    <h4 class="font-semibold">Email Notifications</h4>
                                    <p class="text-sm text-base-content/70">Receive email for all activities</p>
                                </div>
                            </div>
                            <x-ui.toggle wire:model.live="email_notifications" class="toggle-success" />
                        </div>

                        <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="bg-primary/10 p-2 rounded-full">
                                    <x-ui.icon name="o-ticket" class="w-5 h-5 text-primary" />
                                </div>
                                <div>
                                    <h4 class="font-semibold">Ticket Updates</h4>
                                    <p class="text-sm text-base-content/70">Get notified on ticket changes</p>
                                </div>
                            </div>
                            <x-ui.toggle wire:model.live="ticket_updates" class="toggle-primary" />
                        </div>
                    </div>

                    <x-slot:actions>
                        <x-ui.button wire:click="updatePreferences" icon="o-check"
                            class="btn-info data-loading:opacity-50 data-loading:pointer-events-none"
                            x-show="hasChanges" spinner>
                            Save Preferences
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.card>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Account Stats --}}
                <x-ui.card title="Account Overview" class="shadow-lg">
                    <div class="space-y-4">
                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-figure text-primary">
                                <x-ui.icon name="o-calendar" class="w-8 h-8" />
                            </div>
                            <div class="stat-title">Member Since</div>
                            <div class="stat-value text-lg">{{ $user->created_at->format('M Y') }}</div>
                            <div class="stat-desc">{{ $user->created_at->diffForHumans() }}</div>
                        </div>

                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-figure text-success">
                                <x-ui.icon name="o-clock" class="w-8 h-8" />
                            </div>
                            <div class="stat-title">Last Login</div>
                            @if ($user->last_login)
                                <div class="stat-value text-lg">{{ $user->last_login->format('M d, Y') }}</div>
                                <div class="stat-desc">{{ $user->last_login->format('h:i A') }}</div>
                            @endif
                        </div>

                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-figure text-info">
                                <x-ui.icon name="o-shield-check" class="w-8 h-8" />
                            </div>
                            <div class="stat-title">Account Status</div>
                            <div class="stat-value text-lg">Active</div>
                            <div class="stat-desc">Full system access</div>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</div>
