<?php

namespace App\Livewire\Superadmin\SystemSettings;

use App\Models\Venue;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class VenueManager extends Component
{
    use Toast;

    // Delete data
    public $deletingVenueId = null;
    public $deletingVenueName = '';
    public $hasAssociatedTickets = false;
    public $associatedTicketsCount = 0;
    public $deleteModalOpen = false;

    // Cache duration
    protected $cacheDuration = 10;

    public function render()
    {
        return view('livewire.superadmin.system-settings.venue-manager', [
            'venues' => $this->getVenues(),
        ]);
    }

    protected function getVenues()
    {
        return Cache::remember('venues', $this->cacheDuration, function () {
            return Venue::orderBy('created_at', 'desc')->get();
        });
    }

    public function openDeleteModal($venueId)
    {
        $venue = Venue::find($venueId);
        if ($venue) {
            $this->deletingVenueId = $venueId;
            $this->deletingVenueName = $venue->venue_name;
            $this->associatedTicketsCount = $venue->tickets()->count();
            $this->hasAssociatedTickets = $this->associatedTicketsCount > 0;
            $this->deleteModalOpen = true;
        }
    }

    public function resetDeleteModal()
    {
        $this->reset(['deletingVenueId', 'deletingVenueName', 'hasAssociatedTickets', 'associatedTicketsCount']);
        $this->deleteModalOpen = false;
    }

    public function confirmDelete()
    {
        $venue = Venue::find($this->deletingVenueId);

        if (!$venue) {
            $this->error('Venue not found!', position: 'toast-top');
            return;
        }

        // Block deletion if venue has associated tickets
        if ($venue->tickets()->count() > 0) {
            $this->error('Cannot delete venue that is being used by tickets! Consider deactivating it instead.', position: 'toast-top');
            return;
        }

        DB::beginTransaction();
        try {
            // Log the venue deletion before deleting
            TransactionLogService::logVenueOperation('deleted', $venue);

            $venue->delete();

            DB::commit();

            $this->reset(['deletingVenueId', 'deletingVenueName', 'hasAssociatedTickets', 'associatedTicketsCount']);
            $this->deleteModalOpen = false;
            $this->clearVenuesCache();
            $this->success('Venue deleted successfully!', position: 'toast-top');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Venue deletion failed', ['error' => $e->getMessage()]);
            $this->error('Failed to delete venue: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    /**
     * Toggle venue active status
     * Deactivating only prevents new ticket submissions, doesn't affect existing tickets
     */
    public function toggleActive($venueId)
    {
        $venue = Venue::find($venueId);

        if (!$venue) {
            $this->error('Venue not found!', position: 'toast-top');
            return;
        }

        DB::beginTransaction();
        try {
            $previousStatus = $venue->is_active ? 'active' : 'inactive';
            $venue->is_active = !$venue->is_active;
            $venue->save();

            $newStatus = $venue->is_active ? 'active' : 'inactive';
            TransactionLogService::logVenueOperation('updated', $venue, ["Status: {$previousStatus} → {$newStatus}"]);

            DB::commit();

            $this->clearVenuesCache();
            $statusLabel = $venue->is_active ? 'activated' : 'deactivated';
            $this->success("Venue {$statusLabel} successfully!", position: 'toast-top');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Venue status toggle failed', ['error' => $e->getMessage()]);
            $this->error('Failed to update venue status: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    protected function clearVenuesCache()
    {
        Cache::forget('venues');
        $this->dispatch('cache-cleared');
    }

    #[On('refresh-cache')]
    public function refreshCache()
    {
        Cache::forget('venues');
    }
}
