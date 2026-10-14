<?php

namespace App\Livewire\Superadmin\Archive;

use App\Models\Event;
use App\Models\Ticket;
use App\Services\Cache\ArchiveCacheService;
use App\Services\TransactionLogService;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use Toast, WithPagination;

    #[Title('Archive Management - SuperAdmin')]
    #[Layout('components.layouts.superadmin')]

    // Filters
    public $search = '';

    public $typeFilter = 'all'; // all, events, tickets

    public $dateFrom;

    public $dateTo;

    // Selected items
    public $selectedItems = [];

    public $showRestoreModal = false;

    public $showDeleteModal = false;

    public $itemToAction = null;

    public function mount()
    {
        // Default to last 90 days
        $this->dateTo = now()->format('Y-m-d');
        $this->dateFrom = now()->subDays(90)->format('Y-m-d');
    }

    #[Computed]
    public function archivedItems()
    {
        return ArchiveCacheService::getSuperadminArchives(
            $this->search,
            $this->typeFilter,
            $this->dateFrom,
            $this->dateTo,
            function () {
                $items = collect();
                $from = Carbon::parse($this->dateFrom)->startOfDay();
                $to = Carbon::parse($this->dateTo)->endOfDay();
                $matchesSearch = fn ($q) => $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('ticket_number', 'like', "%{$this->search}%");

                // Completed events: the historical record, read-only here.
                if ($this->typeFilter === 'all' || $this->typeFilter === 'events') {
                    $events = Event::with(['ticket.user' => fn ($q) => $q->withTrashed()->with('studentOrganization'), 'eventSchedules'])
                        ->whereHas('ticket', function ($q) use ($from, $to, $matchesSearch) {
                            $q->where('status', 'completed')
                                ->whereBetween('updated_at', [$from, $to])
                                ->when($this->search, fn ($q2) => $q2->where($matchesSearch));
                        })
                        ->get()
                        ->map(function ($event) {
                            return [
                                'id' => $event->event_id,
                                'type' => 'event',
                                'title' => $event->ticket->title ?? 'Untitled Event',
                                'identifier' => $event->ticket->ticket_number ?? 'N/A',
                                'organization' => $event->ticket?->user?->studentOrganization?->org_name ?? 'N/A',
                                'status' => $event->ticket->status ?? 'completed',
                                'date' => $event->eventSchedules->first()?->start_date,
                                'archived_at' => $event->ticket->updated_at,
                            ];
                        });

                    $items = $items->merge($events);
                }

                // Deleted tickets: can be restored or permanently deleted.
                if ($this->typeFilter === 'all' || $this->typeFilter === 'tickets') {
                    $tickets = Ticket::onlyTrashed()
                        ->with(['user' => fn ($q) => $q->withTrashed()->with('studentOrganization')])
                        ->when($this->search, fn ($q) => $q->where($matchesSearch))
                        ->whereBetween('deleted_at', [$from, $to])
                        ->get()
                        ->map(function ($ticket) {
                            $orgDeleted = $ticket->user?->studentOrganization?->trashed();

                            return [
                                'id' => $ticket->ticket_id,
                                'type' => 'ticket',
                                'title' => $ticket->title,
                                'identifier' => $ticket->ticket_number,
                                'organization' => $orgDeleted ? 'Deleted Organization' : ($ticket->user?->studentOrganization?->org_name ?? 'N/A'),
                                'status' => 'deleted',
                                'date' => $ticket->created_at,
                                'archived_at' => $ticket->deleted_at,
                            ];
                        });

                    $items = $items->merge($tickets);
                }

                return $items->sortByDesc('archived_at')->values();
            }
        );
    }

    public function headers(): array
    {
        return [
            ['key' => 'type', 'label' => 'Type'],
            ['key' => 'title', 'label' => 'Title'],
            ['key' => 'identifier', 'label' => 'ID'],
            ['key' => 'organization', 'label' => 'Organization'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'archived_at', 'label' => 'Archived Date'],
            ['key' => 'actions', 'label' => 'Actions'],
        ];
    }

    public function openRestoreModal($id, $type)
    {
        $this->itemToAction = ['id' => $id, 'type' => $type];
        $this->showRestoreModal = true;
    }

    public function openDeleteModal($id, $type)
    {
        $this->itemToAction = ['id' => $id, 'type' => $type];
        $this->showDeleteModal = true;
    }

    public function restoreItem()
    {
        if (! $this->itemToAction) {
            $this->error('No item selected!', position: 'toast-top');

            return;
        }

        if ($this->itemToAction['type'] !== 'ticket') {
            $this->error('Completed events are kept as a record and cannot be restored.', position: 'toast-top');

            return;
        }

        try {
            $ticket = Ticket::onlyTrashed()->findOrFail($this->itemToAction['id']);
            $ticket->restore();

            TransactionLogService::log('RESTORE', "Restored ticket: {$ticket->title} ({$ticket->ticket_number})", Auth::user()->user_id);

            ArchiveCacheService::clearAllArchives();
            $this->success('Ticket restored successfully!', position: 'toast-top');
            $this->showRestoreModal = false;
            $this->itemToAction = null;
            unset($this->archivedItems);
        } catch (\Exception $e) {
            $this->error('Failed to restore item: '.$e->getMessage(), position: 'toast-top');
        }
    }

    public function permanentlyDelete()
    {
        if (! $this->itemToAction) {
            $this->error('No item selected!', position: 'toast-top');

            return;
        }

        if ($this->itemToAction['type'] !== 'ticket') {
            $this->error('Completed events are kept as a record and cannot be deleted.', position: 'toast-top');

            return;
        }

        try {
            DB::beginTransaction();

            $ticket = Ticket::onlyTrashed()->findOrFail($this->itemToAction['id']);
            $label = "{$ticket->title} ({$ticket->ticket_number})";

            // Delete attachment models first so their files are removed from storage;
            // events, schedules, approvals and comments cascade with the ticket row.
            $ticket->attachments()->get()->each->delete();
            $ticket->forceDelete();

            TransactionLogService::log('DELETE', "Permanently deleted ticket: {$label}", Auth::user()->user_id);

            DB::commit();
            ArchiveCacheService::clearAllArchives();
            $this->success('Ticket permanently deleted!', position: 'toast-top');
            $this->showDeleteModal = false;
            $this->itemToAction = null;
            unset($this->archivedItems);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to delete item: '.$e->getMessage(), position: 'toast-top');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
        unset($this->archivedItems);
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
        unset($this->archivedItems);
    }

    public function updatedDateFrom()
    {
        unset($this->archivedItems);
    }

    public function updatedDateTo()
    {
        unset($this->archivedItems);
    }

    public function render()
    {
        return view('livewire.superadmin.archive.index')->with([
            'items' => $this->archivedItems,
            'headers' => $this->headers(),
        ]);
    }
}
