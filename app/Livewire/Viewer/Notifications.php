<?php

namespace App\Livewire\Viewer;

use App\Livewire\Gso\Notifications as GsoNotifications;
use Illuminate\View\View;

class Notifications extends GsoNotifications
{
    public function render(): View
    {
        return parent::render()
            ->layout('components.layouts.viewer-layout')
            ->title('Notifications');
    }
}
