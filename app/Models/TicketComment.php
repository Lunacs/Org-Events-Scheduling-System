<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
    ];

    /**
     * Ticket that this comment belongs to
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * User who made this comment
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function namingConvention()
    {
        // Check if this comment is from the current user
        if ($this->user_id === auth()->id()) {
            return 'You';
        }

        $user = $this->user;

        // Return based on user's role
        return match ($user->role_id) {
            User::ROLE_OSA => 'OSA',
            User::ROLE_GSO => $user->office?->office_code ?? 'GSO',
            User::ROLE_STUDENT_ORG => $user->studentOrganization?->org_code ?? 'Student Org',
            User::ROLE_SUPERADMIN => 'Admin',
            default => $user->name,
        };
    }
}
