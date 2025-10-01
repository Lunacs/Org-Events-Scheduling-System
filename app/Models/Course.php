<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_code',
        'course_name',
        'department',
    ];

    /**
     * Student organizations under this course
     */
    public function studentOrganizations()
    {
        return $this->hasMany(Student_Organization::class, 'course_id');
    }
}
