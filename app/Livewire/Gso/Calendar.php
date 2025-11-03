<?php

namespace App\Livewire\Gso;

use App\Livewire\Components\EventCalendar as ComponentsEventCalendar;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class Calendar extends ComponentsEventCalendar
{
    #[Title('Event Calendar - GSO')]
    #[Layout('components.layouts.gso-layout')]

    // All functionality is inherited from ComponentsEventCalendar
    protected function getRoleSpecificData(): array
    {
        return [];
    }
}
