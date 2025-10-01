<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    /** @use HasFactory<\Database\Factories\OfficeFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'office_name',
        'office_description',
        'office_head',
    ];

    /**
     * Users assigned to this office
     */
    public function users()
    {
        return $this->hasMany(User::class, 'office_id');
    }

    /**
     * Office approvals made by this office
     */
    public function officeApprovals()
    {
        return $this->hasMany(Office_Approval::class, 'office_id');
    }
}
