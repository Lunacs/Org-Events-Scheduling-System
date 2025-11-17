<?php

namespace App\Livewire\Osa;

use App\Models\Student_Organization;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Communication extends Component
{
    use WithPagination;

    #[Title('Communication & Notifications - OSA Admin')]
    #[Layout('components.layouts.app')]
    public $showComposeModal = false;

    public $recipientType = 'organization';

    public $selectedOrganization = '';

    public $selectedUser = '';

    public $subject = '';

    public $message = '';

    public $priority = 'normal';

    #[Url(except: '')]
    public $search = '';

    #[Url(except: '')]
    public $typeFilter = '';

    protected $rules = [
        'recipientType' => 'required|in:organization,individual,all',
        'selectedOrganization' => 'required_if:recipientType,organization',
        'selectedUser' => 'required_if:recipientType,individual',
        'subject' => 'required|string|max:255',
        'message' => 'required|string|max:2000',
        'priority' => 'required|in:low,normal,high,urgent',
    ];

    public function openComposeModal()
    {
        $this->showComposeModal = true;
    }

    public function closeComposeModal()
    {
        $this->showComposeModal = false;
        $this->reset(['recipientType', 'selectedOrganization', 'selectedUser', 'subject', 'message', 'priority']);
    }

    public function sendMessage()
    {
        $this->validate();

        // Logic to send notification/message
        // This would integrate with your notification system

        session()->flash('message', 'Message sent successfully!');
        $this->closeComposeModal();
    }

    public function render()
    {
        // Get recent communications/notifications
        $communications = collect(); // Replace with actual communication history model

        return view('livewire.osa.communication', [
            'organizations' => $this->organizations,
            'users' => $this->users,
            'communications' => $communications,
        ]);
    }

    #[Computed(persist: true, seconds: 3600)]
    public function organizations()
    {
        return \Illuminate\Support\Facades\Cache::remember('osa_communication_organizations', 3600, function () {
            return Student_Organization::select(['org_id', 'org_name', 'org_code'])
                ->where('status', 'active')
                ->orderBy('org_name')
                ->get();
        });
    }

    #[Computed(persist: true, seconds: 3600)]
    public function users()
    {
        return \Illuminate\Support\Facades\Cache::remember('osa_communication_users', 3600, function () {
            return User::select(['user_id', 'name', 'email', 'org_id'])
                ->where('role_id', User::getRoleId('student-org'))
                ->with('studentOrganization:org_id,org_name,logo')
                ->orderBy('name')
                ->get();
        });
    }
}
