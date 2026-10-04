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
        <x-ui.card title="Pending Approvals" subtitle="Tickets still waiting on OSA or GSO." class="lg:col-span-2">
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

<x-ui.card title="Pending Approvals" subtitle="Tickets still waiting on OSA or GSO." class="lg:col-span-2">
    <x-slot:menu>
        <a href="{{ route('superadmin.tickets') }}" wire:navigate class="btn btn-ghost btn-sm">View all</a>
    </x-slot:menu>
    @if (count($this->pendingApprovals) > 0)
        <div class="-mx-4 -mb-4 overflow-x-auto sm:-mx-5 sm:-mb-5">
            <table class="table tabular-nums">
                <thead>
                    <tr class="border-base-300 bg-base-200/60 text-[0.8125rem] font-medium text-base-content/70">
                        <th scope="col" class="font-medium">Request</th>
                        <th scope="col" class="font-medium">Type</th>
                        <th scope="col" class="font-medium">Submitted</th>
                        <th scope="col" class="font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->pendingApprovals as $approval)
                        <tr class="hover:bg-base-200/50">
                            <td class="max-w-64">
                                <a href="{{ route('superadmin.tickets') }}" wire:navigate
                                    class="block truncate font-medium text-base-content hover:underline">{{ $approval['request'] }}</a>
                                <div class="text-xs text-base-content/60">{{ $approval['user'] }}</div>
                            </td>
                            <td class="text-sm">{{ $approval['type'] }}</td>
                            <td class="text-sm">{{ $approval['submitted'] }}</td>
                            <td>
                                <x-tickets.progress-badge :status="$approval['raw_status']" size="sm" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-ui.empty-state icon="o-check-circle" title="No pending approvals"
            description="Tickets waiting on OSA or GSO appear here." />
    @endif
</x-ui.card>
