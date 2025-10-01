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
}
