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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-mary-input label="From Date" wire:model.live="dateFrom" type="date" />

                <x-mary-input label="To Date" wire:model.live="dateTo" type="date" />

                <x-mary-select label="Organizations" wire:model.live="selectedOffices" :options="$offices"
                    option-value="org_id" option-label="org_name" multiple />

                <x-mary-select label="Event Types" wire:model.live="selectedEventTypes" :options="$eventTypes"
                    option-value="event_type_id" option-label="type_name" multiple />
            </div>
        </x-mary-card>

        {{-- Overview Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-mary-stat title="Total Events" description="In selected period" :value="$chartData['overview']['total_events'] ?? 0" icon="o-calendar"
                class="bg-primary text-primary-content shadow-lg" />

            <x-mary-stat title="Approved Events" description="Successfully approved" :value="$chartData['overview']['approved_events'] ?? 0"
                icon="o-check-circle" class="bg-success text-success-content shadow-lg" />

            <x-mary-stat title="Total Tickets" description="All submissions" :value="$chartData['overview']['total_tickets'] ?? 0" icon="o-ticket"
                class="bg-info text-info-content shadow-lg" />

            <x-mary-stat title="Active Organizations" description="With events" :value="$chartData['overview']['active_orgs'] ?? 0" icon="o-user-group"
                class="bg-accent text-accent-content shadow-lg" />
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
