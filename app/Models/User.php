<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // User roles constants
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_OSA = 'osa';
    const ROLE_GSO = 'gso';
    const ROLE_STUDENT_ORG = 'student_org';

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
        'role',
        'org_id',
        'office_id',
        'avatar',
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
        return $this->role === self::ROLE_SUPERADMIN;
    }

    /**
     * Check if user is an OSA admin
     *
     * @return bool
     */
    public function isOSA(): bool
    {
        return $this->role === self::ROLE_OSA;
    }

    /**
     * Check if user is a GSO admin
     *
     * @return bool
     */
    public function isGSO(): bool
    {
        return $this->role === self::ROLE_GSO;
    }

    /**
     * Check if user is a student organization member
     *
     * @return bool
     */
    public function isStudentOrg(): bool
    {
        return $this->role === self::ROLE_STUDENT_ORG;
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
