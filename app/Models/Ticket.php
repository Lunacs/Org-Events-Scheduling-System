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
        'proponent_contact',
        'adviser_contact',
        'plv_participants',
        'external_participants',
        'total_participants',
        'venue_requested',
        'alternate_venue',
        'special_requirements',
        'igp_requested',
        'igp_details',
        'oc_accommodation',
        'oc_tsp',
        'oc_driver_name',
        'oc_vehicle_type',
        'oc_vehicle_plate_number',
        'oc_driver_contact_number',
        'date_from',
        'date_to',
        'time_from',
        'time_to',
        'estimated_budget',
        'budget_breakdown',
        'additional_notes',
        'fund_source_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'igp_requested' => 'boolean',
        'estimated_budget' => 'float',
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
     * OSA approvals for this ticket (history of all OSA actions)
     * Ordered by most recent first
     */
    public function osaApprovals()
    {
        return $this->hasMany(OSA_Approval::class, 'ticket_id')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get the latest OSA approval decision
     */
    public function latestOsaApproval()
    {
        // Use the correct primary key for OSA_Approval to determine the latest record
        return $this->hasOne(OSA_Approval::class, 'ticket_id')
                    ->latestOfMany('osa_approval_id');
    }

    /**
     * Office approvals for this ticket (history of all office actions)
     * Ordered by most recent first
     */
    public function officeApprovals()
    {
        return $this->hasMany(Office_Approval::class, 'ticket_id')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get the latest office approval decision
     */
    public function latestOfficeApproval()
    {
        return $this->hasOne(Office_Approval::class, 'ticket_id')
                    ->latestOfMany();
    }

    /**
     * Attachments for this ticket
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'ticket_id');
    }

    public function fundSource()
    {
        return $this->belongsTo(Fund_Sources::class, 'fund_source_id', 'source_id');
    }

    /**
     * Comments for this ticket
     */
    public function comments()
    {
        return $this->hasMany(TicketComment::class, 'ticket_id');
    }

    /**
     * Check if ticket is ready for final OSA approval
     */
    public function isReadyForFinalApproval()
    {
        return $this->status === 'pending_osa_approval';
    }

    /**
     * Check if ticket needs GSO approval
     */
    public function needsGsoApproval()
    {
        return in_array($this->status, ['received', 'amended']) && 
               ($this->venue_requested || $this->special_requirements);
    }

    /**
     * Get the complete approval history combining OSA and Office approvals
     * Sorted by most recent first
     */
    public function getApprovalHistoryAttribute()
    {
        $allApprovals = collect();

        // Add OSA approvals
        foreach ($this->osaApprovals as $approval) {
            $allApprovals->push([
                'type' => 'OSA',
                'office_name' => 'Office of Student Affairs',
                'user' => $approval->user,
                'decision' => $approval->decision,
                'remarks' => $approval->remarks,
                'created_at' => $approval->created_at,
            ]);
        }

        // Add Office approvals
        foreach ($this->officeApprovals as $approval) {
            $allApprovals->push([
                'type' => 'Office',
                'office_name' => $approval->office->office_name ?? 'Unknown Office',
                'user' => $approval->user,
                'decision' => $approval->decision,
                'remarks' => $approval->remarks,
                'created_at' => $approval->created_at,
            ]);
        }

        return $allApprovals->sortByDesc('created_at');
    }

    /**
     * Check if ticket has been forwarded to GSO
     */
    public function hasBeenForwardedToGso()
    {
        return $this->osaApprovals()
                    ->where('decision', 'forwarded')
                    ->exists();
    }

    /**
     * Check if ticket has revision requests
     */
    public function hasRevisionRequests()
    {
        return $this->osaApprovals()
                    ->where('decision', 'revision_requested')
                    ->exists();
    }
}
