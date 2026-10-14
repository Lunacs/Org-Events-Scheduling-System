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

<nav class="border-b border-base-300 bg-base-100" aria-label="Account">
    <div class="flex h-16 items-center justify-between gap-2 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2">
            {{-- Mobile: open the sidebar drawer --}}
            <button type="button" onclick="window.__toggleSidebar()" aria-controls="portal-sidebar"
                class="btn btn-ghost btn-square btn-sm lg:hidden" aria-label="Open navigation menu">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <a href="{{ auth()->check() ? route(auth()->user()->getDashboardRoute()) : url('/') }}" wire:navigate
                class="flex items-center gap-2 lg:hidden">
                <img src="{{ asset('images/optimized/plv-logo.webp') }}" alt="" class="size-7" width="80" height="80">
                <span class="text-sm font-semibold">Event Scheduling</span>
            </a>
        </div>

        <div class="flex items-center gap-1 sm:gap-2">
            {{-- Theme toggle --}}
            <label class="swap swap-rotate btn btn-ghost btn-sm btn-square" title="Switch light/dark theme">
                <input id="theme-toggle" type="checkbox" value="emeraldDark" class="theme-controller sr-only"
                    aria-label="Dark theme" onchange="window.__themeToggleChange(this)" />
                {{-- moon = shown in light mode → click to go dark --}}
                <x-ui.icon name="o-moon" class="swap-off size-5" aria-hidden="true" />
                {{-- sun = shown in dark mode → click to go light --}}
                <x-ui.icon name="o-sun" class="swap-on size-5" aria-hidden="true" />
            </label>

            @auth
                @can('notifications.view')
                    <livewire:notification-dropdown />
                @endcan

                {{-- Account menu --}}
                <x-ui.dropdown right width="w-72" wire:key="nav-profile-dropdown">
                    <x-slot:trigger>
                        <div class="btn btn-ghost btn-sm h-10 gap-2 px-1.5 sm:px-2">
                            <div
                                wire:key="nav-avatar-{{ $this->user->avatar_style }}-{{ $this->user->avatar_seed }}-{{ $this->user->avatar ?? 'none' }}-{{ $this->user->avatar_preference ?? 'dicebear' }}">
                                <x-ui.avatar :user="$this->user" size="sm" nav="true" />
                            </div>
                            <span class="hidden max-w-[160px] truncate text-sm font-medium md:inline-block">{{ $this->user->name }}</span>
                            <span class="sr-only md:hidden">Account menu</span>
                            <x-ui.icon name="o-chevron-down" class="hidden size-4 text-base-content/60 md:block" aria-hidden="true" />
                        </div>
                    </x-slot:trigger>

                    <div class="overflow-hidden rounded-box border border-base-300 bg-base-100">
                        @if ($this->user)
                            <div class="flex items-center gap-3 border-b border-base-300 px-4 py-3">
                                <div
                                    wire:key="dropdown-avatar-{{ $this->user->avatar_style }}-{{ $this->user->avatar_seed }}-{{ $this->user->avatar ?? 'none' }}-{{ $this->user->avatar_preference ?? 'dicebear' }}">
                                    <x-ui.avatar :user="$this->user" size="md" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-base-content">{{ $this->user->name }}</p>
                                    <p class="truncate text-xs text-base-content/70">{{ $this->user->email }}</p>
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        @if ($this->user->role)
                                            <span class="badge badge-soft badge-primary badge-sm">{{ $this->user->role_display }}</span>
                                        @endif
                                        @if ($this->user->position)
                                            <span class="badge badge-soft badge-sm">{{ $this->user->position->position_name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <ul class="p-1.5">
                            <li>
                                <a href="{{ route('profile') }}" wire:navigate.hover
                                    class="flex items-center gap-3 rounded-field px-3 py-2.5 text-sm font-medium transition-colors hover:bg-base-200">
                                    <x-ui.icon name="o-user-circle" class="size-5 text-base-content/70" aria-hidden="true" />
                                    Profile
                                </a>
                            </li>
                            <li>
                                <button type="button" wire:click="logout"
                                    class="flex w-full items-center gap-3 rounded-field px-3 py-2.5 text-sm font-medium text-error transition-colors hover:bg-error/10">
                                    <x-ui.icon name="o-arrow-right-start-on-rectangle" class="size-5" aria-hidden="true" />
                                    Log out
                                </button>
                            </li>
                        </ul>
                    </div>
                </x-ui.dropdown>
            @endauth

            @guest
                <a href="{{ route('login') }}" wire:navigate class="btn btn-primary btn-sm">Log in</a>
            @endguest
        </div>
    </div>
</nav>
