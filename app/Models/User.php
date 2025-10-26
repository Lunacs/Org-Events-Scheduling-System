<?php

namespace App\Models;

use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // User roles constants
    const ROLE_SUPERADMIN = 1;
    const ROLE_OSA = 2;
    const ROLE_GSO = 3;
    const ROLE_STUDENT_ORG = 4;

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
     * Check if user is a superadmin
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role_id === self::ROLE_SUPERADMIN;
    }

    /**
     * Check if user is an OSA admin
     *
     * @return bool
     */
    public function isOSA(): bool
    {
        return $this->role_id === self::ROLE_OSA;
    }

    /**
     * Check if user is a GSO admin
     *
     * @return bool
     */
    public function isGSO(): bool
    {
        return $this->role_id === self::ROLE_GSO;
    }

    /**
     * Check if user is a student organization member
     *
     * @return bool
     */
    public function isStudentOrg(): bool
    {
        return $this->role_id === self::ROLE_STUDENT_ORG;
    }

    /**
     * Get the user's dashboard route based on their role
     *
     * @return string
     */
    public function getDashboardRoute(): string
    {
        return match ($this->role) {
            self::ROLE_SUPERADMIN => 'superadmin.dashboard',
            self::ROLE_OSA => 'admin.dashboard',
            self::ROLE_GSO => 'gso.dashboard',
            self::ROLE_STUDENT_ORG => 'student-org.dashboard',
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
     * Get the user's avatar URL using DiceBear
     */
    public function getAvatarUrlAttribute(): string
    {
        $style = $this->avatar_style ?? 'big-ears';
        $seed = $this->avatar_seed ?? $this->email;

        return "dicebear:{$style}:{$seed}";
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
