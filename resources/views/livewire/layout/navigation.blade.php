<?php

use App\Livewire\Actions\Logout;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    /**
     * Get the authenticated user.
     * Using computed property to avoid reactivity issues with nested relationships.
     */
    #[Computed]
    public function user()
    {
        return auth()->user();
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
        // Force refresh by dispatching to self
        $this->dispatch('$refresh');

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
}" class="bg-base-100 border-base-300 transition-shadow duration-300"
    :class="{ 'shadow-lg': scrolled, 'shadow-xs': !scrolled }">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-sm:px-0 sm:px-6 lg:px-8">
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
                <div>
                    <x-mary-theme-toggle lightTheme="emerald" darkTheme="emeraldDark"
                        class="btn btn-ghost btn-sm btn-circle" />
                </div>

                @auth
                    <!-- Notifications Dropdown -->
                    <livewire:notification-dropdown />

                    <!-- Profile Dropdown -->
                    <x-mary-dropdown right wire:key="nav-profile-dropdown">
                        {{-- Trigger Button --}}
                        <x-slot:trigger>
                            <div class="btn btn-ghost btn-sm gap-2 hover:bg-base-200 transition-colors">
                                <div
                                    wire:key="nav-avatar-{{ $this->user->avatar_style }}-{{ $this->user->avatar_seed }}-{{ $this->user->avatar ?? 'none' }}-{{ $this->user->avatar_preference ?? 'dicebear' }}">
                                    <x-ui.avatar :user="$this->user" size="sm" nav="true" />
                                </div>
                                <span class="hidden md:inline-block max-w-[150px] truncate">{{ $this->user->name }}</span>
                                <svg class="w-4 h-4 hidden md:block" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7">
                                    </path>
                                </svg>
                            </div>
                        </x-slot:trigger>

                        {{-- Dropdown Content --}}
                        <div class="w-72 bg-base-100 rounded-box border border-base-300">
                            {{-- User Info Header --}}
                            @if ($this->user)
                                <div class="px-4 py-3 bg-base-200 rounded-t-box border-b border-base-300">
                                    <div class="flex items-center gap-3">
                                        <div
                                            wire:key="dropdown-avatar-{{ $this->user->avatar_style }}-{{ $this->user->avatar_seed }}-{{ $this->user->avatar ?? 'none' }}-{{ $this->user->avatar_preference ?? 'dicebear' }}">
                                            <x-ui.avatar :user="$this->user" size="lg" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-base text-base-content truncate">
                                                {{ $this->user->name }}
                                            </h4>
                                            <p class="text-xs text-base-content/70 truncate">{{ $this->user->email }}</p>
                                            <div class="flex gap-2">
                                                @if ($this->user->role)
                                                    <div class="mt-1">
                                                        <span
                                                            class="badge badge-primary badge-xs text-neutral-content dark:text-base-200">{{ $this->user->role_display }}</span>
                                                    </div>
                                                @endif
                                                @if ($this->user->position)
                                                    <div class="mt-1">
                                                        <span
                                                            class="badge badge-info badge-xs text-neutral-content dark:text-base-200">{{ $this->user->position->position_name }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Menu Items --}}
                            <div class="p-2">
                                <a href="{{ route('profile') }}" wire:navigate.hover
                                    class="py-3 px-4 hover:bg-base-200 transition-colors flex items-center gap-3 rounded-lg">
                                    <i class="fa-solid fa-user-circle w-5"></i>
                                    <span class="font-medium">Profile</span>
                                </a>
                            </div>

                            <div class="divider my-0"></div>

                            {{-- Logout --}}
                            <div class="p-2">
                                <button wire:click="logout"
                                    class="w-full py-3 px-4 text-error hover:bg-error/10 hover:cursor-pointer transition-colors flex items-center gap-3 rounded-lg">
                                    <i class="fa-solid fa-right-from-bracket w-5"></i>
                                    <span class="font-medium">Log Out</span>
                                </button>
                            </div>
                        </div>
                    </x-mary-dropdown>
                @endauth

                @guest
                    <!-- Login Button for Guests -->
                    <a href="{{ route('student-org.login') }}"
                        class="btn btn-primary btn-sm gap-2 hover:shadow-lg transition-all">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span class="hidden md:inline-block">Login</span>
                    </a>
                @endguest
            </div>
        </div>
    </div>
</nav>
