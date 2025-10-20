<div>
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold font-heading">SuperAdmin Dashboard</h1>
            <x-mary-button icon="o-arrow-path" class="btn-outline" wire:click="refreshData">
                Refresh Data
            </x-mary-button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-mary-stat title="Total Users" :value="number_format($stats['totalUsers'])" icon="o-users" />
            <x-mary-stat title="Total Tickets" :value="number_format($stats['totalTickets'])" icon="o-ticket" />
            <x-mary-stat title="Total Events" :value="number_format($stats['totalEvents'])" icon="o-calendar-days" />
            <x-mary-stat title="Pending Tickets" :value="number_format($stats['pendingTickets'])" icon="o-clock" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-mary-card title="Pending Approvals" class="col-span-1 lg:col-span-2">
                @if (count($pendingApprovals) > 0)
                    <x-mary-table :headers="$headers" :rows="$pendingApprovals">
                        @scope('cell_status', $row)
                            <x-mary-badge :value="$row['status']" :class="match ($row['status']) {
                                'Pending' => 'badge-warning',
                                'Approved' => 'badge-success',
                                'Rejected' => 'badge-error',
                                default => 'badge-ghost',
                            }" />
                        @endscope
                    </x-mary-table>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <x-mary-icon name="o-check-circle" class="w-12 h-12 mx-auto mb-2" />
                        <p>No pending approvals</p>
                    </div>
                @endif
            </x-mary-card>

            <x-mary-card shadow separator title="Recent Activity">
                @if (count($recentLogs) > 0)
                    <ul class="space-y-2">
                        @foreach ($recentLogs as $log)
                            <li>
                                <x-mary-list-item :item="[
                                    'title' => $log['user'],
                                    'subtitle' => $log['action'] . ' - ' . $log['target'],
                                ]" value="title" sub-value="subtitle"
                                    icon="o-clock" />
                                <div class="text-xs text-gray-500 ml-8">{{ $log['timestamp'] }}</div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4 text-gray-500">
                        <x-mary-icon name="o-document-text" class="w-8 h-8 mx-auto mb-2" />
                        <p>No recent activity</p>
                    </div>
                @endif
            </x-mary-card>
        </div>
    </div>
</div>
