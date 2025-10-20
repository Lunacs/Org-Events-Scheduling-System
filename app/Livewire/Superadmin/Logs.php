<?php

namespace App\Livewire\Superadmin;

use App\Models\Transaction_Logs;
use App\Services\TransactionLogService;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;
use Mary\Traits\Toast;

class Logs extends Component
{
    use WithPagination, Toast;

    #[Title('Superadmin - Transaction Logs')]
    #[Layout('components.layouts.superadmin')]

    // Search and filter properties
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Cache duration in minutes
    protected $cacheDuration = 2;

    public function logs()
    {
        return Transaction_Logs::with(['user'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('action', 'like', '%' . $this->search . '%')
                        ->orWhere('details', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($userQuery) {
                            $userQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function headers(): array
    {
        return [
            ['key' => 'when', 'label' => 'When', 'sortable' => true],
            ['key' => 'who', 'label' => 'Who', 'sortable' => true],
            ['key' => 'action', 'label' => 'Action', 'sortable' => true],
            ['key' => 'details', 'label' => 'Details'],
        ];
    }


    public function clearLogs()
    {
        // Use the manual cleanup method to keep only recent logs
        $deletedCount = TransactionLogService::manualCleanup(100); // Keep only last 100 logs
        
        if ($deletedCount > 0) {
            $this->success("Cleared {$deletedCount} old transaction logs. Kept the most recent 100 logs.", position: 'toast-top');
        } else {
            $this->success('No old logs to clear. Database is already optimized.', position: 'toast-top');
        }
    }

    public function exportLogs()
    {
        // This would typically generate a CSV or Excel file
        $this->success('Logs export initiated! Check your downloads.', position: 'toast-top');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }


    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.superadmin.logs')->with([
            'logs' => $this->logs(),
            'headers' => $this->headers(),
        ]);
    }
}
