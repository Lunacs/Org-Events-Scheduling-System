@props(['user', 'size' => 'md', 'nav' => 'false', 'class' => ''])

@php
use Illuminate\Support\Facades\Storage;

$sizeClasses = [
'xs' => 'w-6 h-6 text-xs',
'sm' => 'w-8 h-8 text-xs',
'md' => 'w-10 h-10 text-sm',
'lg' => 'w-12 h-12 text-lg',
'xl' => 'w-16 h-16 text-xl',
'2xl' => 'w-24 h-24 text-3xl',
];

$sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];

// Check if user prefers uploaded avatar and has one
$prefersUploadedAvatar = ($user->avatar_preference ?? 'dicebear') === 'uploaded';
$hasUploadedAvatar = $user->avatar && Storage::disk('public')->exists($user->avatar);
$useUploadedAvatar = $prefersUploadedAvatar && $hasUploadedAvatar;

if ($useUploadedAvatar) {
$avatarUrl = Storage::url($user->avatar);
} else {
// Fallback to DiceBear
$style = $user->avatar_style ?? 'big-ears';
$seed = $user->avatar_seed ?? $user->email;
$avatarData = "dicebear:{$style}:{$seed}";
}
@endphp

@if ($useUploadedAvatar)
{{-- Custom uploaded avatar --}}
<div class="avatar {{ $class }}">
    <div class="rounded-full {{ $sizeClass }}">
        <img src="{{ $avatarUrl }}" alt="{{ $user->name }}'s avatar"
            class="rounded-full w-full h-full object-cover" />
    </div>
</div>
@elseif ($nav == 'true')
{{-- DiceBear avatar for navigation --}}
<div class="avatar placeholder {{ $class }}">
    <div class="bg-base-300 text-base-content rounded-full {{ $sizeClass }}">
        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'%3E%3C/svg%3E"
            data-avatar="{{ $avatarData }}" alt="{{ $user->email }}'s avatar"
            class="rounded-full w-full h-full object-cover" />
    </div>
</div>
@else
{{-- DiceBear avatar default --}}
<div class="avatar placeholder {{ $class }}">
    <div class="bg-primary text-primary-content rounded-full {{ $sizeClass }}">
        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'%3E%3C/svg%3E"
            data-avatar="{{ $avatarData }}" alt="{{ $user->name }}'s avatar"
            class="rounded-full w-full h-full object-cover" />
    </div>
</div>
@endif