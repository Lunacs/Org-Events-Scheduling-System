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
        'venue_requested',
        'date-requested',
        'status',
        'plv_participants',
        'external_participants',
        'total_participants',
        'sponsoring_body',
        'alternate_venue',
        'special_requirements',
        'date_from',
        'date_to',
        'time_from',
        'time_to',
        'estimated_budget',
        'budget_breakdown',
        'additional_notes',
        'proponent_contact',
        'adviser_contact',
        'igp_requested',
        'igp_details',
        'oc_accommodation',
        'oc_tsp',
        'oc_driver_name',
        'oc_vehicle_type',
        'oc_vehicle_plate_number',
        'oc_driver_contact_number',
        'fund_source_id',
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

    public function fundSources()
    {
        return $this->hasMany(Fund_Sources::class, 'fund_source_id');
    }
}
