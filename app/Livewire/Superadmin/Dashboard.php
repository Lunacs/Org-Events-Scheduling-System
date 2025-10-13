<?php

namespace App\Livewire\Superadmin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{
    #[Title('Superadmin - Dashboard')]
    #[Layout('layouts.superadmin')]
    public function render()
    {
        return view('livewire.superadmin.dashboard');
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'request', 'label' => 'Request'],
            ['key' => 'type', 'label' => 'Type'],
            ['key' => 'submitted', 'label' => 'Submitted'],
            ['key' => 'status', 'label' => 'status']
        ];
    }

    public function rows()
    {
        return [
            [
                'id' => 'TKT-001',
                'request' => 'Annual Org Meeting',
                'type' => 'Fun Run',
                'status' => 'Pending',
                'submitted' => '2025-09-28',
            ],
            [
                'id' => 'TKT-002',
                'request' => 'Nigga meeting',
                'type' => 'Fun Run',
                'status' => 'Pending',
                'submitted' => '2025-09-29',
            ],
            [
                'id' => 'TKT-003',
                'request' => 'Blackest black meeting',
                'type' => 'Zumba',
                'status' => 'Pending',
                'submitted' => '2025-09-30',
            ]
        ];
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'rows' => $this->rows()
        ];
    }
}
