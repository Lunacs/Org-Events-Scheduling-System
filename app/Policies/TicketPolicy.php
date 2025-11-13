<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket)
    {
        return $user->user_id === $ticket->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can approve a ticket.
     */
    public function approve(User $user, Ticket $ticket): bool
    {
        // OSA can approve tickets in 'received' or 'amended' status
        if ($user->isOSA() && in_array($ticket->status, ['received', 'amended'])) {
            return true;
        }

        // GSO can approve tickets in 'gso_review' status
        if ($user->isGSO() && $ticket->status === 'gso_review') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can reject a ticket.
     */
    public function reject(User $user, Ticket $ticket): bool
    {
        // OSA can reject tickets in 'received' or 'amended' status
        if ($user->isOSA() && in_array($ticket->status, ['received', 'amended'])) {
            return true;
        }

        // GSO can reject tickets in 'gso_review' status
        if ($user->isGSO() && $ticket->status === 'gso_review') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can request revision of a ticket.
     */
    public function requestRevision(User $user, Ticket $ticket): bool
    {
        // Only OSA can request revisions
        return $user->isOSA() && in_array($ticket->status, ['received', 'amended']);
    }

    /**
     * Determine whether the user can forward a ticket to GSO.
     */
    public function forwardToGso(User $user, Ticket $ticket): bool
    {
        // Only OSA can forward to GSO
        return $user->isOSA() && in_array($ticket->status, ['received', 'amended']);
    }

    /**
     * Determine whether the user can make final approval after GSO review.
     */
    public function finalApprove(User $user, Ticket $ticket): bool
    {
        // Only OSA can make final approval after GSO review
        return $user->isOSA() && $ticket->status === 'pending_osa_approval';
    }

    /**
     * Determine whether the user can make final rejection after GSO review.
     */
    public function finalReject(User $user, Ticket $ticket): bool
    {
        // Only OSA can make final rejection after GSO review
        return $user->isOSA() && $ticket->status === 'pending_osa_approval';
    }
}
