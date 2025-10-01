<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ticket_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticket_number',
        'user_id',
        'event_type_id',
        'title',
        'description',
        'venue-requested',
        'date-requested',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date-requested' => 'date',
    ];

    /**
     * User who created this ticket
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Event type for this ticket
     */
    public function eventType()
    {
        return $this->belongsTo(Event_Type::class, 'event_type_id');
    }

    /**
     * Events related to this ticket
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'ticket_id');
    }

    /**
     * OSA approvals for this ticket
     */
    public function osaApprovals()
    {
        return $this->hasMany(OSA_Approval::class, 'ticket_id');
    }

    /**
     * Office approvals for this ticket
     */
    public function officeApprovals()
    {
        return $this->hasMany(Office_Approval::class, 'ticket_id');
    }

    /**
     * Attachments for this ticket
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'ticket_id');
    }
}
