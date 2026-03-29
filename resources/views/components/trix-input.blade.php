@props(['id', 'name', 'value' => ''])

<input
    type="hidden"
    name="{{ $name }}"
    id="{{ $id }}_input"
    value="{{ $value }}"
/>

<trix-toolbar
    class="[&_.trix-button]:bg-base-100 [&_.trix-button.trix-active]:bg-base-300"
    id="{{ $id }}_toolbar"
    wire:ignore
></trix-toolbar>

<trix-editor
    id="{{ $id }}"
    toolbar="{{ $id }}_toolbar"
    input="{{ $id }}_input"
    {{ $attributes->merge(['class' => 'trix-content border-base-300 bg-base-100 text-base-content focus:ring-1 focus:border-primary focus:ring-primary rounded-md shadow-sm dark:[&_pre]:!bg-base-300 dark:[&_pre]:rounded dark:[&_pre]:!text-base-content']) }}
></trix-editor>
