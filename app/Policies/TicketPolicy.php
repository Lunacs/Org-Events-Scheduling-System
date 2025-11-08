<?php

namespace App\Policies;

use App\Models\Office;
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
    public function update(User $user, Ticket $ticket): bool
    {
        return false;
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

        // GSO can approve when ticket is in review or when their office decision is pending
        if ($user->isGSO()) {
            if ($ticket->status === 'gso_review') {
                return true;
            }

            if ($this->gsoHasPendingDecision($user, $ticket)) {
                return true;
            }
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

        // GSO can reject when ticket is in review or when their office decision is pending
        if ($user->isGSO()) {
            if ($ticket->status === 'gso_review') {
                return true;
            }

            if ($this->gsoHasPendingDecision($user, $ticket)) {
                return true;
            }
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
     * Determine if the GSO user's office still has a pending decision on the ticket.
     */
    protected function gsoHasPendingDecision(User $user, Ticket $ticket): bool
    {
        $officeId = $user->office_id;

        if (! $officeId) {
            $officeId = Office::query()
                ->where('office_code', 'GSO')
                ->value('office_id');
        }

        if (! $officeId) {
            return false;
        }

        if ($ticket->relationLoaded('officeApprovals')) {
            return $ticket->officeApprovals
                ->contains(fn ($approval) => (int) $approval->office_id === (int) $officeId
                    && strcasecmp($approval->decision, 'pending') === 0);
        }

        return $ticket->officeApprovals()
            ->where('office_id', $officeId)
            ->where('decision', 'pending')
            ->exists();
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
