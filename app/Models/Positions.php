<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Positions extends Model
{
    // Positions for student organization members

    protected $table = 'positions';
    protected $primaryKey = 'position_id';
    protected $fillable = [
        'position_name',
    ];

    public function users() : HasMany
    {
        return $this->hasMany(User::class, 'position_id');
    }
}
