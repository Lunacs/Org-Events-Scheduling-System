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
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mary\Traits\Toast;

class Dashboard extends Component
{
    use Toast;
    #[Title('Superadmin - Dashboard')]
    #[Layout('components.layouts.superadmin')]

    public function render()
    {
        return view('livewire.superadmin.dashboard')->with([
            'stats' => $this->stats,
            'pendingApprovals' => $this->pendingApprovals,
            'recentLogs' => $this->recentLogs,
            'headers' => $this->headers,
        ]);
    }

    #[Computed(persist: true, seconds: 300)] // 5 minutes cache
    public function stats(): array
    {
        return Cache::remember('superadmin_dashboard_stats', 300, function () {
            return [
                'totalUsers' => User::count(),
                'totalTickets' => Ticket::count(),
                'totalEvents' => Event::count(),
                'pendingTickets' => Ticket::where('status', 'pending')->count(),
            ];
        });
    }

    #[Computed(persist: true, seconds: 180)] // 3 minutes cache
    public function pendingApprovals(): array
    {
        return Cache::remember('superadmin_dashboard_pending_approvals', 180, function () {
            return Ticket::select(['ticket_id', 'title', 'status', 'created_at', 'user_id', 'event_type_id'])
                ->with([
                    'eventType:event_type_id,type_name',
                    'user:user_id,name'
                ])
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

    #[Computed(persist: true, seconds: 120)] // 2 minutes cache
    public function recentLogs(): array
    {
        return Cache::remember('superadmin_dashboard_recent_logs', 120, function () {
            return Transaction_Logs::select(['log_id', 'action', 'details', 'created_at', 'user_id'])
                ->with('user:user_id,email')
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
        // Clear computed properties and cache
        unset($this->stats, $this->pendingApprovals, $this->recentLogs);
        Cache::forget('superadmin_dashboard_stats');
        Cache::forget('superadmin_dashboard_pending_approvals');
        Cache::forget('superadmin_dashboard_recent_logs');

        $this->success('Dashboard data refreshed!', position: 'toast-top');
    }

    #[Computed]
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
