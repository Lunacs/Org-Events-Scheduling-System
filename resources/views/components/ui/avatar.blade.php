@props(['user', 'size' => 'md', 'nav' => 'false', 'class' => ''])

@php
    $sizeClasses = [
        'xs' => 'w-6 h-6 text-xs',
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-12 h-12 text-lg',
        'xl' => 'w-16 h-16 text-xl',
        '2xl' => 'w-24 h-24 text-3xl',
    ];

    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $style = $user->avatar_style ?? 'big-ears';
    $seed = $user->avatar_seed ?? $user->email;
    $avatarData = "dicebear:{$style}:{$seed}";
@endphp

@if ($nav == 'true')
    <div class="avatar placeholder {{ $class }}">
        <div class="bg-base-300 text-base-content rounded-full {{ $sizeClass }}">
            <img data-avatar="{{ $avatarData }}" alt="{{ $user->name }}'s avatar"
                class="rounded-full w-full h-full object-cover" />
        </div>
    </div>
@else
    <div class="avatar placeholder {{ $class }}">
        <div class="bg-primary text-primary-content rounded-full {{ $sizeClass }}">
            <img data-avatar="{{ $avatarData }}" alt="{{ $user->name }}'s avatar"
                class="rounded-full w-full h-full object-cover" />
        </div>
    </div>
@endif
