@props(['user', 'roleColor' => 'primary'])

{{--
    x-profile.identity-header — shared profile page banner.

    Solid token-color banner (not a gradient — primary/secondary are the same navy
    value in this theme, so a "from-primary to-secondary" gradient renders as flat
    navy anyway). `roleColor` lets each role tint the banner subtly without
    duplicating markup across profile pages.
--}}
<div class="bg-{{ $roleColor }} rounded-box shadow-lg p-6 text-{{ $roleColor }}-content">
    <div class="flex items-center gap-4">
        <div wire:key="profile-header-avatar-{{ $user->avatar_style }}-{{ $user->avatar_seed }}">
            <x-ui.avatar :user="$user" size="2xl" class="ring-4 ring-base-100/30" nav="false" />
        </div>
        <div class="min-w-0">
            <h1 class="text-xl sm:text-3xl font-bold break-words">{{ $user->name }}</h1>
            <p class="opacity-80 mt-1 text-sm sm:text-base break-words">{{ $user->email }}</p>
            <div class="flex gap-2 mt-2">
                <span class="badge badge-lg bg-base-100/20 border-0" style="color: inherit;">
                    {{ $user->role_display }}
                </span>
                @if ($user->email_verified_at)
                    <span class="badge badge-lg badge-success text-white">
                        <x-ui.icon name="s-check-circle" class="w-4 h-4 mr-1" />
                        Verified
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
