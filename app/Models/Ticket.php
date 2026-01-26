<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory, SoftDeletes;

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
        'venue_other',
        'alternate_venue',
        'alternate_venue_other',
        'special_requirements',
        'igp_requested',
        'igp_details',
        'oc_accommodation',
        'oc_tsp',
        'oc_driver_name',
        'oc_transportation_type',
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
        'content',
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
     * Venue requested for this ticket
     */
    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_requested');
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
     * Approval history for this ticket (immutable audit trail)
     * Ordered by most recent first
     */
    public function approvalHistory()
    {
        return $this->hasMany(ApprovalHistory::class, 'ticket_id')
            ->orderBy('created_at', 'desc');
    }

    public function latestComment()
    {
        return $this->hasOne(TicketComment::class, 'ticket_id')
            ->latest('updated_at');
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
     * Get the complete approval history from the approval_history table
     * Sorted by most recent first
     *
     * This method now uses the dedicated approval_history table instead of
     * combining osa_approvals and office_approvals for better performance
     * and to avoid bloat in those tables.
     */
    public function getApprovalHistoryAttribute()
    {
        return $this->approvalHistory()
            ->with('user')
            ->get()
            ->map(function ($history) {
                return [
                    'type' => strtoupper($history->approval_type),
                    'office_name' => $history->office_display_name,
                    'user' => $history->user,
                    'decision' => $history->action,
                    'remarks' => $history->remarks,
                    'created_at' => $history->created_at,
                ];
            });
    }

    /**
     * Get the venue from the active schedule of the ticket's events
     */
    public function getScheduleVenueAttribute()
    {
        return $this->events
            ->flatMap(fn($event) => $event->schedules)
            ->where('status', 'active')
            ->first()
            ?->venue;
    }

    /**
     * Get the display name for the requested venue
     */
    public function getVenueDisplayNameAttribute()
    {
        // Get venue from ticket's requested venue relationship
        $requestedVenue = $this->venue?->venue_name;

        if ($requestedVenue === 'Others (Please Specify)') {
            return $this->venue_other;
        }

        return $requestedVenue;
    }

    public function alternateVenue()
    {
        return $this->belongsTo(Venue::class, 'alternate_venue');
    }

    /**
     * Get the display name for the alternate venue
     */
    public function getAlternateVenueDisplayNameAttribute()
    {
        $alternateVenue = $this->alternateVenue?->venue_name;

        if ($alternateVenue === 'Others (Please Specify)') {
            return $this->alternate_venue_other;
        }

        return $alternateVenue;
    }

    /**
     * Helper method to log approval history
     *
     * @param  string  $approvalType  'osa' or 'office'
     * @param  string  $action  'pending', 'approved', 'for_revision', 'forwarded', 'revision_requested'
     */
    public function logApprovalHistory(
        string $approvalType,
        string $action,
        ?string $remarks = null,
        ?int $officeId = null,
        ?int $userId = null
    ): ApprovalHistory {
        return ApprovalHistory::log(
            $this->ticket_id,
            $approvalType,
            $action,
            $remarks,
            $officeId,
            $userId
        );
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

    /**
     * Check if the event date has passed
     */
    public function isEventDatePassed(): bool
    {
        // Check if event end date has passed
        if ($this->date_to) {
            try {
                $endDate = \Carbon\Carbon::parse($this->date_to);

                return now()->isAfter($endDate->endOfDay());
            } catch (\Throwable) {
                return false;
            }
        }

        // If no end date, check start date
        if ($this->date_from) {
            try {
                $startDate = \Carbon\Carbon::parse($this->date_from);

                return now()->isAfter($startDate->endOfDay());
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }

    /**
     * Check if comments should be disabled for this ticket
     *
     * Comments are disabled if:
     * - Ticket status is 'completed', OR
     * - Event date has passed
     */
    public function areCommentsDisabled(): bool
    {
        return $this->status === 'completed' || $this->isEventDatePassed();
    }
}
