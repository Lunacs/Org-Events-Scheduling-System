<?php

namespace App\Livewire\Osa;

use App\Livewire\Components\EventCalendar as ComponentsEventCalendar;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class EventCalendar extends ComponentsEventCalendar
{
    #[Title('Event Calendar - OSA Admin')]
    #[Layout('components.layouts.app')]

    // All functionality is inherited from ComponentsEventCalendar
    protected function getRoleSpecificData(): array
    {
        return [];
    }
}
