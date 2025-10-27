<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OSA_Approval extends Model
{
    /** @use HasFactory<\Database\Factories\OSAApprovalFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'o_s_a__approvals';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'osa_approval_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'decision',
        'remarks',
    ];

    /**
     * Disable updates to maintain audit trail integrity
     * All approval actions should create new records
     */
    protected static function boot()
    {
        parent::boot();
        
        // Optionally prevent updates (uncomment if you want strict enforcement)
        // static::updating(function ($model) {
        //     throw new \Exception('OSA Approval records cannot be updated. Create a new record instead to maintain audit trail.');
        // });
    }

    /**
     * Ticket that this approval belongs to
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * User who made this approval
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope to get approvals by decision type
     */
    public function scopeByDecision($query, $decision)
    {
        return $query->where('decision', $decision);
    }

    /**
     * Scope to get latest approval for each ticket
     */
    public function scopeLatestForTicket($query, $ticketId)
    {
        return $query->where('ticket_id', $ticketId)
                    ->latest('created_at')
                    ->first();
    }

    /**
     * Scope to get pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('decision', 'pending');
    }

    /**
     * Scope to get approved
     */
    public function scopeApproved($query)
    {
        return $query->where('decision', 'approved');
    }

    /**
     * Scope to get rejected
     */
    public function scopeRejected($query)
    {
        return $query->where('decision', 'rejected');
    }

    /**
     * Scope to get forwarded
     */
    public function scopeForwarded($query)
    {
        return $query->where('decision', 'forwarded');
    }

    /**
     * Scope to get revision requested
     */
    public function scopeRevisionRequested($query)
    {
        return $query->where('decision', 'revision_requested');
    }

    /**
     * Check if this is the latest approval for the ticket
     */
    public function isLatest()
    {
        return $this->created_at->equalTo(
            static::where('ticket_id', $this->ticket_id)
                ->max('created_at')
        );
    }
}
