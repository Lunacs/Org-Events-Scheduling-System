<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

class History extends Component
{
    #[Title('Event History - Student Organization')]
    #[Layout('layouts.student-org-layout')]

    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $yearFilter = '';

    public function exportReport()
    {
        // Implement export logic
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->typeFilter = '';
        $this->yearFilter = '';
    }

    public function render()
    {
        return view('livewire.student-org.history');
    }
}
