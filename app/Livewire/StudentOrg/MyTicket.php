<?php

namespace App\Livewire\StudentOrg;

use App\Models\Ticket;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class MyTicket extends Component
{
    use Toast;
    use WithPagination;

    #[Title('My Ticket - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]
    #[\Livewire\Attributes\Url(except: '')]
    public $search = '';

    #[\Livewire\Attributes\Url(as: 'status', except: '')]
    public $statusFilter = '';

    #[\Livewire\Attributes\Url(as: 'date', except: '')]
    public $dateFilter = '';

    public $showDetailsModal = false;

    public $showCommentsModal = false;

    public $selectedTicketId;

    public $isLoadingTicket = false;

    private function getBaseTicketsQuery()
    {
        $user = auth()->user();
        $query = Ticket::query()
            ->with([
                'eventType',
                'venue',
                'user' => function ($query) {
                    $query->withTrashed();
                },
                'user.studentOrganization',
            ]);

        // If user is President - see only their own tickets
        if ($user->position->position_name === 'President') {
            $query->where('user_id', $user->user_id);
        }
        // If user is Chairperson or Adviser - see all tickets from their org
        elseif (in_array($user->position->position_name, ['Chairperson', 'Adviser'])) {
            $query->whereHas('user', function ($q) {
                $orgId = $this->getUserOrgId();
                $q->withTrashed()->where('org_id', $orgId);
            });
        }

        return $query;
    }

    private function getUserOrgId()
    {
        return auth()->user()->org_id;
    }

    #[On('resubmit-ticket')]
    public function resubmitTicket($ticketId)
    {
        $ticket = Ticket::query()->findOrFail($ticketId);

        Gate::authorize('resubmit', $ticket);

        return redirect()->route('student-org.submit-ticket', ['amend' => $ticket->ticket_number]);
    }

    #[On('open-ticket-details')]
    public function openDetailsModal($ticketId = null)
    {
        $this->showDetailsModal = true;
        $this->selectedTicketId = null; // Clear first

        // Use $nextTick in JavaScript to load data after modal is shown
        $this->dispatch('modal-opened', ticketId: $ticketId);
    }

    #[On('ticket-updated')]
    public function refreshTickets()
    {
        // Reset pagination to first page
        $this->resetPage();
    }

    #[On('load-ticket-data')]
    public function loadTicketData($ticketId)
    {
        $this->selectedTicketId = $ticketId;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedTicketId = null;
        $this->isLoadingTicket = false;
    }

    #[On('open-comment-section')]
    public function openCommentsModal($ticketId = null)
    {
        $this->selectedTicketId = $ticketId;
        $this->showCommentsModal = true;
    }

    public function closeCommentsModal()
    {
        $this->showCommentsModal = false;
        $this->selectedTicketId = null;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateFilter = '';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function getSelectedTicketProperty()
    {
        if (! $this->selectedTicketId) {
            return null;
        }

        $user = auth()->user();
        $query = Ticket::query()
            ->with([
                'eventType',
                'comments',
                'attachments',
                'fundSource',
                'user' => function ($query) {
                    $query->withTrashed();
                },
                'user.studentOrganization.course',
                'user.position',
            ]);

        // Apply same visibility logic
        if ($user->position->position_name === 'President') {
            $query->where('user_id', $user->user_id);
        } elseif (in_array($user->position->position_name, ['Chairperson', 'Adviser'])) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->withTrashed()->where('org_id', $user->org_id);
            });
        }

        return $query->find($this->selectedTicketId);
    }

    public function getSelectedTicketCommentsProperty()
    {
        if (! $this->selectedTicketId) {
            \Log::info('No ticket ID set for comments');

            return null;
        }

        return auth()->user()->tickets()
            ->find($this->selectedTicketId)
            ?->comments()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Generate a temporary URL and open in a new tab for preview.
     */
    public function previewAttachment(int $attachmentId): void
    {
        if (! $this->selectedTicketId) {
            $this->warning('No ticket selected.');

            return;
        }

        $ticket = auth()->user()->tickets()->with('attachments')->find($this->selectedTicketId);
        if (! $ticket) {
            $this->warning('You do not have access to that ticket.');

            return;
        }

        $attachment = $ticket->attachments->firstWhere('attachment_id', $attachmentId);

        if (! $attachment) {
            $this->warning('Attachment not found.');

            return;
        }

        $url = $this->makeTemporaryUrl($attachment->attachment_id, false);

        $this->dispatch('open-attachment-preview', url: $url);
    }

    /**
     * Generate a temporary URL that forces download and dispatch it for JavaScript handling.
     */
    public function downloadAttachment(int $attachmentId): void
    {
        if (! $this->selectedTicketId) {
            $this->warning('No ticket selected.');

            return;
        }

        $ticket = auth()->user()->tickets()->with('attachments')->find($this->selectedTicketId);
        if (! $ticket) {
            $this->warning('You do not have access to that ticket.');

            return;
        }

        $attachment = $ticket->attachments->firstWhere('attachment_id', $attachmentId);

        if (! $attachment) {
            $this->warning('Attachment not found.');

            return;
        }

        $url = $this->makeTemporaryUrl($attachment->attachment_id, true);

        $this->dispatch('download-attachment', url: $url, filename: $attachment->file_name);
    }

    /**
     * Build a temporary URL from the configured filesystem.
     * Uses S3 temporaryUrl for cloud storage, or signed routes for local storage.
     */
    private function makeTemporaryUrl(int $attachmentId, bool $forceDownload = false): string
    {
        $routeName = $forceDownload ? 'attachments.download' : 'attachments.preview';

        return URL::temporarySignedRoute(
            $routeName,
            now()->addMinutes(5),
            ['attachment' => $attachmentId]
        );
    }

    public function render()
    {
        $statsQuery = $this->getBaseTicketsQuery()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status NOT IN ('approved', 'for_revision') THEN 1 ELSE 0 END) as under_review,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'for_revision' THEN 1 ELSE 0 END) as need_action
            ")
            ->first();

        $ticketStats = [
            'total' => (int) $statsQuery->total,
            'under_review' => (int) $statsQuery->under_review,
            'approved' => (int) $statsQuery->approved,
            'need_action' => (int) $statsQuery->need_action,
        ];

        $ticketsQuery = $this->getBaseTicketsQuery()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('ticket_number', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'under_review') {
                    $query->whereIn('status', ['received', 'amended', 'gso_review', 'pending_osa_approval']);
                } else {
                    $query->where('status', $this->statusFilter);
                }
            })
            ->when($this->dateFilter, function ($query) {
                $now = now();
                switch ($this->dateFilter) {
                    case 'last_week':
                        $query->where('updated_at', '>=', $now->copy()->subWeek());
                        break;
                    case 'last_month':
                        $query->where('updated_at', '>=', $now->copy()->subMonth());
                        break;
                    case 'last_3_months':
                        $query->where('updated_at', '>=', $now->copy()->subMonths(3));
                        break;
                    case 'this_year':
                        $query->whereYear('updated_at', $now->year);
                        break;
                }
            })
            ->orderBy('updated_at', 'desc');

        return view('livewire.student-org.my-ticket', [
            'ticketStats' => $ticketStats,
            'tickets' => $ticketsQuery->paginate(10),
        ]);
    }
}
