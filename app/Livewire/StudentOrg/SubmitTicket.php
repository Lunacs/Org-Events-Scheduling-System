<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class SubmitTicket extends Component
{
    #[Title('Student Organization Dashboard')]
    #[Layout('layouts.student-org-layout')]


    public $search = '';
    public $statusFilter = '';
    public $dateFilter = '';

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateFilter = '';
    }
    public function render()
    {
        return view('livewire.student-org.submit-ticket');
    }
}
