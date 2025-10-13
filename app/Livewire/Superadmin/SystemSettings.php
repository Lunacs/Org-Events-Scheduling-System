<?php

namespace App\Livewire\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

class SystemSettings extends Component
{
    #[Title('Superadmin - System Settings')]
    #[Layout('layouts.superadmin')]
    public function render()
    {
        return view('livewire.superadmin.system-settings');
    }

    public function officeOptions(): array
    {
        return [
            ['id' => 1, 'name' => 'OSA'],
            ['id' => 2, 'name' => 'GSO'],
            ['id' => 3, 'name' => 'Student Orgs'],
        ];
    }
    public function with(): array
    {
        return [
            'officeOptions' => $this->officeOptions()
        ];
    }
}
