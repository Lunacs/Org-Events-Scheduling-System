<?php

use Illuminate\Support\Facades\Blade;

$input = Blade::render('<x-ui.input label="Name" wire:model.live="name" required placeholder="Full name" ::type="show ? \'text\' : \'password\'" value="hi" readonly />');
echo (str_contains($input, 'wire:model.live="name"') ? 'INPUT_WIRE_OK' : 'INPUT_WIRE_FAIL').PHP_EOL;
echo (str_contains($input, 'required') ? 'INPUT_REQUIRED_OK' : 'INPUT_REQUIRED_FAIL').PHP_EOL;
echo (str_contains($input, '<label for=') ? 'INPUT_FORLABEL_OK' : 'INPUT_FORLABEL_FAIL').PHP_EOL;
echo (str_contains($input, '::type=') ? 'INPUT_ALPINETYPE_OK' : 'INPUT_ALPINETYPE_FAIL').PHP_EOL;
echo (str_contains($input, 'value="hi"') ? 'INPUT_VALUE_OK' : 'INPUT_VALUE_FAIL').PHP_EOL;
echo (str_contains($input, 'readonly') ? 'INPUT_READONLY_OK' : 'INPUT_READONLY_FAIL').PHP_EOL;

$select = Blade::render('<x-ui.select label="Role" wire:model="role" :options="$opts" option-value="id" option-label="name" placeholder="Choose" />', [
    'opts' => [['id' => 1, 'name' => 'Admin'], ['id' => 2, 'name' => 'User']],
]);
echo (str_contains($select, 'wire:model="role"') ? 'SELECT_WIRE_OK' : 'SELECT_WIRE_FAIL').PHP_EOL;
echo (str_contains($select, '<option value="1"') && str_contains($select, 'Admin') ? 'SELECT_OPTIONS_OK' : 'SELECT_OPTIONS_FAIL').PHP_EOL;
echo (str_contains($select, 'Choose') ? 'SELECT_PLACEHOLDER_OK' : 'SELECT_PLACEHOLDER_FAIL').PHP_EOL;
echo (str_contains($select, '<label for=') ? 'SELECT_FORLABEL_OK' : 'SELECT_FORLABEL_FAIL').PHP_EOL;

$flat = Blade::render('<x-ui.select wire:model="x" :options="$o" />', ['o' => ['A', 'B']]);
echo (str_contains($flat, '<option value="A"') && str_contains($flat, '>A') !== false ? 'SELECT_FLAT_OK' : 'SELECT_FLAT_FAIL').PHP_EOL;

$toggle = Blade::render('<x-ui.toggle label="Active" wire:model="active" />');
echo (str_contains($toggle, 'wire:model="active"') ? 'TOGGLE_WIRE_OK' : 'TOGGLE_WIRE_FAIL').PHP_EOL;
echo (str_contains($toggle, 'type="checkbox"') && str_contains($toggle, 'toggle') ? 'TOGGLE_MARKUP_OK' : 'TOGGLE_MARKUP_FAIL').PHP_EOL;
echo (str_contains($toggle, '<label for=') ? 'TOGGLE_FORLABEL_OK' : 'TOGGLE_FORLABEL_FAIL').PHP_EOL;

echo 'ALL_RENDERED_OK'.PHP_EOL;
