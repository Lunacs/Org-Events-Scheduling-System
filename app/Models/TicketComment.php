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

        // Load role relationship if not already loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        // Return based on user's role
        return match ($user->role?->role_name) {
            'osa' => 'OSA',
            'gso' => $user->office?->office_code ?? 'GSO',
            'student-org' => $user->studentOrganization?->org_code ?? 'Student Org',
            'superadmin' => 'Admin',
            default => $user->name,
        };
    }
}
