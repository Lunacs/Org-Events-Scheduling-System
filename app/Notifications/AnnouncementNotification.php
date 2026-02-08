<?php

namespace App\Notifications;

use App\Models\ContentSection;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AnnouncementNotification extends Notification
{
    use Queueable;

    protected ContentSection $announcement;
    protected User $createdBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(ContentSection $announcement, User $createdBy)
    {
        $this->announcement = $announcement;
        $this->createdBy = $createdBy;
    }

    /**
     * Get the notification's delivery channels.
     * Only database (in-app) notifications, no email
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'announcement',
            'title' => 'New Announcement',
            'message' => $this->announcement->title,
            'content' => strip_tags($this->announcement->content?->toHtml() ?? ''),
            'color' => 'info',
            'announcement_id' => $this->announcement->id,
            'announcement_key' => $this->announcement->section_key,
            'created_by' => $this->createdBy->name,
            'created_by_id' => $this->createdBy->user_id,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
