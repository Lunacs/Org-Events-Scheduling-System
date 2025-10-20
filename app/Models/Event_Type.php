<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event_Type extends Model
{
    /** @use HasFactory<\Database\Factories\EventTypeFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event__types';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'event_type_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_name',
        'description',
    ];

    /**
     * Tickets with this event type
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'event_type_id');
    }

    /**
     * Events with this event type
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'event__type_id');
    }
}
