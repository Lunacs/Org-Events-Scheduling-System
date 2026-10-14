<?php

namespace App\Models;

use App\Support\RolePortals;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roles extends Model
{
    /**
     * Built-in role slugs that portal routing depends on.
     *
     * @var list<string>
     */
    public const BUILT_IN_ROLE_NAMES = ['superadmin', 'osa', 'gso', 'student-org'];

    /**
     * The generic read-only portal used by custom roles without an office workflow.
     */
    public const VIEWER_PORTAL = 'viewer';

    /**
     * Base portals a custom role may be attached to, with their display labels.
     *
     * @var array<string, string>
     */
    public const SELECTABLE_PORTALS = [
        'viewer' => 'Viewer (read-only)',
        'osa' => 'OSA',
        'gso' => 'GSO',
        'student-org' => 'Student Organization',
    ];

    protected $table = 'roles';

    protected $primaryKey = 'role_id';

    protected $fillable = [
        'role_name',
        'is_protected',
        'base_portal',
    ];

    protected function casts(): array
    {
        return [
            'is_protected' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(static fn () => app(RolePortals::class)->flush());
        static::deleted(static fn () => app(RolePortals::class)->flush());
    }

    /**
     * The portal this role's users work in.
     *
     * Built-in roles always use their own portal; custom roles use their
     * configured base portal, falling back to the read-only viewer portal.
     */
    public function portal(): string
    {
        if (in_array($this->role_name, self::BUILT_IN_ROLE_NAMES, true)) {
            return $this->role_name;
        }

        return array_key_exists((string) $this->base_portal, self::SELECTABLE_PORTALS)
            ? $this->base_portal
            : self::VIEWER_PORTAL;
    }

    /**
     * Determine whether the role may not be renamed or deleted.
     *
     * Built-in roles are always protected, even when the seeder has not yet
     * flagged them, because portal routing depends on their slugs.
     */
    public function isProtected(): bool
    {
        return $this->is_protected || in_array($this->role_name, self::BUILT_IN_ROLE_NAMES, true);
    }

    /**
     * Resolve the portal of a role by its name, or null when no such role exists.
     */
    public static function portalForName(?string $roleName): ?string
    {
        $roleId = $roleName ? User::getRoleId($roleName) : null;

        return $roleId === null ? null : app(RolePortals::class)->portalFor($roleId);
    }

    /**
     * What the user would be missing to work in this role's portal: GSO users
     * act for an office and student-org users for an organization.
     *
     * @return 'office'|'organization'|null
     */
    public function missingAffiliationFor(User $user): ?string
    {
        return match ($this->portal()) {
            'gso' => $user->office_id ? null : 'office',
            'student-org' => $user->org_id ? null : 'organization',
            default => null,
        };
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission', 'role_id', 'permission_id');
    }
}
