@props([
    'label' => 'Password',
    'model' => '',
    'placeholder' => 'Enter password',
    'hint' => null,
    'required' => false,
])

@php
    $showVar = 'show' . str_replace(['.', '-'], '_', $model);
@endphp

<div x-data="passwordStrength(@entangle($model), '{{ $showVar }}')" class="space-y-2">
    <div class="relative">
        <x-ui.input :label="$label" wire:model.live.blur="{{ $model }}" x-bind:type="show ? 'text' : 'password'"
            :placeholder="$placeholder" icon="o-lock-closed" :hint="$hint" :required="$required" />
        <button type="button" x-on:click="show = !show"
            class="absolute right-3 top-9 h-10 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
            tabindex="-1">
            <x-ui.icon name="o-eye" class="w-5 h-5" x-show="show" />
            <x-ui.icon name="o-eye-slash" class="w-5 h-5" x-show="!show" />
        </button>
    </div>

    {{-- Password Strength Indicator --}}
    <div x-show="password && password.length > 0" x-transition x-collapse x-cloak class="space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-medium text-base-content/70">Password Strength:</span>
            <span class="font-semibold"
                x-bind:class="{
                    'text-error': strength <= 2,
                    'text-warning': strength === 3,
                    'text-info': strength === 4,
                    'text-success': strength === 5
                }"
                x-text="strengthLabel"></span>
        </div>
        <div class="w-full bg-base-200 rounded-full h-2 overflow-hidden">
            <div class="h-full transition-all duration-300" x-bind:class="strengthColor"
                x-bind:style="`width: ${(strength / 5) * 100}%`"></div>
        </div>

        {{-- Requirements Checklist --}}
        <div class="grid grid-cols-1 gap-1 text-xs mt-2">
            <div class="flex items-center gap-2" x-bind:class="hasMinLength ? 'text-success' : 'text-base-content/50'">
                <i class="fas fa-check-circle text-xs" x-show="hasMinLength"></i>
                <i class="fas fa-circle text-xs" x-show="!hasMinLength"></i>
                <span>At least 8 characters</span>
            </div>
            <div class="flex items-center gap-2" x-bind:class="hasUpperCase ? 'text-success' : 'text-base-content/50'">
                <i class="fas fa-check-circle text-xs" x-show="hasUpperCase"></i>
                <i class="fas fa-circle text-xs" x-show="!hasUpperCase"></i>
                <span>One uppercase letter</span>
            </div>
            <div class="flex items-center gap-2" x-bind:class="hasLowerCase ? 'text-success' : 'text-base-content/50'">
                <i class="fas fa-check-circle text-xs" x-show="hasLowerCase"></i>
                <i class="fas fa-circle text-xs" x-show="!hasLowerCase"></i>
                <span>One lowercase letter</span>
            </div>
            <div class="flex items-center gap-2" x-bind:class="hasNumber ? 'text-success' : 'text-base-content/50'">
                <i class="fas fa-check-circle text-xs" x-show="hasNumber"></i>
                <i class="fas fa-circle text-xs" x-show="!hasNumber"></i>
                <span>One number</span>
            </div>
            <div class="flex items-center gap-2" x-bind:class="hasSymbol ? 'text-success' : 'text-base-content/50'">
                <i class="fas fa-check-circle text-xs" x-show="hasSymbol"></i>
                <i class="fas fa-circle text-xs" x-show="!hasSymbol"></i>
                <span>One symbol (!@#$%^&*...)</span>
            </div>
        </div>
    </div>
</div>
