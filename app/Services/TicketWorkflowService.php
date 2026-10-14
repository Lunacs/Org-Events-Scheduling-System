<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Event_Schedule;
use App\Models\Office;
use App\Models\Office_Approval;
use App\Models\OSA_Approval;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAmendedNotification;
use App\Notifications\TicketForwardedToGsoNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Notifications\TicketSubmittedNotification;
use App\Services\Cache\ArchiveCacheService;
use App\Services\Cache\CalendarCacheService;
use App\Services\Cache\DashboardCacheService;
use App\Services\Cache\EventCacheService;
use App\Services\Cache\ReportCacheService;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * The ticket state transitions shared by every portal.
 *
 * Each transition locks the ticket, re-checks its status after the lock (so two
 * staff acting at once cannot both succeed), writes the approval records,
 * history and audit log, and notifies after the transaction commits.
 */
class TicketWorkflowService
{
    /** Statuses OSA may approve or send back for revision. */
    public const OSA_DECIDABLE_STATUSES = ['received', 'amended', 'pending_osa_approval'];

    /** Statuses OSA may forward to GSO. */
    public const FORWARDABLE_STATUSES = ['received', 'amended'];

    /**
     * Approve a ticket and create (or move) its approved event schedule.
     */
    public function approve(Ticket $ticket, User $actor, string $remarks): Ticket
    {
        [$ticket, $oldStatus] = DB::transaction(function () use ($ticket, $actor, $remarks): array {
            $ticket = $this->lock($ticket, self::OSA_DECIDABLE_STATUSES);
            $oldStatus = $ticket->status;
            $afterGsoReview = $oldStatus === 'pending_osa_approval';

            $ticket->update(['status' => 'approved']);

            OSA_Approval::updateOrCreate(
                ['ticket_id' => $ticket->ticket_id],
                ['user_id' => $actor->user_id, 'decision' => 'approved', 'remarks' => $remarks],
            );

            $ticket->logApprovalHistory('osa', 'approved', $remarks, null, $actor->user_id);
            TransactionLogService::log(...$this->ticketLog($afterGsoReview ? 'final_approved' : 'approved', $ticket, ['Remarks' => $remarks], $actor));

            $this->syncSchedule($ticket, $afterGsoReview ? ' after GSO review' : '');

            return [$ticket, $oldStatus];
        });

        $ticket->user?->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus, 'approved', $remarks));
        $this->clearCaches();

        return $ticket;
    }

    /**
     * Send a ticket back to the organization for revision.
     */
    public function requestRevision(Ticket $ticket, User $actor, string $remarks): Ticket
    {
        [$ticket, $oldStatus] = DB::transaction(function () use ($ticket, $actor, $remarks): array {
            $ticket = $this->lock($ticket, self::OSA_DECIDABLE_STATUSES);
            $oldStatus = $ticket->status;

            $ticket->update(['status' => 'for_revision']);

            OSA_Approval::updateOrCreate(
                ['ticket_id' => $ticket->ticket_id],
                ['user_id' => $actor->user_id, 'decision' => 'for_revision', 'remarks' => $remarks],
            );

            $ticket->logApprovalHistory('osa', 'for_revision', $remarks, null, $actor->user_id);
            TransactionLogService::log(...$this->ticketLog('for_revision', $ticket, ['Remarks' => $remarks], $actor));

            return [$ticket, $oldStatus];
        });

        $ticket->user?->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus, 'for_revision', $remarks));
        $this->clearCaches();

        return $ticket;
    }

    /**
     * Forward a ticket to the GSO office for venue and logistics review.
     */
    public function forwardToGso(Ticket $ticket, User $actor, string $remarks): Ticket
    {
        $gsoOfficeId = Office::getOfficeId('GSO');

        if (! $gsoOfficeId) {
            throw new DomainException('The GSO office does not exist.');
        }

        [$ticket, $oldStatus] = DB::transaction(function () use ($ticket, $actor, $remarks, $gsoOfficeId): array {
            $ticket = $this->lock($ticket, self::FORWARDABLE_STATUSES);
            $oldStatus = $ticket->status;

            $ticket->update(['status' => 'gso_review']);

            OSA_Approval::updateOrCreate(
                ['ticket_id' => $ticket->ticket_id],
                ['user_id' => $actor->user_id, 'decision' => 'forwarded', 'remarks' => $remarks],
            );

            Office_Approval::updateOrCreate(
                ['ticket_id' => $ticket->ticket_id, 'office_id' => $gsoOfficeId],
                ['user_id' => $actor->user_id, 'decision' => 'pending', 'remarks' => $remarks],
            );

            $ticket->logApprovalHistory('osa', 'forwarded', $remarks, null, $actor->user_id);
            $ticket->logApprovalHistory('office', 'pending', $remarks, $gsoOfficeId, $actor->user_id);
            TransactionLogService::log(...$this->ticketLog('forwarded', $ticket, ['Remarks' => $remarks], $actor));

            return [$ticket, $oldStatus];
        });

        $ticket->user?->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus, 'gso_review', $remarks));

        Notification::send(
            User::query()->inPortal('gso')->where('office_id', $gsoOfficeId)->get(),
            new TicketForwardedToGsoNotification($ticket, $remarks),
        );

        $this->clearCaches();

        return $ticket;
    }

    /**
     * Mark a revised ticket as resubmitted so it returns to OSA's queue.
     */
    public function resubmit(Ticket $ticket, User $owner): Ticket
    {
        $ticket = DB::transaction(function () use ($ticket, $owner): Ticket {
            $ticket = $this->lock($ticket, ['for_revision']);

            $ticket->update(['status' => 'amended']);
            $this->resetOsaDecision($ticket, $owner, 'Resubmitted after revision');
            TransactionLogService::log(...$this->ticketLog('amended', $ticket, [], $owner));

            return $ticket;
        });

        Notification::send(User::query()->inPortal('osa')->get(), new TicketAmendedNotification($ticket));
        $this->clearCaches();

        return $ticket;
    }

    /**
     * Record a reschedule request on an approved ticket. The approved schedule
     * stays on the calendar until OSA approves the new date.
     *
     * @param  list<string>  $changes
     */
    public function recordReschedule(Ticket $ticket, User $owner, array $changes): Ticket
    {
        $ticket = DB::transaction(function () use ($ticket, $owner, $changes): Ticket {
            $ticket->update(['status' => 'amended']);
            $this->resetOsaDecision($ticket, $owner, 'Reschedule requested');
            TransactionLogService::log(...$this->ticketLog('updated', $ticket, $changes, $owner));

            return $ticket;
        });

        Notification::send(User::query()->inPortal('osa')->get(), new TicketSubmittedNotification($ticket));
        $this->clearCaches();

        return $ticket;
    }

    /**
     * Make sure an approved or completed ticket has exactly one event with an
     * approved schedule matching the ticket's current date, time and venue.
     */
    public function syncSchedule(Ticket $ticket, string $noteSuffix = ''): Event_Schedule
    {
        $event = Event::firstOrCreate(
            ['ticket_id' => $ticket->ticket_id],
            ['event__type_id' => $ticket->event_type_id, 'notes' => 'Event created from approved ticket'.$noteSuffix],
        );

        return Event_Schedule::updateOrCreate(
            ['event_id' => $event->event_id],
            [
                'start_date' => $ticket->date_from,
                'end_date' => $ticket->date_to,
                'start_time' => $ticket->time_from,
                'end_time' => $ticket->time_to,
                'venue' => $ticket->fresh('venue')->venue_display_name,
                'status' => 'approved',
                'remarks' => 'Schedule created from approved ticket'.$noteSuffix,
            ],
        );
    }

    public function clearCaches(): void
    {
        DashboardCacheService::clearAllDashboards();
        CalendarCacheService::clearAllCalendar();
        EventCacheService::clearAllEventRelatedCaches();
        ArchiveCacheService::clearAllArchives();
        ReportCacheService::clearAllReports();
    }

    /**
     * Lock the ticket row and confirm it is still in one of the given statuses.
     *
     * @param  list<string>  $allowedStatuses
     */
    private function lock(Ticket $ticket, array $allowedStatuses): Ticket
    {
        $locked = Ticket::query()->lockForUpdate()->findOrFail($ticket->ticket_id);

        if (! in_array($locked->status, $allowedStatuses, true)) {
            throw new DomainException("Ticket {$locked->ticket_number} is no longer in a state that allows this action.");
        }

        return $locked;
    }

    private function resetOsaDecision(Ticket $ticket, User $actor, string $remarks): void
    {
        OSA_Approval::updateOrCreate(
            ['ticket_id' => $ticket->ticket_id],
            ['decision' => 'pending', 'remarks' => $remarks],
        );

        $ticket->logApprovalHistory('osa', 'pending', $remarks, null, $actor->user_id);
    }

    /**
     * Arguments for TransactionLogService::log(), formatted like logTicketOperation()
     * but attributed to the acting user rather than whoever is signed in.
     *
     * @param  array<string, string>  $changes
     * @return array{0: string, 1: string, 2: int}
     */
    private function ticketLog(string $operation, Ticket $ticket, array $changes, User $actor): array
    {
        $label = "{$ticket->title} ({$ticket->ticket_number})";
        $suffix = $changes === [] ? '' : ' - '.implode(', ', $changes);

        $details = match ($operation) {
            'approved' => "Approved ticket: {$label}{$suffix}",
            'final_approved' => "Final approval for ticket: {$label} after GSO review{$suffix}",
            'for_revision' => "Requested revision for ticket: {$label}{$suffix}",
            'forwarded' => "Forwarded ticket: {$label} to GSO{$suffix}",
            'amended' => "Amended ticket: {$label} - Resubmitted after revision",
            'updated' => "Updated ticket: {$label}".($changes === [] ? '' : ' - Changes: '.implode(', ', $changes)),
        };

        return ['TICKET_'.strtoupper($operation), $details, $actor->user_id];
    }
}
