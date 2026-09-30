<?php

namespace App\Livewire\Viewer;

use App\Livewire\Components\EventCalendar as ComponentsEventCalendar;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class Calendar extends ComponentsEventCalendar
{
    #[Title('Event Calendar')]
    #[Layout('components.layouts.viewer-layout')]

    // All functionality is inherited from ComponentsEventCalendar
    protected function getRoleSpecificData(): array
    {
        return [];
    }
}
