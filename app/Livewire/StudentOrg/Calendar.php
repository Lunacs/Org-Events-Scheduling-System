<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

class Calendar extends Component
{
    #[Title('Event Calendar - Student Organization')]
    #[Layout('layouts.student-org-layout')]

    public $eventTypeFilter = '';
    public $venueFilter = '';

    public function previousMonth()
    {
        // Implement month navigation logic
    }

    public function nextMonth()
    {
        // Implement month navigation logic
    }

    public function resetFilters()
    {
        $this->eventTypeFilter = '';
        $this->venueFilter = '';
    }

    public function render()
    {
        return view('livewire.student-org.calendar');
    }
}
