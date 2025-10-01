<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'event_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticket_id',
        'event__type_id',
        'notes',
    ];

    /**
     * Ticket that this event belongs to
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * Event type for this event
     */
    public function eventType()
    {
        return $this->belongsTo(Event_Type::class, 'event__type_id');
    }

    /**
     * Event schedules for this event
     */
    public function eventSchedules()
    {
        return $this->hasMany(Event_Schedule::class, 'event_id');
    }
}
