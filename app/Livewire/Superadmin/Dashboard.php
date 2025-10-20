<?php

namespace App\Livewire\Superadmin;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Transaction_Logs;
use App\Services\TransactionLogService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mary\Traits\Toast;

class Dashboard extends Component
{
    use Toast;
    #[Title('Superadmin - Dashboard')]
    #[Layout('components.layouts.superadmin')]

    // Cache duration in minutes
    protected $cacheDuration = 5;

    public function render()
    {
        return view('livewire.superadmin.dashboard')->with([
            'stats' => $this->getStats(),
            'pendingApprovals' => $this->getPendingApprovals(),
            'recentLogs' => $this->getRecentLogs(),
            'headers' => $this->headers(),
        ]);
    }

    protected function getStats(): array
    {
        return Cache::remember('dashboard_stats', $this->cacheDuration, function () {
            return [
                'totalUsers' => User::count(),
                'totalTickets' => Ticket::count(),
                'totalEvents' => Event::count(),
                'pendingTickets' => Ticket::where('status', 'pending')->count(),
            ];
        });
    }

    protected function getPendingApprovals(): array
    {
        return Cache::remember('dashboard_pending_approvals', $this->cacheDuration, function () {
            return Ticket::with(['eventType', 'user'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->ticket_id,
                        'request' => $ticket->title,
                        'type' => $ticket->eventType ? $ticket->eventType->type_name : 'N/A',
                        'submitted' => $ticket->created_at->setTimezone('Asia/Manila')->format('M d, Y g:i A'),
                        'status' => ucfirst($ticket->status),
                        'user' => $ticket->user ? $ticket->user->name : 'Unknown',
                    ];
                })
                ->toArray();
        });
    }

    protected function getRecentLogs(): array
    {
        return Cache::remember('dashboard_recent_logs', $this->cacheDuration, function () {
            return Transaction_Logs::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($log) {
                    return [
                        'user' => $log->user ? $log->user->email : 'System',
                        'action' => $log->action,
                        'target' => $log->details,
                        'timestamp' => $log->created_at->setTimezone('Asia/Manila')->format('M d, Y g:i A'),
                    ];
                })
                ->toArray();
        });
    }

    public function refreshData()
    {
        // Clear cache to force refresh
        Cache::forget('dashboard_stats');
        Cache::forget('dashboard_pending_approvals');
        Cache::forget('dashboard_recent_logs');
        
        $this->success('Dashboard data refreshed!', position: 'toast-top');
    }

    public function headers(): array
    {
        return [
            ['key' => 'request', 'label' => 'Request'],
            ['key' => 'type', 'label' => 'Type'],
            ['key' => 'submitted', 'label' => 'Submitted'],
            ['key' => 'status', 'label' => 'Status'],
        ];
    }
}
