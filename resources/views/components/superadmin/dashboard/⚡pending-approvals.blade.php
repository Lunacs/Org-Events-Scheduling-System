<?php

use App\Models\Ticket;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    #[Computed(persist: true, seconds: 180)]
    public function pendingApprovals(): array
    {
        return Cache::remember('superadmin_dashboard_pending_approvals', 180, function () {
            return Ticket::select(['ticket_id', 'title', 'status', 'created_at', 'user_id', 'event_type_id'])
                ->with(['eventType:event_type_id,type_name', 'user:user_id,name'])
                ->whereIn('status', ['pending', 'gso_review', 'pending_osa_approval'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($ticket) {
                    $userDeleted = $ticket->user?->trashed();

                    return [
                        'id' => $ticket->ticket_id,
                        'request' => $ticket->title,
                        'type' => $ticket->eventType ? $ticket->eventType->type_name : 'N/A',
                        'submitted' => $ticket->created_at->setTimezone('Asia/Manila')->format('M d, Y g:i A'),
                        'status' => ucfirst(str_replace('_', ' ', $ticket->status)),
                        'raw_status' => $ticket->status,
                        'user' => $userDeleted ? 'Deleted User' : ($ticket->user ? $ticket->user->name : 'Unknown'),
                    ];
                })
                ->toArray();
        });
    }

    public function placeholder()
    {
        return <<<'HTML'
        <x-ui.card title="Pending Approvals" subtitle="Tickets awaiting review" class="lg:col-span-2" shadow>
            <div class="animate-pulse space-y-3">
                <div class="h-4 bg-base-300 rounded w-full"></div>
                <div class="h-4 bg-base-300 rounded w-3/4"></div>
                <div class="h-4 bg-base-300 rounded w-5/6"></div>
                <div class="h-4 bg-base-300 rounded w-2/3"></div>
            </div>
        </x-ui.card>
        HTML;
    }
};
?>

<x-ui.card title="Pending Approvals" subtitle="Tickets awaiting review" class="lg:col-span-2" shadow>
    <x-slot:menu>
        <a href="{{ route('superadmin.tickets') }}" class="btn btn-ghost btn-sm">View All</a>
    </x-slot:menu>
    @if (count($this->pendingApprovals) > 0)
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Type</th>
                        <th>Submitted</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->pendingApprovals as $approval)
                        <tr class="hover cursor-pointer" onclick="window.location='{{ route('superadmin.tickets') }}'">
                            <td>
                                <div class="font-medium">{{ Str::limit($approval['request'], 30) }}</div>
                                <div class="text-xs text-base-content/60">{{ $approval['user'] }}</div>
                            </td>
                            <td class="text-sm">{{ $approval['type'] }}</td>
                            <td class="text-sm">{{ $approval['submitted'] }}</td>
                            <td>
                                <span
                                    class="badge badge-sm {{ match ($approval['raw_status']) {
                                        'pending' => 'badge-warning',
                                        'gso_review' => 'badge-info',
                                        'pending_osa_approval' => 'badge-secondary',
                                        default => 'badge-ghost',
                                    } }}">
                                    {{ $approval['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-success/10 mb-4">
                <x-ui.icon name="o-check-circle" class="w-8 h-8 text-success" />
            </div>
            <h3 class="text-lg font-semibold text-base-content mb-1">All Caught Up!</h3>
            <p class="text-sm text-base-content/60">No pending approvals at the moment.</p>
        </div>
    @endif
</x-ui.card>
