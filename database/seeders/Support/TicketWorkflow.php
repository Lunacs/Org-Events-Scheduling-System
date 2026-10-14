<?php

namespace Database\Seeders\Support;

use App\Models\Attachment;
use App\Models\Event;
use App\Models\Event_Schedule;
use App\Models\Office;
use App\Models\Office_Approval;
use App\Models\OSA_Approval;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\TicketAmendedNotification;
use App\Notifications\TicketCommentNotification;
use App\Notifications\TicketForwardedToGsoNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Notifications\TicketSubmittedNotification;
use App\Services\TransactionLogService;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Replays the real ticket workflow for seeding.
 *
 * Each step writes the same rows as the matching app action (SubmitTicket,
 * Osa\TicketReview\Show, Gso\Details, Components\TicketComments), at a given
 * point in time and as a given user. Notifications are written to the
 * database channel only, so seeding never sends mail or queues jobs.
 */
class TicketWorkflow
{
    private readonly ?int $gsoOfficeId;

    public function __construct()
    {
        $this->gsoOfficeId = Office::query()->where('office_code', 'GSO')->value('office_id');
    }

    /**
     * A student organization submits a new ticket.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function submit(User $owner, array $attributes, CarbonInterface $at): Ticket
    {
        return $this->as($owner, $at, function () use ($owner, $attributes): Ticket {
            $ticket = Ticket::create([
                ...$attributes,
                'user_id' => $owner->user_id,
                'ticket_number' => $this->nextTicketNumber((string) $owner->studentOrganization?->org_code),
                'proponent_contact' => $owner->phone ?? '',
                'status' => 'received',
            ]);

            $this->attachDocuments($ticket);

            TransactionLogService::logTicketOperation('created', $ticket);

            $this->notify($this->osaUsers(), new TicketSubmittedNotification($ticket));

            return $ticket;
        });
    }

    /**
     * The organization revises a ticket OSA sent back and resubmits the same ticket.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function resubmit(Ticket $ticket, User $owner, array $attributes, CarbonInterface $at): void
    {
        $this->as($owner, $at, function () use ($ticket, $owner, $attributes): void {
            $ticket->update([...$attributes, 'status' => 'amended']);

            OSA_Approval::updateOrCreate(
                ['ticket_id' => $ticket->ticket_id],
                ['decision' => 'pending', 'remarks' => 'Resubmitted after revision'],
            );

            $ticket->logApprovalHistory('osa', 'pending', 'Resubmitted after revision', null, $owner->user_id);

            TransactionLogService::logTicketOperation('amended', $ticket);

            $this->notify($this->osaUsers(), new TicketAmendedNotification($ticket));
        });
    }

    /**
     * OSA forwards a ticket to GSO for venue and logistics review.
     */
    public function forward(Ticket $ticket, User $osa, string $remarks, CarbonInterface $at): void
    {
        $this->as($osa, $at, function () use ($ticket, $osa, $remarks): void {
            $oldStatus = $ticket->status;

            $ticket->update(['status' => 'gso_review']);

            OSA_Approval::updateOrCreate(
                ['ticket_id' => $ticket->ticket_id],
                ['user_id' => $osa->user_id, 'decision' => 'forwarded', 'remarks' => $remarks],
            );

            Office_Approval::updateOrCreate(
                ['ticket_id' => $ticket->ticket_id, 'office_id' => $this->gsoOfficeId],
                ['user_id' => $osa->user_id, 'decision' => 'pending', 'remarks' => $remarks],
            );

            $ticket->logApprovalHistory('osa', 'forwarded', $remarks);
            $ticket->logApprovalHistory('office', 'pending', $remarks, $this->gsoOfficeId);

            TransactionLogService::logTicketOperation('forwarded', $ticket, ['Remarks' => $remarks]);

            $this->notify([$ticket->user], new TicketStatusUpdatedNotification($ticket, $oldStatus, 'gso_review', $remarks));
            $this->notify($this->gsoOfficeUsers(), new TicketForwardedToGsoNotification($ticket, $remarks));
        });
    }

    /**
     * GSO approves a forwarded ticket or sends it back to OSA asking for revisions.
     */
    public function gsoDecide(Ticket $ticket, User $gso, string $decision, string $remarks, CarbonInterface $at): void
    {
        $this->as($gso, $at, function () use ($ticket, $gso, $decision, $remarks): void {
            $oldStatus = $ticket->status;

            $ticket->update(['status' => 'pending_osa_approval']);

            Office_Approval::updateOrCreate(
                ['ticket_id' => $ticket->ticket_id, 'office_id' => $gso->office_id],
                ['user_id' => $gso->user_id, 'decision' => $decision, 'remarks' => $remarks],
            );

            $ticket->logApprovalHistory('office', $decision, $remarks, $gso->office_id);

            TransactionLogService::logOfficeApproval('GSO', $decision, $ticket, ['Remarks' => $remarks]);

            [$ownerMessage, $osaMessage] = $decision === 'approved'
                ? ['GSO has approved your ticket and it is now awaiting final OSA approval.', "GSO has approved this ticket. Remarks: {$remarks}"]
                : ['GSO has reviewed your ticket and forwarded it to OSA for a final decision.', "GSO has requested revisions. Remarks: {$remarks}"];

            $this->notify([$ticket->user], new TicketStatusUpdatedNotification($ticket, $oldStatus, 'pending_osa_approval', $ownerMessage));
            $this->notify($this->osaUsers(), new TicketStatusUpdatedNotification($ticket, $oldStatus, 'pending_osa_approval', $osaMessage));
        });
    }

    /**
     * OSA sends a ticket back to the organization for revision.
     */
    public function requestRevision(Ticket $ticket, User $osa, string $remarks, CarbonInterface $at): void
    {
        $this->as($osa, $at, function () use ($ticket, $osa, $remarks): void {
            $oldStatus = $ticket->status;

            $ticket->update(['status' => 'for_revision']);

            OSA_Approval::updateOrCreate(
                ['ticket_id' => $ticket->ticket_id],
                ['user_id' => $osa->user_id, 'decision' => 'for_revision', 'remarks' => $remarks],
            );

            $ticket->logApprovalHistory('osa', 'for_revision', $remarks);

            TransactionLogService::logTicketOperation('for_revision', $ticket, ['Remarks' => $remarks]);

            $this->notify([$ticket->user], new TicketStatusUpdatedNotification($ticket, $oldStatus, 'for_revision', $remarks));
        });
    }

    /**
     * OSA approves a ticket, directly or as the final decision after GSO review,
     * which creates the event and its approved schedule.
     */
    public function approve(Ticket $ticket, User $osa, string $remarks, CarbonInterface $at): void
    {
        $this->as($osa, $at, function () use ($ticket, $osa, $remarks): void {
            $oldStatus = $ticket->status;
            $afterGsoReview = $oldStatus === 'pending_osa_approval';

            $ticket->update(['status' => 'approved']);

            OSA_Approval::updateOrCreate(
                ['ticket_id' => $ticket->ticket_id],
                ['user_id' => $osa->user_id, 'decision' => 'approved', 'remarks' => $remarks],
            );

            $ticket->logApprovalHistory('osa', 'approved', $remarks);

            TransactionLogService::logTicketOperation($afterGsoReview ? 'final_approved' : 'approved', $ticket, ['Remarks' => $remarks]);

            $suffix = $afterGsoReview ? ' after GSO review' : '';

            $event = Event::create([
                'ticket_id' => $ticket->ticket_id,
                'event__type_id' => $ticket->event_type_id,
                'notes' => 'Event created from approved ticket'.$suffix,
            ]);

            Event_Schedule::create([
                'event_id' => $event->event_id,
                'start_date' => $ticket->date_from,
                'end_date' => $ticket->date_to,
                'start_time' => $ticket->time_from,
                'end_time' => $ticket->time_to,
                'venue' => $ticket->venue_display_name,
                'status' => 'approved',
                'remarks' => 'Schedule created from approved ticket'.$suffix,
            ]);

            $this->notify([$ticket->user], new TicketStatusUpdatedNotification($ticket, $oldStatus, 'approved', $remarks));
        });
    }

    /**
     * A user comments on a ticket, notifying the same people the comment thread does.
     */
    public function comment(Ticket $ticket, User $author, string $content, CarbonInterface $at): void
    {
        $this->as($author, $at, function () use ($ticket, $author, $content): void {
            $comment = TicketComment::create([
                'ticket_id' => $ticket->ticket_id,
                'user_id' => $author->user_id,
                'content' => $content,
            ]);

            $recipients = collect();

            if ($ticket->user && $ticket->user->user_id !== $author->user_id) {
                $recipients->push($ticket->user);
            }

            if ($author->isStudentOrg() || $author->isGSO()) {
                $recipients = $recipients->merge($this->osaUsers());
            }

            if (($author->isOSA() || $author->isSuperAdmin()) && $this->hasGsoInvolvement($ticket)) {
                $recipients = $recipients->merge(User::query()->inPortal('gso')->get());
            }

            $this->notify($recipients, new TicketCommentNotification($ticket, $comment, $author, ['database']));
        });
    }

    /**
     * Run one workflow step at a point in time, signed in as the acting user.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $step
     * @return TReturn
     */
    private function as(User $actor, CarbonInterface $at, Closure $step): mixed
    {
        Carbon::setTestNow($at);
        Auth::setUser($actor);

        try {
            return $step();
        } finally {
            Carbon::setTestNow();
            Auth::forgetUser();
        }
    }

    /**
     * The next per-organization ticket number, found the same way SubmitTicket finds it.
     */
    private function nextTicketNumber(string $orgCode): string
    {
        $lastTicket = Ticket::query()
            ->where('ticket_number', 'LIKE', "TKT-{$orgCode}-%")
            ->orderByRaw('CAST(SUBSTRING(ticket_number, LOCATE(\'-\', ticket_number, 5) + 1) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = $lastTicket
            ? ((int) substr(strrchr($lastTicket->ticket_number, '-'), 1)) + 1
            : 1;

        return "TKT-{$orgCode}-".str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Store a letter of request plus the first documentary requirement of the
     * event type as small placeholder PDFs, where uploaded attachments end up.
     */
    private function attachDocuments(Ticket $ticket): void
    {
        $directory = "tickets/{$ticket->ticket_id}/attachments";

        // Ticket ids restart after migrate:fresh, so clear files left by an earlier seed.
        Storage::deleteDirectory("tickets/{$ticket->ticket_id}");

        $requirement = collect(config("event_requirements.documents.{$ticket->event_type_id}", []))
            ->first(static fn (mixed $document): bool => is_string($document));

        $documentNames = collect(['Letter of Request', $requirement])
            ->filter()
            ->map(static fn (string $name): string => Str::of($name)->before(' (')->before(' —')->replace('/', ' and ')->squish()->limit(60, '')->toString());

        foreach ($documentNames as $documentName) {
            $fileName = "{$documentName}.pdf";
            $contents = $this->placeholderPdf("{$documentName} - {$ticket->ticket_number}");
            $path = "{$directory}/".time().'_'.uniqid().'_'.str_replace(' ', '_', $fileName);

            Storage::put($path, $contents);

            Attachment::create([
                'ticket_id' => $ticket->ticket_id,
                'file_name' => $fileName,
                'file_path' => $path,
                'file_type' => 'application/pdf',
                'file_size' => strlen($contents),
            ]);
        }
    }

    /**
     * A minimal valid one-page PDF showing a single line of text.
     */
    private function placeholderPdf(string $text): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], Str::ascii($text));
        $stream = "BT /F1 16 Tf 72 720 Td ({$text}) Tj ET";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= 'xref'."\n".'0 '.(count($objects) + 1)."\n".'0000000000 65535 f '."\n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf('%010d 00000 n ', $offset)."\n";
        }

        return $pdf.'trailer'."\n".'<< /Size '.(count($objects) + 1).' /Root 1 0 R >>'."\n".'startxref'."\n{$xrefOffset}\n".'%%EOF'."\n";
    }

    /**
     * Whether GSO is or was involved with the ticket, as the comment thread decides it.
     */
    private function hasGsoInvolvement(Ticket $ticket): bool
    {
        return in_array($ticket->status, ['gso_review', 'pending_osa_approval'], true)
            || Office_Approval::query()
                ->where('ticket_id', $ticket->ticket_id)
                ->where('office_id', $this->gsoOfficeId)
                ->exists();
    }

    /**
     * @return Collection<int, User>
     */
    private function osaUsers(): Collection
    {
        return User::query()->inPortal('osa')->get();
    }

    /**
     * Users of the GSO portal who belong to the GSO office.
     *
     * @return Collection<int, User>
     */
    private function gsoOfficeUsers(): Collection
    {
        return User::query()->inPortal('gso')->where('office_id', $this->gsoOfficeId)->get();
    }

    /**
     * Write a notification to each recipient's database inbox only.
     *
     * @param  iterable<User>  $recipients
     */
    private function notify(iterable $recipients, Notification $notification): void
    {
        collect($recipients)
            ->filter()
            ->unique('user_id')
            ->each(static fn (User $user) => $user->notifyNow($notification, ['database']));
    }
}
