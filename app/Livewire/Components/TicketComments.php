<?php

namespace App\Livewire\Components;

use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\TicketCommentNotification;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class TicketComments extends Component
{
    use Toast;

    public Ticket $ticket;

    public string $comment = '';

    protected $listeners = ['refreshComments' => '$refresh'];

    public function mount(Ticket $ticket): void
    {
        // Ensure comments are loaded
        if (! $this->ticket->relationLoaded('comments')) {
            $this->ticket->load([
                'comments:id,ticket_id,user_id,content,created_at',
                'comments.user:user_id,name,role_id,avatar,avatar_preference,avatar_style,avatar_seed',
                'comments.user.role:role_id,role_name',
            ]);
        }
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="animate-pulse space-y-4">
                <div class="h-6 bg-base-200 rounded w-1/4"></div>
                <div class="space-y-3">
                    <div class="h-4 bg-base-200 rounded"></div>
                    <div class="h-4 bg-base-200 rounded w-5/6"></div>
                    <div class="h-4 bg-base-200 rounded w-4/6"></div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function addComment(): void
    {
        // Prevent comments on completed events or past event dates
        if ($this->ticket->areCommentsDisabled()) {
            if ($this->ticket->status === 'completed') {
                $this->error('Comments are disabled for completed events.');
            } else {
                $this->error('Comments are disabled for events that have already passed.');
            }

            return;
        }

        $this->validate(
            ['comment' => 'required|string|min:3|max:1000'],
            [
                'comment.required' => 'Please enter a comment.',
                'comment.min' => 'Comment must be at least 3 characters.',
                'comment.max' => 'Comment cannot exceed 1000 characters.',
            ]
        );

        // Create comment
        $newComment = TicketComment::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'content' => $this->comment,
        ]);

        // Log the user comment activity in

        // Clear the input
        $this->comment = '';

        // Reload only the comment relationship with minimal data
        $this->ticket->load([
            'comments:id,ticket_id,user_id,content,created_at',
            'comments.user:user_id,name,role_id,avatar,avatar_preference,avatar_style,avatar_seed',
            'comments.user.role:role_id,role_name',
        ]);

        // Notify relevant parties (server-side concern)
        $this->notifyCommentAdded($newComment);

        // Simple success message (no heavy notifications)
        $this->success('Comment added successfully.');

        // Dispatch event for avatar initialization (client-side UI)
        $this->dispatch('comment-added');
    }

    /**
     * Notify relevant users when a comment is added
     *
     * - OSA comment → Notify ticket owner (Student Org) and GSO (if ticket has GSO involvement)
     * - Student Org comment → Notify OSA
     * - GSO comment → Notify ticket owner (Student Org) and OSA
     * - If ticket is in GSO review → Notify GSO users as well
     */
    private function notifyCommentAdded(TicketComment $comment): void
    {
        $commenter = auth()->user();

        // Ensure ticket user relationship is loaded
        if (! $this->ticket->relationLoaded('user')) {
            $this->ticket->load('user:user_id,name,email,role_id');
        }

        $ticketOwner = $this->ticket->user;

        // Don't notify the person who made the comment
        $usersToNotify = collect();

        // Always notify the ticket owner if they didn't make the comment
        if ($ticketOwner && $ticketOwner->user_id !== $commenter->user_id) {
            $usersToNotify->push($ticketOwner);
        }

        // If commenter is Student Org, notify OSA users
        if ($commenter->isStudentOrg()) {
            $osaUsers = Cache::remember('osa_users_notifications', 3600, function () {
                return User::select(['user_id', 'name', 'email', 'role_id'])
                    ->where('role_id', User::getRoleId('osa'))
                    ->get();
            });
            $usersToNotify = $usersToNotify->merge($osaUsers);
        }

        // If commenter is GSO, notify OSA users
        if ($commenter->role_id === User::getRoleId('gso')) {
            $osaUsers = Cache::remember('osa_users_notifications', 3600, function () {
                return User::select(['user_id', 'name', 'email', 'role_id'])
                    ->where('role_id', User::getRoleId('osa'))
                    ->get();
            });
            $usersToNotify = $usersToNotify->merge($osaUsers);
        }

        // If commenter is OSA, check if ticket has GSO involvement and notify GSO users
        if ($commenter->isOSA() || $commenter->isSuperAdmin()) {
            $hasGsoInvolvement = $this->ticketHasGsoInvolvement();

            if ($hasGsoInvolvement) {
                $gsoUsers = Cache::remember('gso_users_notifications', 3600, function () {
                    return User::select(['user_id', 'name', 'email', 'role_id'])
                        ->where('role_id', User::getRoleId('gso'))
                        ->get();
                });
                $usersToNotify = $usersToNotify->merge($gsoUsers);
            }
        }

        // If ticket is in GSO review status and commenter is not GSO, notify GSO users
        // (This handles cases where non-OSA users comment on GSO review tickets)
        if (
            in_array($this->ticket->status, ['gso_review', 'pending_osa_approval']) &&
            $commenter->role_id !== User::getRoleId('gso') &&
            ! $commenter->isOSA() &&
            ! $commenter->isSuperAdmin()
        ) {
            $gsoUsers = Cache::remember('gso_users_notifications', 3600, function () {
                return User::select(['user_id', 'name', 'email', 'role_id'])
                    ->where('role_id', User::getRoleId('gso'))
                    ->get();
            });
            $usersToNotify = $usersToNotify->merge($gsoUsers);
        }

        // Send DB + broadcast immediately; queue mail separately to avoid UI delay
        $usersToNotify->unique('user_id')->each(function ($user) use ($comment, $commenter) {
            // immediate
            $user->notifyNow(new TicketCommentNotification($this->ticket, $comment, $commenter, ['database', 'broadcast']));

            // queued mail only
            $user->notify(new TicketCommentNotification($this->ticket, $comment, $commenter, ['mail']));
        });

        // Dispatch real-time notification event
        if ($usersToNotify->isNotEmpty()) {
            $this->dispatch('refresh-notifications');
        }
    }

    /**
     * Check if ticket has GSO involvement
     *
     * A ticket has GSO involvement if:
     * - Ticket status is 'gso_review' or 'pending_osa_approval', OR
     * - Ticket has any Office_Approval records for GSO office
     */
    private function ticketHasGsoInvolvement(): bool
    {
        // Check ticket status
        if (in_array($this->ticket->status, ['gso_review', 'pending_osa_approval'])) {
            return true;
        }

        // Check if ticket has GSO office approvals
        $gsoOfficeId = Office::query()
            ->where('office_code', 'GSO')
            ->value('office_id');

        if (! $gsoOfficeId) {
            return false;
        }

        // Check if ticket has any Office_Approval for GSO office
        if (! $this->ticket->relationLoaded('officeApprovals')) {
            return $this->ticket->officeApprovals()
                ->where('office_id', $gsoOfficeId)
                ->exists();
        }

        return $this->ticket->officeApprovals
            ->contains(fn ($approval) => (int) $approval->office_id === (int) $gsoOfficeId);
    }

    public function render()
    {
        return view('livewire.components.ticket-comments');
    }
}
