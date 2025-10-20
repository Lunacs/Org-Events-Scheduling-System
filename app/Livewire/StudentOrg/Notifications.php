<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

class Notifications extends Component
{
    #[Title('Notifications - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]

    public $search = '';
    public $typeFilter = '';
    public $statusFilter = '';

    public function markAllAsRead()
    {
        // Implement mark all as read logic
    }

    public function openNotificationSettings()
    {
        // Implement settings modal logic
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->statusFilter = '';
    }

    public function loadMore()
    {
        // Implement load more logic
    }

    public function render()
    {
        return view('livewire.student-org.notifications');
    }
}
