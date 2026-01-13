<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- Page Header --}}
        <div class="bg-gradient-to-r from-primary to-secondary rounded-box shadow-lg p-6 text-primary-content">
            <div class="flex items-center gap-4">
                <div wire:key="profile-header-avatar-{{ $user->avatar_style }}-{{ $user->avatar_seed }}">
                    <x-ui.avatar :user="$user" size="2xl" class="ring-4 ring-base-100" nav="false" />
                </div>
                <div>
                    <h1 class="text-3xl font-bold">{{ $user->name }}</h1>
                    <p class="text-primary-content/80 mt-1">{{ $user->email }}</p>
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
                <livewire:avatar-selector />

                {{-- Profile Information --}}
                <x-mary-card title="Profile Information" subtitle="Update your account details" x-data="{
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
                        <x-mary-icon name="o-user-circle" class="w-6 h-6 text-primary" />
                    </x-slot:menu>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-mary-input wire:model.live="name" label="Full Name" placeholder="Your name" icon="o-user"
                            required />

                        <x-mary-input wire:model.live="email" label="Email Address" type="email"
                            placeholder="you@example.com" icon="o-envelope" required />

                        <x-mary-input wire:model.live="phone" label="Phone Number" placeholder="09123456789"
                            icon="o-phone" />

                        <x-mary-input wire:model="office" label="Office" placeholder="Your office"
                            icon="o-building-office" disabled />
                    </div>

                    <x-slot:actions>
                        <x-mary-button wire:click="updateProfile" icon="o-check" class="btn-primary"
                            x-show="hasChanges">
                            <span wire:loading.remove wire:target="updateProfile">Save Changes</span>
                            <span wire:loading wire:target="updateProfile">Saving...</span>
                        </x-mary-button>
                    </x-slot:actions>
                </x-mary-card>

                {{-- Password Change --}}
                <x-mary-card title="Change Password" subtitle="Update your password to keep your account secure"
                    x-data="{
                        get hasPasswordInput() {
                            return ($wire.current_password && $wire.current_password.length > 0) ||
                                ($wire.new_password && $wire.new_password.length > 0) ||
                                ($wire.new_password_confirmation && $wire.new_password_confirmation.length > 0);
                        }
                    }">
                    <x-slot:menu>
                        <x-mary-icon name="o-lock-closed" class="w-6 h-6 text-warning" />
                    </x-slot:menu>

                    <div class="space-y-4">
                        {{-- Current Password with Toggle --}}
                        <div x-data="{ show: false }" class="relative">
                            <x-mary-input wire:model="current_password" label="Current Password"
                                x-bind:type="show ? 'text' : 'password'" placeholder="Enter current password"
                                icon="o-key" />
                            <button type="button" @click="show = !show"
                                class="absolute right-3 top-[2.6rem] text-gray-400 hover:text-gray-600 transition-colors"
                                tabindex="-1">
                                <i class="fas fa-eye-slash" x-show="!show"></i>
                                <i class="fas fa-eye" x-show="show" style="display: none;"></i>
                            </button>
                        </div>

                        {{-- New Password Fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div x-data="{ show: false }" class="relative">
                                <x-mary-input wire:model="new_password" label="New Password"
                                    x-bind:type="show ? 'text' : 'password'" placeholder="Enter new password"
                                    icon="o-lock-closed" />
                                <button type="button" @click="show = !show"
                                    class="absolute right-3 top-[2.6rem] text-gray-400 hover:text-gray-600 transition-colors"
                                    tabindex="-1">
                                    <i class="fas fa-eye-slash" x-show="!show"></i>
                                    <i class="fas fa-eye" x-show="show" style="display: none;"></i>
                                </button>
                            </div>

                            <div x-data="{ show: false }" class="relative">
                                <x-mary-input wire:model="new_password_confirmation" label="Confirm New Password"
                                    x-bind:type="show ? 'text' : 'password'" placeholder="Confirm new password"
                                    icon="o-lock-closed" />
                                <button type="button" @click="show = !show"
                                    class="absolute right-3 top-[2.6rem] text-gray-400 hover:text-gray-600 transition-colors"
                                    tabindex="-1">
                                    <i class="fas fa-eye-slash" x-show="!show"></i>
                                    <i class="fas fa-eye" x-show="show" style="display: none;"></i>
                                </button>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <x-mary-icon name="o-information-circle" class="w-5 h-5" />
                            <div class="text-sm">
                                <p class="font-medium">Password Requirements:</p>
                                <ul class="list-disc list-inside mt-1">
                                    <li>At least 8 characters long</li>
                                    <li>Contains uppercase and lowercase letters</li>
                                    <li>Contains at least one number</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <x-slot:actions>
                        <x-mary-button wire:click="updatePassword" icon="o-shield-check"
                            class="btn-warning text-neutral-content" x-show="hasPasswordInput">
                            <span wire:loading.remove wire:target="updatePassword">Update Password</span>
                            <span wire:loading wire:target="updatePassword">Updating...</span>
                        </x-mary-button>
                    </x-slot:actions>
                </x-mary-card>

                {{-- Notification Preferences --}}
                <x-mary-card title="Notification Preferences" subtitle="Manage how you receive updates"
                    x-data="{
                        initialEmailNotifications: {{ Js::from($email_notifications) }},
                        initialTicketUpdates: {{ Js::from($ticket_updates) }},
                        initialApprovalAlerts: {{ Js::from($approval_alerts) }},
                        get hasChanges() {
                            return $wire.email_notifications !== this.initialEmailNotifications ||
                                $wire.ticket_updates !== this.initialTicketUpdates ||
                                $wire.approval_alerts !== this.initialApprovalAlerts;
                        }
                    }"
                    @preferences-updated.window="
                        initialEmailNotifications = $wire.email_notifications;
                        initialTicketUpdates = $wire.ticket_updates;
                        initialApprovalAlerts = $wire.approval_alerts;
                    ">
                    <x-slot:menu>
                        <x-mary-icon name="o-bell" class="w-6 h-6 text-info" />
                    </x-slot:menu>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="bg-success/10 p-2 rounded-full">
                                    <x-mary-icon name="o-envelope" class="w-5 h-5 text-success" />
                                </div>
                                <div>
                                    <h4 class="font-semibold font-heading">Email Notifications</h4>
                                    <p class="text-sm text-base-content/70">Receive email for all activities</p>
                                </div>
                            </div>
                            <x-mary-toggle wire:model.live="email_notifications" class="toggle-success" />
                        </div>

                        <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="bg-primary/10 p-2 rounded-full">
                                    <x-mary-icon name="o-ticket" class="w-5 h-5 text-primary" />
                                </div>
                                <div>
                                    <h4 class="font-semibold font-heading">Ticket Updates</h4>
                                    <p class="text-sm text-base-content/70">Get notified on ticket changes</p>
                                </div>
                            </div>
                            <x-mary-toggle wire:model.live="ticket_updates" class="toggle-primary" />
                        </div>

                        <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="bg-secondary/10 p-2 rounded-full">
                                    <x-mary-icon name="o-check-circle" class="w-5 h-5 text-secondary" />
                                </div>
                                <div>
                                    <h4 class="font-semibold font-heading">Approval Alerts</h4>
                                    <p class="text-sm text-base-content/70">Receive alerts for pending approvals</p>
                                </div>
                            </div>
                            <x-mary-toggle wire:model.live="approval_alerts" class="toggle-secondary" />
                        </div>
                    </div>

                    <x-slot:actions>
                        <x-mary-button wire:click="updatePreferences" icon="o-check"
                            class="btn-info text-neutral-content" x-show="hasChanges">
                            <span wire:loading.remove wire:target="updatePreferences">Save Preferences</span>
                            <span wire:loading wire:target="updatePreferences">Saving...</span>
                        </x-mary-button>
                    </x-slot:actions>
                </x-mary-card>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Account Stats --}}
                <x-mary-card title="Account Overview" class="shadow-lg">
                    <div class="space-y-4">
                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-figure text-primary">
                                <x-mary-icon name="o-calendar" class="w-8 h-8" />
                            </div>
                            <div class="stat-title">Member Since</div>
                            <div class="stat-value text-lg">{{ $user->created_at->format('M Y') }}</div>
                            <div class="stat-desc">{{ $user->created_at->diffForHumans() }}</div>
                        </div>

                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-figure text-success">
                                <x-mary-icon name="o-clock" class="w-8 h-8" />
                            </div>
                            <div class="stat-title">Last Login</div>
                            @if ($user->last_login)
                                <div class="stat-value text-lg">{{ $user->last_login->format('M d, Y') }}</div>
                                <div class="stat-desc">{{ $user->last_login->format('h:i A') }}</div>
                            @endif
                        </div>

                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-figure text-info">
                                <x-mary-icon name="o-shield-check" class="w-8 h-8" />
                            </div>
                            <div class="stat-title">Account Status</div>
                            <div class="stat-value text-lg">Active</div>
                            <div class="stat-desc">Verified account</div>
                        </div>
                    </div>
                </x-mary-card>
            </div>
        </div>
    </div>
</div>
