<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalHistory extends Model
{
    /** @use HasFactory<\Database\Factories\ApprovalHistoryFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'approval_history';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'history_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'office_id',
        'approval_type',
        'action',
        'remarks',
        'office_name',
    ];

    /**
     * Prevent updates to maintain audit trail integrity
     * Approval history is immutable - only inserts allowed
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            throw new \Exception('ApprovalHistory records cannot be updated. This is an immutable audit trail.');
        });
    }

    /**
     * Ticket that this history entry belongs to
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * User who performed this action
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Office that this approval is for (if office approval)
     */
    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    /**
     * Scope to get history for a specific ticket
     */
    public function scopeForTicket($query, $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    /**
     * Scope to get history by approval type
     */
    public function scopeByApprovalType($query, $type)
    {
        return $query->where('approval_type', $type);
    }

    /**
     * Scope to get history by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get latest history entries first
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get display name for the office
     */
    public function getOfficeDisplayNameAttribute()
    {
        if ($this->approval_type === 'osa') {
            return 'Office of Student Affairs';
        }

        return $this->office_name ?? ($this->office->office_name ?? 'Unknown Office');
    }

    /**
     * Static helper to log an approval action
     *
     * @param int $ticketId
     * @param string $approvalType 'osa' or 'office'
     * @param string $action 'pending', 'approved', '', 'forwarded', 'revision_requested'
     * @param string|null $remarks
     * @param int|null $officeId
     * @param int|null $userId
     * @return ApprovalHistory
     */
    public static function log(
        int $ticketId,
        string $approvalType,
        string $action,
        ?string $remarks = null,
        ?int $officeId = null,
        ?int $userId = null
    ): self {
        $userId = $userId ?? auth()->id();

        // Get office name if office_id is provided
        $officeName = null;
        if ($officeId) {
            $office = Office::find($officeId);
            $officeName = $office?->office_name;
        } elseif ($approvalType === 'osa') {
            $officeName = 'Office of Student Affairs';
        }

        return self::create([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'office_id' => $officeId,
            'approval_type' => $approvalType,
            'action' => $action,
            'remarks' => $remarks,
            'office_name' => $officeName,
        ]);
    }
}
