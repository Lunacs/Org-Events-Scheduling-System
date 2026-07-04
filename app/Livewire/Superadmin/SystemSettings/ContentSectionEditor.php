<?php

namespace App\Livewire\Superadmin\SystemSettings;

use App\Models\ContentSection;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class ContentSectionEditor extends Component
{
    use Toast;

    // Form data
    public $sectionId = null;
    public $sectionKey = '';
    public $title = '';
    public $sectionType = '';
    public $content = '';
    public $isActive = true;
    public $displayOrder = 0;
    public $targetRoles = []; // Array of role names for targeted announcements

    // Track if user manually edited the section key
    public $sectionKeyManuallyEdited = false;

    // Mode
    public $isEditing = false;

    #[Title('Content Section Editor')]
    #[Layout('components.layouts.superadmin')]

    public function mount($id = null)
    {
        if ($id) {
            $section = ContentSection::findOrFail($id);
            $this->sectionId = $section->id;
            $this->sectionKey = $section->section_key;
            $this->title = $section->title;
            $this->sectionType = $section->section_type;
            $this->content = $section->content?->toHtml() ?? '';
            $this->isActive = $section->is_active;
            $this->displayOrder = $section->display_order;
            $this->targetRoles = $section->target_roles ?? [];
            $this->isEditing = true;
            $this->sectionKeyManuallyEdited = true; // Don't auto-update when editing
        }
    }

    public function render()
    {
        return view('livewire.superadmin.system-settings.content-section-editor', [
            'sectionTypes' => ContentSection::getSectionTypes(),
            'roleOptions' => ContentSection::getTargetRoleOptions(),
        ]);
    }

    // Auto-generate section key from title (only if not manually edited)
    public function updatedTitle($value)
    {
        if (!$this->isEditing && !$this->sectionKeyManuallyEdited) {
            $this->sectionKey = Str::slug($value, '_');
        }
    }

    // Track when user manually edits the section key
    public function updatedSectionKey($value)
    {
        if (!$this->isEditing) {
            // Mark as manually edited if user types something different from auto-generated
            $this->sectionKeyManuallyEdited = $value !== Str::slug($this->title, '_');
        }
    }

    public function save()
    {
        $rules = [
            'sectionType' => 'required|string',
            'title' => 'required|string|max:255',
            'sectionKey' => 'required|string|max:100|regex:/^[a-z0-9_]+$/',
            'content' => 'nullable|string',
            'isActive' => 'boolean',
            'displayOrder' => 'integer|min:0',
            'targetRoles' => 'nullable|array',
            'targetRoles.*' => 'string|in:student-org,osa,gso,superadmin',
        ];

        // Add unique validation for section key
        if ($this->isEditing) {
            $rules['sectionKey'] .= '|unique:content_sections,section_key,' . $this->sectionId;
        } else {
            $rules['sectionKey'] .= '|unique:content_sections,section_key';
        }

        $this->validate($rules, [
            'sectionKey.regex' => 'Section key can only contain lowercase letters, numbers, and underscores.',
            'sectionKey.unique' => 'This section key is already in use.',
        ]);

        DB::beginTransaction();
        try {
            if ($this->isEditing) {
                $section = ContentSection::find($this->sectionId);
                $originalTitle = $section->title;
                $changes = [];

                if ($section->title !== $this->title) {
                    $changes[] = "Title: {$section->title} → {$this->title}";
                }
                if ($section->section_type !== $this->sectionType) {
                    $changes[] = "Type changed";
                }
                if (($section->content?->toHtml() ?? '') !== ($this->content ?? '')) {
                    $changes[] = "Content updated";
                }
                if ($section->is_active !== $this->isActive) {
                    $changes[] = "Status: " . ($this->isActive ? 'Activated' : 'Deactivated');
                }

                $section->update([
                    'section_key' => $this->sectionKey,
                    'section_type' => $this->sectionType,
                    'title' => $this->title,
                    'content' => $this->content,
                    'is_active' => $this->isActive,
                    'display_order' => $this->displayOrder,
                    'target_roles' => !empty($this->targetRoles) ? $this->targetRoles : null,
                ]);

                if (!empty($changes)) {
                    TransactionLogService::log(
                        'content_section_updated',
                        "Updated content section: {$originalTitle} (ID: {$section->id}). Changes: " . implode(', ', $changes)
                    );
                }

                $this->clearCache();
                $this->success('Content section updated successfully!', position: 'toast-top');
            } else {
                $section = ContentSection::create([
                    'section_key' => $this->sectionKey,
                    'section_type' => $this->sectionType,
                    'title' => $this->title,
                    'content' => $this->content,
                    'is_active' => $this->isActive,
                    'display_order' => $this->displayOrder ?: (ContentSection::max('display_order') + 1),
                    'target_roles' => !empty($this->targetRoles) ? $this->targetRoles : null,
                ]);

                TransactionLogService::log(
                    'content_section_created',
                    "Created content section: {$section->title} (ID: {$section->id})"
                );

                // Send notification to superadmins about content section creation
                $superadmins = User::where('role_id', User::getRoleId('superadmin'))->get();
                foreach ($superadmins as $admin) {
                    $admin->notify(new \App\Notifications\SystemSettingsUpdatedNotification(
                        'content_section',
                        $section->title,
                        'created',
                        auth()->user()
                    ));
                }

                // For announcements, also notify targeted users (in-app only, no email)
                if ($this->sectionType === ContentSection::TYPE_ANNOUNCEMENT && $this->isActive) {
                    $this->notifyAnnouncementRecipients($section);
                }

                $this->clearCache();
                $this->success('Content section created successfully!', position: 'toast-top');
            }

            DB::commit();

            // Redirect back to system settings with content tab active
            return $this->redirect(route('superadmin.system-settings', ['activeTab' => 'content']), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Content section save failed', ['error' => $e->getMessage()]);
            $this->error('Failed to save content section. Please try again.', position: 'toast-top');
        }
    }

    public function cancel()
    {
        return $this->redirect(route('superadmin.system-settings', ['activeTab' => 'content']), navigate: true);
    }

    protected function clearCache()
    {
        $sections = ContentSection::all();
        foreach ($sections as $section) {
            Cache::forget("content_section_{$section->section_key}");
            Cache::forget("content_sections_type_{$section->section_type}");
        }
    }

    /**
     * Send in-app notifications to users based on announcement target roles
     */
    protected function notifyAnnouncementRecipients(ContentSection $section): void
    {
        $targetRoles = $section->target_roles;
        $currentUser = auth()->user();

        // Build query for users to notify
        $usersQuery = User::query();

        if (!empty($targetRoles)) {
            // Get role IDs for the target roles
            $roleIds = [];
            foreach ($targetRoles as $roleName) {
                $roleId = User::getRoleId($roleName);
                if ($roleId) {
                    $roleIds[] = $roleId;
                }
            }

            if (empty($roleIds)) {
                return; // No valid roles found
            }

            $usersQuery->whereIn('role_id', $roleIds);
        }

        // Exclude the current user (they created the announcement)
        $usersQuery->where('user_id', '!=', $currentUser->user_id);

        // Send notifications in chunks to avoid memory issues
        $usersQuery->chunk(100, function ($users) use ($section, $currentUser) {
            foreach ($users as $user) {
                $user->notify(new AnnouncementNotification($section, $currentUser));
            }
        });
    }
}
