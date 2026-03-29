<?php

namespace App\Policies;

use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    private const OSA_ACTIONABLE_STATUSES = ['received', 'amended'];

    private const PENDING_GSO_STATUS = 'gso_review';

    private const PENDING_OSA_STATUS = 'pending_osa_approval';

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->isSuperAdmin() || $user->user_id === $ticket->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->isSuperAdmin() || $user->user_id === $ticket->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can approve a ticket.
     */
    public function approve(User $user, Ticket $ticket): bool
    {
        // OSA can approve tickets in 'received' or 'amended' status
        if ($user->isOSA() && in_array($ticket->status, self::OSA_ACTIONABLE_STATUSES, true)) {
            return true;
        }

        // GSO can approve when ticket is in review or when their office decision is pending
        if ($user->isGSO()) {
            if ($ticket->status === self::PENDING_GSO_STATUS) {
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
        if ($user->isOSA() && in_array($ticket->status, self::OSA_ACTIONABLE_STATUSES, true)) {
            return true;
        }

        // GSO can reject when ticket is in review or when their office decision is pending
        if ($user->isGSO()) {
            if ($ticket->status === self::PENDING_GSO_STATUS) {
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
        // OSA can request revisions in received/amended status
        if ($user->isOSA() && in_array($ticket->status, self::OSA_ACTIONABLE_STATUSES, true)) {
            return true;
        }

        // OSA can also request revisions after GSO review
        if ($user->isOSA() && $ticket->status === self::PENDING_OSA_STATUS) {
            return true;
        }

        // GSO can request revisions when ticket is in gso_review
        if ($user->isGSO() && $ticket->status === self::PENDING_GSO_STATUS) {
            $officeApproval = $ticket->officeApprovals()
                ->where('office_id', $user->office_id)
                ->first();

            $decision = $officeApproval ? strtolower($officeApproval->decision ?? 'pending') : 'pending';

            return $decision === 'pending';
        }

        return false;
    }

    /**
     * Determine whether the user can forward a ticket to GSO.
     */
    public function forwardToGso(User $user, Ticket $ticket): bool
    {
        return $user->isOSA() && in_array($ticket->status, self::OSA_ACTIONABLE_STATUSES, true);
    }

    /**
     * Determine whether the user can make final approval after GSO review.
     */
    public function finalApprove(User $user, Ticket $ticket): bool
    {
        return $user->isOSA() && $ticket->status === self::PENDING_OSA_STATUS;
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
        return $user->isOSA() && $ticket->status === self::PENDING_OSA_STATUS;
    }
}
