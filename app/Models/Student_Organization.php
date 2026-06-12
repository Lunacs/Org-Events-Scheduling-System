<?php

namespace App\Models;

use Database\Factories\StudentOrganizationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student_Organization extends Model
{
    /** @use HasFactory<StudentOrganizationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student__organizations';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'org_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'org_code',
        'org_name',
        'course_id',
        'adviser_name',
        'user_id',
        'status',
        'logo',
    ];

    /**
     * Course that this organization belongs to
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * User assigned to this organization
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Users who belong to this organization
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'org_id');
    }

    /**
     * Tickets created by users in this organization
     */
    public function tickets(): HasManyThrough
    {
        return $this->hasManyThrough(Ticket::class, User::class, 'org_id', 'user_id', 'org_id', 'user_id');
    }

    /**
     * Get the URL for the organization logo
     */
    public function getLogoUrlAttribute(): string
    {
        $diskName = config('filesystems.default') === 's3' ? 's3' : 'public';
        $disk = \Storage::disk($diskName);

        if ($this->logo && $disk->exists($this->logo)) {
            if ($diskName === 's3') {
                return $disk->temporaryUrl($this->logo, now()->addMinutes(30));
            }

            return $disk->url($this->logo);
        }

        return asset('images/default-org-logo.svg');
    }
}
