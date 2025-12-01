<?php

namespace App\Notifications;

use App\Models\Student_Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrganizationCreatedNotification extends Notification
{
    use Queueable;

    protected $organization;
    protected $createdBy;

    public function __construct(Student_Organization $organization, User $createdBy)
    {
        $this->organization = $organization;
        $this->createdBy = $createdBy;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'organization_created',
            'title' => 'New Student Organization Added',
            'message' => "Student organization '{$this->organization->org_name}' ({$this->organization->org_code}) has been created.",
            'color' => 'info',
            'org_id' => $this->organization->org_id,
            'org_name' => $this->organization->org_name,
            'org_code' => $this->organization->org_code,
            'created_by' => $this->createdBy->name,
            'created_by_id' => $this->createdBy->user_id,
            'action_url' => $notifiable->isSuperAdmin() ? route('superadmin.system-settings') : null,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}

