<div>
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold font-heading">Transaction Logs</h1>
            <div class="flex gap-2">
                <x-mary-button icon="o-arrow-down-tray" class="btn-outline" wire:click="exportLogs">
                    Export
                </x-mary-button>
                <x-mary-button icon="o-trash" class="btn-error" wire:click="clearLogs">
                    Clear Old Logs
                </x-mary-button>
            </div>
        </div>

        <x-mary-card>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <x-mary-input label="Search Logs" wire:model.live.debounce.300ms="search"
                    placeholder="Search by action, user, or details..." icon="o-magnifying-glass" />

                <x-mary-input label="From Date" wire:model.live="dateFrom" type="date" />

                <x-mary-input label="To Date" wire:model.live="dateTo" type="date" />
            </div>

            <x-mary-table :headers="$headers" :rows="$logs">
                @scope('cell_when', $log)
                    <div class="text-sm">
                        <div class="font-medium">{{ $log->created_at->setTimezone('Asia/Manila')->format('M d, Y') }}</div>
                        <div class="text-gray-500">{{ $log->created_at->setTimezone('Asia/Manila')->format('g:i A') }}</div>
                    </div>
                @endscope

                @scope('cell_who', $log)
                    @if ($log->user)
                        <div class="text-sm">
                            <div class="font-medium">{{ $log->user->name }}</div>
                            <div class="text-gray-500">{{ $log->user->email }}</div>
                        </div>
                    @else
                        <span class="text-gray-500">System</span>
                    @endif
                @endscope

                @scope('cell_action', $log)
                    <x-mary-badge :value="$log->action" :class="match ($log->action) {
                        'CREATE' => 'badge-success',
                        'UPDATE' => 'badge-info',
                        'DELETE' => 'badge-error',
                        'APPROVE' => 'badge-success',
                        'REJECT' => 'badge-warning',
                        default => 'badge-ghost',
                    }" />
                @endscope

                @scope('cell_details', $log)
                    <div class="text-sm max-w-xs" title="{{ $log->details }}">
                        {{ $log->details ?: 'No details' }}
                    </div>
                @endscope
            </x-mary-table>

            @if ($logs->hasPages())
                <x-tickets.ticket-pagination :tickets="$logs" label="logs" />
            @endif
        </x-mary-card>
    </div>
</div>
