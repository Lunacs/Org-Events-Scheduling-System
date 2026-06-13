<?php

namespace App\Livewire\Superadmin\Archive;

use App\Models\Event;
use App\Models\Ticket;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

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
        return \App\Services\Cache\ArchiveCacheService::getSuperadminArchives(
            $this->search,
            $this->typeFilter,
            $this->dateFrom,
            $this->dateTo,
            function () {
                $items = collect();

                // Get archived/cancelled events
                if ($this->typeFilter === 'all' || $this->typeFilter === 'events') {
                    $events = Event::with(['ticket', 'studentOrganization', 'eventType'])
                        ->whereHas('ticket', function ($q) {
                            $q->whereIn('status', ['cancelled', '']);
                        })
                        ->when($this->search, function ($q) {
                            $q->whereHas('ticket', function ($q2) {
                                $q2->where('title', 'like', "%{$this->search}%")
                                    ->orWhere('ticket_number', 'like', "%{$this->search}%");
                            });
                        })
                        ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
                        ->get()
                        ->map(function ($event) {
                            return [
                                'id' => $event->event_id,
                                'type' => 'event',
                                'title' => $event->ticket->title ?? 'Untitled Event',
                                'identifier' => $event->ticket->ticket_number ?? 'N/A',
                                'organization' => $event->studentOrganization->org_name ?? 'N/A',
                                'status' => $event->ticket->status ?? 'cancelled',
                                'date' => $event->event_date,
                                'archived_at' => $event->updated_at,
                            ];
                        });

                    $items = $items->merge($events);
                }

                // Get cancelled/ tickets (without events)
                if ($this->typeFilter === 'all' || $this->typeFilter === 'tickets') {
                    $tickets = Ticket::with(['user.studentOrganization'])
                        ->whereIn('status', ['cancelled', ''])
                        ->whereDoesntHave('events')
                        ->when($this->search, function ($q) {
                            $q->where('title', 'like', "%{$this->search}%")
                                ->orWhere('ticket_number', 'like', "%{$this->search}%");
                        })
                        ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
                        ->get()
                        ->map(function ($ticket) {
                            $orgDeleted = $ticket->user?->studentOrganization?->trashed();
                            return [
                                'id' => $ticket->ticket_id,
                                'type' => 'ticket',
                                'title' => $ticket->title,
                                'identifier' => $ticket->ticket_number,
                                'organization' => $orgDeleted ? 'Deleted Organization' : ($ticket->user?->studentOrganization?->org_name ?? 'N/A'),
                                'status' => $ticket->status,
                                'date' => $ticket->created_at,
                                'archived_at' => $ticket->updated_at,
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
        if (!$this->itemToAction) {
            $this->error('No item selected!', position: 'toast-top');
            return;
        }

        try {
            DB::beginTransaction();

            if ($this->itemToAction['type'] === 'event') {
                $event = Event::with('ticket')->find($this->itemToAction['id']);
                if ($event && $event->ticket) {
                    $event->ticket->update(['status' => 'pending']);
                    $message = "Restored event: {$event->ticket->title}";
                }
            } else {
                $ticket = Ticket::find($this->itemToAction['id']);
                if ($ticket) {
                    $ticket->update(['status' => 'pending']);
                    $message = "Restored ticket: {$ticket->title}";
                }
            }

            // Log action
            TransactionLogService::log(
                'RESTORE',
                $message ?? 'Restored archived item',
                Auth::user()->user_id
            );

            DB::commit();
            \App\Services\Cache\ArchiveCacheService::clearAllArchives();
            $this->success('Item restored successfully!', position: 'toast-top');
            $this->showRestoreModal = false;
            $this->itemToAction = null;
            unset($this->archivedItems);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to restore item: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function permanentlyDelete()
    {
        if (!$this->itemToAction) {
            $this->error('No item selected!', position: 'toast-top');
            return;
        }

        try {
            DB::beginTransaction();

            if ($this->itemToAction['type'] === 'event') {
                $event = Event::with('ticket', 'eventSchedules')->find($this->itemToAction['id']);
                if ($event) {
                    $title = $event->ticket->title ?? 'Unknown Event';

                    // Delete schedules
                    $event->eventSchedules()->delete();

                    // Delete ticket
                    if ($event->ticket) {
                        $event->ticket->delete();
                    }

                    // Delete event
                    $event->delete();

                    $message = "Permanently deleted event: {$title}";
                }
            } else {
                $ticket = Ticket::find($this->itemToAction['id']);
                if ($ticket) {
                    $title = $ticket->title;
                    $ticket->delete();
                    $message = "Permanently deleted ticket: {$title}";
                }
            }

            // Log action
            TransactionLogService::log(
                'DELETE',
                $message ?? 'Permanently deleted archived item',
                Auth::user()->user_id
            );

            DB::commit();
            \App\Services\Cache\ArchiveCacheService::clearAllArchives();
            $this->success('Item permanently deleted!', position: 'toast-top');
            $this->showDeleteModal = false;
            $this->itemToAction = null;
            unset($this->archivedItems);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to delete item: ' . $e->getMessage(), position: 'toast-top');
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
