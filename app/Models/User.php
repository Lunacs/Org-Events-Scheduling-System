<?php

namespace App\Models;

use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * In-memory cache for role IDs to prevent duplicate cache queries within the same request
     *
     * @var array<string, int|null>
     */
    protected static array $roleIdCache = [];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'org_id',
        'office_id',
        'avatar',
        'avatar_style',
        'avatar_seed',
        'avatar_preference',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get role ID by role name (cached for performance)
     * Uses in-memory cache first to prevent duplicate queries within the same request,
     * then falls back to persistent cache for cross-request caching.
     */
    public static function getRoleId(string $roleName): ?int
    {
        // Check in-memory cache first (prevents duplicate queries in same request)
        if (isset(static::$roleIdCache[$roleName])) {
            return static::$roleIdCache[$roleName];
        }

        // Get from persistent cache (or database if not cached)
        $roleId = Cache::rememberForever("role_id_{$roleName}", function () use ($roleName) {
            return Roles::where('role_name', $roleName)->value('role_id');
        });

        // Store in in-memory cache for this request
        static::$roleIdCache[$roleName] = $roleId;

        return $roleId;
    }

    /**
     * Preload commonly used role IDs to prevent duplicate cache queries
     * Call this method early in the request lifecycle (e.g., in middleware or service provider)
     *
     * @param  array<string>  $roleNames  Role names to preload (defaults to common roles)
     */
    public static function preloadRoleIds(array $roleNames = ['osa', 'student-org', 'gso', 'superadmin']): void
    {
        foreach ($roleNames as $roleName) {
            // This will populate both in-memory and persistent cache
            static::getRoleId($roleName);
        }
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role_id === self::getRoleId($roleName);
    }

    /**
     * Check if user is a superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Check if user is an OSA admin
     */
    public function isOSA(): bool
    {
        return $this->hasRole('osa');
    }

    /**
     * Check if user is a GSO admin
     */
    public function isGSO(): bool
    {
        return $this->hasRole('gso');
    }

    /**
     * Check if user is a student organization member
     */
    public function isStudentOrg(): bool
    {
        return $this->hasRole('student-org');
    }

    /**
     * Get the user's dashboard route based on their role
     */
    public function getDashboardRoute(): string
    {
        // Load role relationship if not already loaded
        if (! $this->relationLoaded('role')) {
            $this->load('role');
        }

        return match ($this->role?->role_name) {
            'superadmin' => 'superadmin.dashboard',
            'osa' => 'admin.dashboard',
            'gso' => 'gso.dashboard',
            'student-org' => 'student-org.dashboard',
            default => 'admin.dashboard',
        };
    }

    /**
     * Relationship to Student Organization
     */
    public function studentOrganization()
    {
        return $this->belongsTo(Student_Organization::class, 'org_id');
    }

    public function role()
    {
        return $this->belongsTo(Roles::class, 'role_id');
    }

    public function position()
    {
        return $this->belongsTo(Positions::class, 'position_id');
    }

    /**
     * Relationship to Office
     */
    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    /**
     * Tickets created by this user
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    /**
     * OSA approvals made by this user
     */
    public function osaApprovals()
    {
        return $this->hasMany(OSA_Approval::class, 'user_id');
    }

    /**
     * Office approvals made by this user
     */
    public function officeApprovals()
    {
        return $this->hasMany(Office_Approval::class, 'user_id');
    }

    /**
     * Transaction logs for this user
     */
    public function transactionLogs()
    {
        return $this->hasMany(Transaction_Logs::class, 'user_id');
    }

    /**
     * Get the user's last login from transaction logs
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getLastLoginAttribute()
    {
        $lastLogin = $this->transactionLogs()
            ->where('action', 'AUTH_LOGIN')
            ->latest('created_at')
            ->first();

        return $lastLogin?->created_at;
    }

    /**
     * Get the user's avatar URL based on their preference.
     * Returns uploaded photo URL if preference is 'uploaded' and avatar exists,
     * otherwise returns DiceBear avatar format.
     */
    public function getAvatarUrlAttribute(): string
    {
        // Check if user prefers uploaded photo and has one
        if ($this->avatar_preference === 'uploaded' && $this->avatar) {
            // Check if the file exists in storage
            if (\Storage::disk('public')->exists($this->avatar)) {
                return asset('storage/' . $this->avatar);
            }
        }

        // Default to DiceBear avatar
        $style = $this->avatar_style ?? 'big-ears';
        $seed = $this->avatar_seed ?? $this->email;

        return "dicebear:{$style}:{$seed}";
    }

    /**
     * Get the user's formatted role name (for display)
     */
    public function getRoleDisplayAttribute(): string
    {
        // Load role relationship if not already loaded
        if (! $this->relationLoaded('role')) {
            $this->load('role');
        }

        $roleName = $this->role?->role_name ?? 'unknown';

        return ucfirst(str_replace('-', ' ', $roleName));
    }

    public function getRoleDisplayName(string $role): string
    {
        return ucfirst(str_replace('-', ' ', $role));
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }
}
