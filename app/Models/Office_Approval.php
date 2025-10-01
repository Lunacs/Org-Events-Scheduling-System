<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office_Approval extends Model
{
    /** @use HasFactory<\Database\Factories\OfficeApprovalFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'office__approvals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticket_id',
        'office_id',
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
     * Office that made this approval
     */
    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    /**
     * User who made this approval
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
