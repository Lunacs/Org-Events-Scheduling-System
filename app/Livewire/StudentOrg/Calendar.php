<?php

namespace App\Livewire\StudentOrg;

use App\Livewire\Components\EventCalendar as ComponentsEventCalendar;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class Calendar extends ComponentsEventCalendar
{
    #[Title('Event Calendar - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]

    // All functionality is inherited from ComponentsEventCalendar
    protected function getRoleSpecificData(): array
    {
        return [];
    }
}
