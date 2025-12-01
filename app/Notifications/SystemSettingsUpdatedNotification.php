<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemSettingsUpdatedNotification extends Notification
{
    use Queueable;

    protected $settingType;
    protected $settingName;
    protected $action;
    protected $updatedBy;

    /**
     * @param string $settingType Type of setting (event_type, venue, academic_year, etc.)
     * @param string $settingName Name of the setting that was changed
     * @param string $action Action performed (created, updated, deleted)
     * @param User $updatedBy User who made the change
     */
    public function __construct(string $settingType, string $settingName, string $action, User $updatedBy)
    {
        $this->settingType = $settingType;
        $this->settingName = $settingName;
        $this->action = $action;
        $this->updatedBy = $updatedBy;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $actionMap = [
            'created' => 'added',
            'updated' => 'updated',
            'deleted' => 'removed',
        ];

        $actionText = $actionMap[$this->action] ?? $this->action;

        $settingTypeMap = [
            'event_type' => 'Event Type',
            'venue' => 'Venue',
            'academic_year' => 'Academic Year',
            'organization' => 'Student Organization',
            'course' => 'Course',
            'office' => 'Office',
            'position' => 'Position',
            'fund_source' => 'Fund Source',
        ];

        $settingDisplay = $settingTypeMap[$this->settingType] ?? ucwords(str_replace('_', ' ', $this->settingType));

        $colorMap = [
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'error',
        ];

        return [
            'type' => 'system_settings_updated',
            'title' => 'System Settings Modified',
            'message' => "{$settingDisplay} '{$this->settingName}' has been {$actionText}.",
            'color' => $colorMap[$this->action] ?? 'info',
            'setting_type' => $this->settingType,
            'setting_name' => $this->settingName,
            'action' => $this->action,
            'updated_by' => $this->updatedBy->name,
            'updated_by_id' => $this->updatedBy->user_id,
            'action_url' => $notifiable->isSuperAdmin() ? route('superadmin.system-settings') : null,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}

