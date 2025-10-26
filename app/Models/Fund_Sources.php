<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fund_Sources extends Model
{
    //

    protected $table = 'fund__sources';
    protected $primaryKey = 'source_id';
    protected $fillable = [
        'source_name',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'fund_source_id');
    }
}
