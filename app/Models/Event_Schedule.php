<?php

namespace App\Models;

use Database\Factories\EventSchedulesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event_Schedule extends Model
{
    /** @use HasFactory<EventSchedulesFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): EventSchedulesFactory
    {
        return EventSchedulesFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_schedules';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'schedule_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'venue',
        'status',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
    ];

    /**
     * Event that this schedule belongs to
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
