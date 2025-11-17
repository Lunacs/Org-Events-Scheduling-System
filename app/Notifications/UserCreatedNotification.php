<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification
{
    use Queueable;

    protected $createdUser;

    protected $createdBy;

    public function __construct(User $createdUser, User $createdBy)
    {
        $this->createdUser = $createdUser;
        $this->createdBy = $createdBy;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $roleMap = [
            'osa' => 'OSA Admin',
            'gso' => 'GSO Admin',
            'student-org' => 'Student Organization',
            'superadmin' => 'Super Administrator',
        ];

        $roleName = $this->createdUser->role ? $this->createdUser->role->role_name : 'Unknown';
        $roleDisplay = $roleMap[$roleName] ?? $roleName;

        return [
            'type' => 'user_created',
            'title' => 'New User Account Created',
            'message' => "A new {$roleDisplay} account has been created for {$this->createdUser->name}.",
            'color' => 'success',
            'user_id' => $this->createdUser->user_id,
            'user_name' => $this->createdUser->name,
            'user_email' => $this->createdUser->email,
            'user_role' => $roleDisplay,
            'created_by' => $this->createdBy->name,
            'created_by_id' => $this->createdBy->user_id,
            'action_url' => route('superadmin.users'),
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
