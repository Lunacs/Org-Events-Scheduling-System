<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student_Organization extends Model
{
    /** @use HasFactory<\Database\Factories\StudentOrganizationFactory> */
    use HasFactory;

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
    ];

    /**
     * Course that this organization belongs to
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * User assigned to this organization
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Users who belong to this organization
     */
    public function users()
    {
        return $this->hasMany(User::class, 'org_id');
    }
}
