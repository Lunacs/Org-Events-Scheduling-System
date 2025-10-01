<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event_Schedule extends Model
{
    /** @use HasFactory<\Database\Factories\EventSchedulesFactory> */
    use HasFactory;

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
        'schedule_date',
        'schedule_venue',
        'status',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'schedule_date' => 'datetime',
    ];

    /**
     * Event that this schedule belongs to
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
