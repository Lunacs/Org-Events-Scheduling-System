@props(['disabled' => false])

<div x-data="{ show: false }" class="relative items-center">
    <input
        :type="show ? 'text' : 'password'"
        wire:model="form.password"
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm pr-10']) }}>

    <button
        type="button"
        @click="show = !show"
        class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
        <i class="fas" :class="{ 'fa-eye': show, 'fa-eye-slash': !show }"></i>
    </button>
</div>
<x-input-error :messages="$errors->get('form.password')" class="mt-2" />
