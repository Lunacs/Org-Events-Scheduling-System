<?php

use App\Livewire\Actions\Logout;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public $user;

    public function mount()
    {
        $this->user = auth()->user();
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    /**
     * Listen for avatar updates and refresh avatars
     */
    #[On('avatar-updated')]
    public function refreshAvatars(): void
    {
        // Refresh the user model from database
        $this->user = auth()->user()->fresh();

        // Trigger JavaScript to reinitialize avatars
        $this->js('window.dispatchEvent(new CustomEvent("navigation-refresh"))');
    }
}; ?>

@script
    <script>
        // Listen for avatar changes from any component
        window.addEventListener('avatar-changed', () => {
            Livewire.dispatch('avatar-updated');
        });
    </script>
@endscript

<nav x-data="{
    scrolled: false,
    init() {
        window.addEventListener('scroll', () => {
            this.scrolled = window.scrollY > 10;
        });
    }
}" :class="{ 'shadow-lg': scrolled, 'shadow-sm': !scrolled }"
    class="bg-base-100 border-b border-base-300 sticky top-0 z-10 transition-shadow duration-300">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left Side -->
            <div class="flex items-center gap-3">
                <!-- Hamburger for sidebar -->
                <label for="main-drawer" class="btn btn-ghost btn-sm btn-circle lg:hidden cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </label>

                {{-- <h2 class="text-lg font-semibold text-base-content hidden lg:block">
                    Event Scheduling System
                </h2> --}}
            </div>

            <!-- Right Side -->
            <div class="flex items-center gap-1 sm:gap-3">
                <!-- Theme Toggle -->
                <div class="tooltip tooltip-bottom" data-tip="Toggle Theme">
                    <x-mary-theme-toggle lightTheme="emerald" darkTheme="emeraldDark"
                        class="btn btn-ghost btn-sm btn-circle" />
                </div>

                <!-- Notifications Dropdown -->
                <livewire:notification-dropdown />

                <!-- Profile Dropdown -->
                <div class="dropdown dropdown-end" data-tip="Profile" wire:key="nav-profile-dropdown">
                    <div tabindex="0" role="button"
                        class="btn btn-ghost btn-sm gap-2 hover:bg-base-200 transition-colors">
                        <div wire:key="nav-avatar-{{ $user->avatar_style }}-{{ $user->avatar_seed }}">
                            <x-ui.avatar :user="$user" size="sm" nav="true" />
                        </div>
                        <span class="hidden md:inline-block max-w-[150px] truncate">{{ $user->name }}</span>
                        <svg class="w-4 h-4 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>
                    <ul tabindex="0"
                        class="dropdown-content z-1 menu p-2 shadow-lg bg-base-100 rounded-box w-72 border border-base-300 mt-2">
                        {{-- User Info Header --}}
                        @if ($user)
                            <li
                                class="menu-title px-4 py-3 bg-base-200 rounded-t-box -mx-2 -mt-2 mb-2 focus:bg-base-200">
                                <div class="flex items-center gap-3">
                                    <div wire:key="dropdown-avatar-{{ $user->avatar_style }}-{{ $user->avatar_seed }}">
                                        <x-ui.avatar :user="$user" size="lg" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-base text-base-content truncate">{{ $user->name }}
                                        </h4>
                                        <p class="text-xs text-base-content/70 truncate">{{ $user->email }}</p>
                                        <div class="flex gap-2">
                                            @if ($user->role)
                                                <div class="mt-1">
                                                    <span
                                                        class="badge badge-primary badge-xs">{{ $user->role_display }}</span>
                                                </div>
                                            @endif
                                            @if ($user->position)
                                                <div class="mt-1">
                                                    <span
                                                        class="badge badge-info badge-xs">{{ $user->position->position_name }}</span>
                                                </div>
                                            @endif
                                        </div>

                                    </div>
                                </div>
                            </li>
                            <div class="divider my-0"></div>
                        @endif

                        {{-- Menu Items --}}
                        <li>
                            <a href="{{ route('profile') }}" wire:navigate
                                class="py-3 px-4 hover:bg-base-200 transition-colors">
                                <i class="fa-solid fa-user-circle w-5"></i>
                                <span class="font-medium">Profile</span>
                            </a>
                        </li>

                        <div class="divider my-0"></div>

                        {{-- Logout --}}
                        <li>
                            <button wire:click="logout"
                                class="py-3 px-4 text-error hover:bg-error/10 transition-colors">
                                <i class="fa-solid fa-right-from-bracket w-5"></i>
                                <span class="font-medium">Log Out</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
