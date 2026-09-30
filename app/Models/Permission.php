<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
    /**
     * Dot-notated permission names, e.g. `tickets.view`.
     */
    public const NAME_PATTERN = '/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/';

    /**
     * Cache key holding every permission name registered as a Gate.
     */
    public const NAMES_CACHE_KEY = 'rbac_permission_names';

    protected $table = 'permissions';

    protected $primaryKey = 'permission_id';

    protected $fillable = [
        'name',
        'label',
        'group',
    ];

    protected static function booted(): void
    {
        static::saved(static fn () => self::forgetCachedNames());
        static::deleted(static fn () => self::forgetCachedNames());
    }

    /**
     * Forget the cached permission names now and again once any open
     * transaction commits, so a concurrent rebuild cannot keep stale names.
     */
    public static function forgetCachedNames(): void
    {
        Cache::forget(self::NAMES_CACHE_KEY);

        DB::afterCommit(static fn (): bool => Cache::forget(self::NAMES_CACHE_KEY));
    }

    /**
     * Determine whether an ability belongs to the RBAC permission namespace.
     *
     * Permission abilities are dot-notated, while model policy abilities
     * (e.g. `approve`, `viewAny`) never contain a dot.
     */
    public static function isPermissionAbility(string $ability): bool
    {
        return str_contains($ability, '.');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Roles::class, 'role_permission', 'permission_id', 'role_id');
    }
}
