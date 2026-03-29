<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Tonysm\RichTextLaravel\Models\Traits\HasRichText;

class ContentSection extends Model
{
    use HasRichText;

    // Section type constants
    const TYPE_ANNOUNCEMENT = 'announcement';

    const TYPE_TERMS_CONDITIONS = 'terms_conditions';

    const TYPE_TICKET_GUIDELINES = 'ticket_guidelines';

    const TYPE_RESCHEDULE_GUIDELINES = 'reschedule_guidelines';

    const TYPE_PAGE_CONTENT = 'page_content';

    private const TYPE_CONFIG = [
        self::TYPE_ANNOUNCEMENT => [
            'name' => 'Announcement',
            'icon' => 'o-megaphone',
            'color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        ],
        self::TYPE_TERMS_CONDITIONS => [
            'name' => 'Terms & Conditions',
            'icon' => 'o-document-check',
            'color' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        ],
        self::TYPE_TICKET_GUIDELINES => [
            'name' => 'Ticket Guidelines',
            'icon' => 'o-clipboard-document-list',
            'color' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        ],
        self::TYPE_RESCHEDULE_GUIDELINES => [
            'name' => 'Reschedule Request Guidelines',
            'icon' => 'o-arrow-path',
            'color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        ],
        self::TYPE_PAGE_CONTENT => [
            'name' => 'Page Content',
            'icon' => 'o-document-text',
            'color' => 'bg-slate-100 text-slate-700 dark:bg-slate-900/30 dark:text-slate-400',
        ],
    ];

    /**
     * The rich text attributes that should be treated as rich text.
     *
     * @var array<int|string, string>
     */
    protected $richTextAttributes = [
        'content',
    ];

    protected $fillable = [
        'section_key',
        'section_type',
        'title',
        'content',
        'is_active',
        'display_order',
        'target_roles',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target_roles' => 'array',
    ];

    /**
     * Get all available section types with user-friendly labels
     */
    public static function getSectionTypes(): array
    {
        return collect(self::TYPE_CONFIG)
            ->map(fn (array $config, string $type) => ['id' => $type, 'name' => $config['name']])
            ->values()
            ->all();
    }

    /**
     * Get section type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_CONFIG[$this->section_type]['name']
            ?? ucfirst(str_replace('_', ' ', $this->section_type));
    }

    /**
     * Get section type icon
     */
    public function getTypeIconAttribute(): string
    {
        return self::TYPE_CONFIG[$this->section_type]['icon'] ?? 'o-document';
    }

    /**
     * Get section type color for badges
     */
    public function getTypeColorAttribute(): string
    {
        return self::TYPE_CONFIG[$this->section_type]['color']
            ?? 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400';
    }

    /**
     * Cached retrieval by section key.
     * Null results are never cached so a newly created record is always visible immediately.
     */
    public static function getByKey(string $key): ?self
    {
        $cacheKey = "content_section_{$key}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $section = static::where('section_key', $key)
            ->where('is_active', true)
            ->first();

        if ($section) {
            Cache::put($cacheKey, $section, 300);
        }

        return $section;
    }

    /**
     * Get all active sections of a specific type.
     * Empty collections are never cached so newly created records are always visible immediately.
     */
    public static function getActiveByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "content_sections_type_{$type}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $sections = static::where('section_type', $type)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        if ($sections->isNotEmpty()) {
            Cache::put($cacheKey, $sections, 300);
        }

        return $sections;
    }

    /**
     * Get available target role options for announcements
     */
    public static function getTargetRoleOptions(): array
    {
        return [
            ['id' => 'student-org', 'name' => 'Student Organizations'],
            ['id' => 'osa', 'name' => 'OSA Staff'],
            ['id' => 'gso', 'name' => 'GSO Staff'],
            ['id' => 'superadmin', 'name' => 'Superadmins'],
        ];
    }

    /**
     * Check if this content section should be visible to a specific user
     * Returns true if:
     * - target_roles is null/empty (show to all users)
     * - The user's role is in the target_roles array
     * - The user is a guest and target_roles is null/empty
     */
    public function isVisibleToUser(?User $user): bool
    {
        // If no target roles specified, show to everyone
        if (empty($this->target_roles)) {
            return true;
        }

        // If user is not logged in, don't show role-targeted announcements
        if (! $user) {
            return false;
        }

        // Load role if not already loaded
        if (! $user->relationLoaded('role')) {
            $user->load('role');
        }

        $userRole = $user->role?->role_name;

        // Check if user's role is in target roles
        return in_array($userRole, $this->target_roles);
    }

    /**
     * Get active sections of a specific type, filtered by user role
     */
    public static function getActiveByTypeForUser(string $type, ?User $user): \Illuminate\Support\Collection
    {
        return static::getActiveByType($type)->filter(function ($section) use ($user) {
            return $section->isVisibleToUser($user);
        });
    }

    /**
     * Clear cache when updated
     */
    protected static function booted(): void
    {
        static::saved(function ($section) {
            Cache::forget("content_section_{$section->section_key}");
            Cache::forget("content_sections_type_{$section->section_type}");
            Cache::forget('content_sections');
        });

        static::deleted(function ($section) {
            Cache::forget("content_section_{$section->section_key}");
            Cache::forget("content_sections_type_{$section->section_type}");
            Cache::forget('content_sections');
        });
    }
}
