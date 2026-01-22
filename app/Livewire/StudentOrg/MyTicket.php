<?php

namespace App\Livewire\StudentOrg;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class MyTicket extends Component
{
    use WithPagination;

    #[Title('My Ticket - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]
    public $search = '';

    public $statusFilter = '';

    public $dateFilter = '';

    public $showDetailsModal = false;

    public $showCommentsModal = false;

    public $showEditDrawer = false;

    public $selectedTicketId;
    public $isLoadingTicket = false;

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

        // Close the drawer
        $this->closeEditDrawer();
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

    #[On('open-ticket-edit')]
    public function openEditDrawer($ticketId = null)
    {
        $this->selectedTicketId = $ticketId;
        $this->showEditDrawer = true;

        // Dispatch event to edit form component to load ticket data
        $this->dispatch('load-ticket-for-edit', ticketId: $ticketId);
    }


    #[On('close-edit-drawer')]
    public function closeEditDrawer()
    {
        $this->showEditDrawer = false;
        $this->selectedTicketId = null;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateFilter = '';
    }

    public function getSelectedTicketProperty()
    {
        if (!$this->selectedTicketId) {
            return null;
        }

        return auth()->user()->tickets()
            ->with(['eventType', 'comments', 'attachments', 'fundSource', 'user.studentOrganization.course', 'user.position'])
            ->find($this->selectedTicketId);
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

        $url = $this->makeTemporaryUrl($attachment->file_path, $attachment->file_name, false);

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

        $url = $this->makeTemporaryUrl($attachment->file_path, $attachment->file_name, true);

        $this->dispatch('download-attachment', url: $url, filename: $attachment->file_name);
    }

    /**
     * Build a temporary URL from the configured filesystem. Falls back to public URL if unsupported.
     */
    private function makeTemporaryUrl(string $path, string $filename, bool $forceDownload = false): string
    {
        $disk = Storage::disk(config('filesystems.default'));

        try {
            if (method_exists($disk, 'temporaryUrl')) {
                $options = [
                    'ResponseContentDisposition' => ($forceDownload ? 'attachment' : 'inline') . '; filename="' . addslashes($filename) . '"',
                ];

                return $disk->temporaryUrl($path, now()->addMinutes(5), $options);
            }
        } catch (\Throwable $e) {
            // Fallback below if temporary URLs are unavailable for the disk
        }

        return Storage::url($path);
    }

    public function render()
    {
        $allTickets = auth()->user()->tickets()->with('eventType')->with('venue')->get();
        $ticketsQuery = auth()->user()->tickets()->with('eventType')->with('venue')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('ticket_number', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'under_review') {
                    $query->whereIn('status', ['received', 'amended', 'rescheduled', 'gso_review', 'pending_osa_approval']);
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
            'allTickets' => $allTickets,
            'tickets' => $ticketsQuery->paginate(10),
        ]);
    }
}
