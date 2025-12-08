<div>
    <div class="p-6 space-y-6">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-heading">Reports & Analytics</h1>
                <p class="text-sm text-base-content/60 mt-1">Generate and export system reports</p>
            </div>
            <div class="flex gap-2">
                <x-mary-button icon="o-arrow-path" class="btn-outline" wire:click="refreshData">
                    Refresh
                </x-mary-button>
                <x-mary-button icon="o-arrow-down-tray" class="btn-primary" x-on:click="$refs.exportMenu.showModal()">
                    Export Report
                </x-mary-button>
            </div>
        </div>

        {{-- Filters --}}
        <x-mary-card title="Filters">
            {{-- Active Filters Display --}}
            @if (count($selectedOffices) > 0 || count($selectedEventTypes) > 0)
                <div class="flex flex-wrap gap-2 mb-4 pb-4 border-b border-base-300">
                    @foreach ($selectedOffices as $orgId)
                        @php
                            $org = $offices->firstWhere('org_id', $orgId);
                        @endphp
                        @if ($org)
                            <div class="badge badge-primary gap-2">
                                {{ $org->org_name }}
                                <button
                                    wire:click="$set('selectedOffices', {{ json_encode(array_values(array_diff($selectedOffices, [$orgId]))) }})"
                                    class="btn btn-ghost btn-xs btn-circle">
                                    <x-mary-icon name="o-x-mark" class="w-3 h-3" />
                                </button>
                            </div>
                        @endif
                    @endforeach

                    @foreach ($selectedEventTypes as $typeId)
                        @php
                            $type = $eventTypes->firstWhere('event_type_id', $typeId);
                        @endphp
                        @if ($type)
                            <div class="badge badge-secondary gap-2">
                                {{ $type->type_name }}
                                <button
                                    wire:click="$set('selectedEventTypes', {{ json_encode(array_values(array_diff($selectedEventTypes, [$typeId]))) }})"
                                    class="btn btn-ghost btn-xs btn-circle">
                                    <x-mary-icon name="o-x-mark" class="w-3 h-3" />
                                </button>
                            </div>
                        @endif
                    @endforeach

                    <button wire:click="clearFilters" class="badge badge-ghost gap-2 hover:badge-error">
                        Clear all
                        <x-mary-icon name="o-x-mark" class="w-3 h-3" />
                    </button>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-mary-input label="From Date" wire:model.live="dateFrom" type="date" />

                <x-mary-input label="To Date" wire:model.live="dateTo" type="date" />

                {{-- Organizations Dropdown with Checkboxes --}}
                <x-mary-choices label="Organizations" wire:model.live="selectedOffices" :options="$offices"
                    option-value="org_id" option-label="org_name" searchable placeholder="Select organizations..."
                    height="max-h-60" icon="o-building-office" search-function />

                {{-- Event Types Dropdown with Checkboxes --}}
                <x-mary-choices label="Event Types" wire:model.live="selectedEventTypes" :options="$eventTypes"
                    option-value="event_type_id" option-label="type_name" searchable placeholder="Select event types..."
                    height="max-h-60" icon="o-calendar" search-function />
            </div>
        </x-mary-card>

        {{-- Overview Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stats shadow-lg border-primary border-2 bg-primary/20 text-primary-content">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <x-mary-icon name="o-calendar" class="w-8 h-8" />
                    </div>
                    <div class="stat-title text-primary-content opacity-90">Total Events</div>
                    <div class="stat-value text-primary-content">{{ $chartData['overview']['total_events'] ?? 0 }}
                    </div>
                    <div class="stat-desc text-primary-content opacity-50">In selected period</div>
                </div>
            </div>

            <div class="stats shadow-lg border-primary border-2 bg-success/20">
                <div class="stat">
                    <div class="stat-figure text-success">
                        <x-mary-icon name="o-check-circle" class="w-8 h-8" />
                    </div>
                    <div class="stat-title text-success-content opacity-90">Approved Events</div>
                    <div class="stat-value text-success-content">{{ $chartData['overview']['approved_events'] ?? 0 }}
                    </div>
                    <div class="stat-desc text-success-content opacity-50">Successfully approved</div>
                </div>
            </div>

            <div class="stats shadow-lg border-info border-2 bg-info/20 text-info-content">
                <div class="stat">
                    <div class="stat-figure text-info">
                        <x-mary-icon name="o-ticket" class="w-8 h-8" />
                    </div>
                    <div class="stat-title text-info-content opacity-90">Total Tickets</div>
                    <div class="stat-value text-info-content">{{ $chartData['overview']['total_tickets'] ?? 0 }}</div>
                    <div class="stat-desc text-info-content opacity-50">All submissions</div>
                </div>
            </div>

            <div class="stats shadow-lg border-accent border-2 bg-accent/40 text-accent-content">
                <div class="stat">
                    <div class="stat-figure text-accent">
                        <x-mary-icon name="o-user-group" class="w-8 h-8" />
                    </div>
                    <div class="stat-title text-accent-content opacity-90">Active Organizations</div>
                    <div class="stat-value text-accent-content">{{ $chartData['overview']['active_orgs'] ?? 0 }}</div>
                    <div class="stat-desc text-accent-content opacity-50">With events</div>
                </div>
            </div>
        </div>

        {{-- Charts Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Events by Month Chart --}}
            <x-mary-card title="Events by Month" class="shadow-lg">
                <div class="h-64 flex items-center justify-center">
                    <canvas id="eventsByMonthChart" width="400" height="200"></canvas>
                </div>
            </x-mary-card>

            {{-- Events by Type Chart --}}
            <x-mary-card title="Events by Type" class="shadow-lg">
                <div class="h-64 flex items-center justify-center">
                    <canvas id="eventsByTypeChart" width="400" height="200"></canvas>
                </div>
            </x-mary-card>

            {{-- Events by Office Chart --}}
            <x-mary-card title="Events by Organization" class="shadow-lg">
                <div class="h-64 flex items-center justify-center">
                    <canvas id="eventsByOfficeChart" width="400" height="200"></canvas>
                </div>
            </x-mary-card>

            {{-- Ticket Status Distribution --}}
            <x-mary-card title="Ticket Status Distribution" class="shadow-lg">
                <div class="h-64 flex items-center justify-center">
                    <canvas id="ticketStatusChart" width="400" height="200"></canvas>
                </div>
            </x-mary-card>
        </div>

        {{-- Users by Role Chart --}}
        <x-mary-card title="Users by Role" class="shadow-lg">
            <div class="h-64 flex items-center justify-center">
                <canvas id="usersByRoleChart" width="800" height="200"></canvas>
            </div>
        </x-mary-card>
    </div>

    {{-- Export Modal --}}
    <x-mary-modal id="exportMenu" title="Export Report" x-ref="exportMenu">
        <div class="space-y-4">
            <p class="text-sm text-base-content/70">
                Choose a format to export the current report data.
            </p>
            <div class="flex flex-col gap-2">
                <x-mary-button icon="o-document-text" class="btn-outline btn-block justify-start"
                    wire:click="exportReport('csv')" x-on:click="$refs.exportMenu.close()">
                    Export as CSV
                </x-mary-button>
                <x-mary-button icon="o-document" class="btn-outline btn-block justify-start"
                    wire:click="exportReport('pdf')" x-on:click="$refs.exportMenu.close()">
                    Export as PDF
                </x-mary-button>
            </div>
        </div>
    </x-mary-modal>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:navigated', () => {
            initializeCharts();
        });

        function initializeCharts() {
            // Check if Chart.js is available
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return;
            }

            const chartData = @json($chartData);

            // Events by Month Chart
            if (document.getElementById('eventsByMonthChart')) {
                new Chart(document.getElementById('eventsByMonthChart'), {
                    type: 'line',
                    data: {
                        labels: chartData.eventsByMonth.labels,
                        datasets: [{
                            label: 'Events',
                            data: chartData.eventsByMonth.data,
                            borderColor: 'rgb(79, 209, 197)',
                            backgroundColor: 'rgba(79, 209, 197, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            // Events by Type Chart
            if (document.getElementById('eventsByTypeChart')) {
                new Chart(document.getElementById('eventsByTypeChart'), {
                    type: 'doughnut',
                    data: {
                        labels: chartData.eventsByType.labels,
                        datasets: [{
                            data: chartData.eventsByType.data,
                            backgroundColor: [
                                'rgb(79, 209, 197)',
                                'rgb(99, 102, 241)',
                                'rgb(251, 191, 36)',
                                'rgb(248, 113, 113)',
                                'rgb(168, 85, 247)',
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            // Events by Office Chart
            if (document.getElementById('eventsByOfficeChart')) {
                new Chart(document.getElementById('eventsByOfficeChart'), {
                    type: 'bar',
                    data: {
                        labels: chartData.eventsByOffice.labels,
                        datasets: [{
                            label: 'Events',
                            data: chartData.eventsByOffice.data,
                            backgroundColor: 'rgb(79, 209, 197)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            // Ticket Status Chart
            if (document.getElementById('ticketStatusChart')) {
                new Chart(document.getElementById('ticketStatusChart'), {
                    type: 'pie',
                    data: {
                        labels: chartData.ticketStatusDistribution.labels,
                        datasets: [{
                            data: chartData.ticketStatusDistribution.data,
                            backgroundColor: [
                                'rgb(34, 197, 94)',
                                'rgb(59, 130, 246)',
                                'rgb(251, 191, 36)',
                                'rgb(239, 68, 68)',
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            // Users by Role Chart
            if (document.getElementById('usersByRoleChart')) {
                new Chart(document.getElementById('usersByRoleChart'), {
                    type: 'bar',
                    data: {
                        labels: chartData.usersByRole.labels,
                        datasets: [{
                            label: 'Users',
                            data: chartData.usersByRole.data,
                            backgroundColor: 'rgb(99, 102, 241)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        }
    </script>
@endpush
