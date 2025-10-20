<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    /** @use HasFactory<\Database\Factories\OfficeFactory> */
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'office_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'office_code',
        'office_name',
        'description',
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
