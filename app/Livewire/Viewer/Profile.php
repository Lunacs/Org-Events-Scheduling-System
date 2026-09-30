<?php

namespace App\Livewire\Viewer;

use App\Livewire\Gso\Profile as GsoProfile;
use Illuminate\View\View;

class Profile extends GsoProfile
{
    public function render(): View
    {
        return parent::render()
            ->layout('components.layouts.viewer-layout')
            ->title('Profile');
    }
}
