<div>
    <div class="p-6 space-y-6">
        <section
            class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
            <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-heading font-bold text-base-content">Transaction Logs</h1>
                        <p class="text-sm text-base-content/70 mt-1">
                            Review system transaction events and user actions logs
                        </p>
                    </div>
                    <div class="flex items-center gap-2 relative z-10 w-full sm:w-auto">
                        <x-ui.button icon="o-arrow-down-tray" class="btn-outline bg-base-100" wire:click="exportLogs">
                            Export
                        </x-ui.button>
                        <x-ui.button icon="o-trash" class="btn-error" wire:click="clearLogs">
                            Clear Old Logs
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </section>

        <x-ui.card>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <x-ui.input label="Search Logs" wire:model.live.debounce.300ms="search"
                    placeholder="Search by action, user, or details..." icon="o-magnifying-glass" />

                <x-ui.input label="From Date" wire:model.live="dateFrom" type="date" />

                <x-ui.input label="To Date" wire:model.live="dateTo" type="date" />
            </div>

            <div class="relative">
                <div wire:loading.class="opacity-50 pointer-events-none" class="transition-opacity duration-200">
                    <x-ui.table :headers="collect($headers)->map(fn($h) => array_merge($h, ['sortable' => false]))->all()" :rows="$logs" :paginate="false">
                        @foreach ($logs as $log)
                            <tr wire:key="log-{{ $log->log_id }}">
                                <x-ui.table-column>
                                    <div class="text-sm">
                                        <div class="font-medium">
                                            {{ $log->created_at->setTimezone('Asia/Manila')->format('M d, Y') }}</div>
                                        <div class="text-gray-500">
                                            {{ $log->created_at->setTimezone('Asia/Manila')->format('g:i A') }}</div>
                                    </div>
                                </x-ui.table-column>

                                <x-ui.table-column>
                                    @if ($log->user)
                                        <div class="text-sm">
                                            <div class="font-medium">{{ $log->user->name }}</div>
                                            <div class="text-gray-500">{{ $log->user->email }}</div>
                                        </div>
                                    @else
                                        <span class="text-gray-500">System</span>
                                    @endif
                                </x-ui.table-column>

                                <x-ui.table-column>
                                    <x-ui.badge :value="$log->action" :class="match ($log->action) {
                                        'CREATE' => 'badge-success',
                                        'UPDATE' => 'badge-info',
                                        'DELETE' => 'badge-error',
                                        'APPROVE' => 'badge-success',
                                        'REJECT' => 'badge-warning',
                                        default => 'badge-ghost',
                                    }" />
                                </x-ui.table-column>

                                <x-ui.table-column>
                                    <div class="text-sm max-w-xs" title="{{ $log->details }}">
                                        {{ $log->details ?: 'No details' }}
                                    </div>
                                </x-ui.table-column>
                            </tr>
                        @endforeach

                        <x-slot:empty>
                            <div class="text-center py-12">
                                <div
                                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-base-200 mb-3">
                                    <x-ui.icon name="o-document-magnifying-glass"
                                        class="w-8 h-8 text-base-content/30" />
                                </div>
                                <h3 class="text-lg font-bold text-base-content/70">No logs found</h3>
                                <p class="text-sm text-base-content/50">Try adjusting your search or filters.</p>
                            </div>
                        </x-slot:empty>
                    </x-ui.table>
                </div>
            </div>

            @if ($logs->hasPages())
                <x-tickets.ticket-pagination :tickets="$logs" label="logs" />
            @endif
        </x-ui.card>
    </div>
</div>
