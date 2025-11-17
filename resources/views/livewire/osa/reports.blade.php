<div>
    {{-- Header --}}
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-base-content">Reports</h1>
                    <p class="text-base-content/70 mt-1">Generate comprehensive reports on events, approvals, and
                        organization participation</p>
                </div>
                <x-mary-button wire:click="generateReport" class="btn-primary" icon="o-document-arrow-down">
                    Generate Report
                </x-mary-button>
            </div>
        </div>
    </div>

    {{-- Report Configuration --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Report Type Selection --}}
        <div class="lg:col-span-2">
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Report Configuration</h2>

                <div class="space-y-6">
                    {{-- Report Type --}}
                    <div>
                        <x-mary-radio wire:model.live="reportType" label="Report Type" :options="[
                            [
                                'id' => 'approved_events',
                                'name' => 'Approved Events Report',
                                'description' => 'List of all approved event requests',
                            ],
                            [
                                'id' => 'rejected_events',
                                'name' => 'Rejected Events Report',
                                'description' => 'List of all rejected event requests',
                            ],
                            [
                                'id' => 'org_participation',
                                'name' => 'Organization Participation',
                                'description' => 'Event activity by organization',
                            ],
                            [
                                'id' => 'monthly_summary',
                                'name' => 'Monthly Summary',
                                'description' => 'Overall statistics and metrics',
                            ],
                        ]"
                            option-value="id" option-label="name" />
                    </div>

                    {{-- Date Range --}}
                    <div class="grid grid-cols-2 gap-4">
                        <x-mary-input wire:model="dateFrom" label="From Date" type="date" />
                        <x-mary-input wire:model="dateTo" label="To Date" type="date" />
                    </div>

                    {{-- Organization Filter --}}
                    <x-mary-select wire:model="organizationFilter" label="Filter by Organization (Optional)"
                        placeholder="All Organizations" :options="$organizations" option-value="org_id" option-label="org_name"
                        clearable />

                    {{-- Export Format --}}
                    <x-mary-select wire:model="exportFormat" label="Export Format" :options="[
                        ['id' => 'pdf', 'name' => 'PDF Document'],
                        ['id' => 'excel', 'name' => 'Excel Spreadsheet'],
                        ['id' => 'csv', 'name' => 'CSV File'],
                    ]" option-value="id"
                        option-label="name" />

                    <div class="flex gap-2">
                        <x-mary-button wire:click="generateReport" class="btn-primary" icon="o-document-arrow-down">
                            Generate & Download
                        </x-mary-button>
                        <x-mary-button wire:click="clearFilters" class="btn-ghost" icon="o-x-mark">
                            Clear Filters
                        </x-mary-button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="space-y-4">
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-success/10 p-2 rounded-full">
                        <x-mary-icon name="o-check-circle" class="w-5 h-5 text-success" />
                    </div>
                    <h3 class="font-semibold">Approved Events</h3>
                </div>
                <div class="text-2xl font-bold text-success">
                    @if (is_array($reportData) && $reportType === 'monthly_summary')
                        {{ $reportData['approved_tickets'] ?? 0 }}
                    @elseif($reportType === 'approved_events')
                        {{ $reportData->count() }}
                    @else
                        --
                    @endif
                </div>
                <p class="text-sm text-base-content/70">This period</p>
            </div>

            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-error/10 p-2 rounded-full">
                        <x-mary-icon name="o-x-circle" class="w-5 h-5 text-error" />
                    </div>
                    <h3 class="font-semibold">Rejected Events</h3>
                </div>
                <div class="text-2xl font-bold text-error">
                    @if (is_array($reportData) && $reportType === 'monthly_summary')
                        {{ $reportData['rejected_tickets'] ?? 0 }}
                    @elseif($reportType === 'rejected_events')
                        {{ $reportData->count() }}
                    @else
                        --
                    @endif
                </div>
                <p class="text-sm text-base-content/70">This period</p>
            </div>

            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-warning/10 p-2 rounded-full">
                        <x-mary-icon name="o-clock" class="w-5 h-5 text-warning" />
                    </div>
                    <h3 class="font-semibold">Pending Reviews</h3>
                </div>
                <div class="text-2xl font-bold text-warning">
                    @if (is_array($reportData) && $reportType === 'monthly_summary')
                        {{ $reportData['pending_tickets'] ?? 0 }}
                    @else
                        --
                    @endif
                </div>
                <p class="text-sm text-base-content/70">Awaiting approval</p>
            </div>
        </div>
    </div>

    {{-- Report Preview --}}
    <div class="bg-base-100 rounded-box shadow-lg">
        <div class="p-6 border-b border-base-300">
            <h2 class="text-lg font-semibold">Report Preview</h2>
            <p class="text-sm text-base-content/70">Preview of {{ ucfirst(str_replace('_', ' ', $reportType)) }}</p>
        </div>

        {{-- Loading Skeleton during report generation --}}
        <div wire:loading.delay wire:target="generateReport,reportType" class="p-6 animate-pulse">
            <div class="space-y-4">
                <div class="h-12 bg-base-200 rounded"></div>
                <div class="h-16 bg-base-200 rounded"></div>
                <div class="h-16 bg-base-200 rounded"></div>
                <div class="h-16 bg-base-200 rounded"></div>
                <div class="h-16 bg-base-200 rounded"></div>
            </div>
            <div class="text-center mt-6">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="text-sm text-base-content/70 mt-2">Generating report...</p>
            </div>
        </div>

        {{-- Actual Report Content --}}
        <div wire:loading.remove.delay wire:target="generateReport,reportType" class="p-6">
            @if ($reportType === 'approved_events' && $reportData->count() > 0)
                {{-- Approved Events Table --}}
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Event Title</th>
                                <th>Organization</th>
                                <th>Event Date</th>
                                <th>Approved Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData->take(10) as $ticket)
                                <tr>
                                    <td>{{ $ticket->title }}</td>
                                    <td>{{ $ticket->user->studentOrganization->org_name ?? 'No Organization' }}</td>
                                    <td>{{ $ticket->events->first()?->created_at?->format('M d, Y') ?? 'TBD' }}</td>
                                    <td>{{ $ticket->updated_at?->format('M d, Y') ?? 'TBD' }}</td>
                                    <td>
                                        <x-mary-badge value="Approved" class="badge-success text-white" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($reportData->count() > 10)
                        <div class="text-center mt-4 text-sm text-base-content/70">
                            Showing 10 of {{ $reportData->count() }} records. Full report will include all data.
                        </div>
                    @endif
                </div>
            @elseif($reportType === 'rejected_events' && $reportData->count() > 0)
                {{-- Rejected Events Table --}}
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Event Title</th>
                                <th>Organization</th>
                                <th>Submitted Date</th>
                                <th>Rejected Date</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData->take(10) as $ticket)
                                <tr>
                                    <td>{{ $ticket->title }}</td>
                                    <td>{{ $ticket->user->studentOrganization->org_name ?? 'No Organization' }}
                                    </td>
                                    <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                                    <td>{{ $ticket->updated_at?->format('M d, Y') ?? 'TBD' }}</td>
                                    <td>
                                        <x-mary-badge value="Rejected" class="badge-error text-white" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif($reportType === 'org_participation' && $reportData->count() > 0)
                {{-- Organization Participation Chart --}}
                <div class="space-y-4">
                    @foreach ($reportData->take(10) as $org)
                        <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content rounded-full w-10">
                                        <span class="text-sm">{{ substr($org->org_name, 0, 2) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold">{{ $org->org_name }}</div>
                                    <div class="text-sm text-base-content/70">{{ $org->tickets_count }} event
                                        requests
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-32 bg-base-300 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full"
                                        style="width: {{ min(100, ($org->tickets_count / max($reportData->max('tickets_count'), 1)) * 100) }}%">
                                    </div>
                                </div>
                                <span class="text-sm font-medium">{{ $org->tickets_count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($reportType === 'monthly_summary' && is_array($reportData))
                {{-- Monthly Summary Dashboard --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-base-200 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-primary">{{ $reportData['total_tickets'] }}</div>
                        <div class="text-sm text-base-content/70 mt-1">Total Requests</div>
                    </div>
                    <div class="bg-base-200 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-success">{{ $reportData['approved_tickets'] }}</div>
                        <div class="text-sm text-base-content/70 mt-1">Approved</div>
                    </div>
                    <div class="bg-base-200 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-error">{{ $reportData['rejected_tickets'] }}</div>
                        <div class="text-sm text-base-content/70 mt-1">Rejected</div>
                    </div>
                    <div class="bg-base-200 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-warning">{{ $reportData['pending_tickets'] }}</div>
                        <div class="text-sm text-base-content/70 mt-1">Pending</div>
                    </div>
                </div>

                {{-- Approval Rate --}}
                <div class="mt-6 bg-base-200 rounded-lg p-6">
                    <h3 class="font-semibold mb-4">Approval Rate</h3>
                    @php
                        $total = $reportData['total_tickets'];
                        $approved = $reportData['approved_tickets'];
                        $approvalRate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;
                    @endphp
                    <div class="flex items-center gap-4">
                        <div class="flex-1 bg-base-300 rounded-full h-4">
                            <div class="bg-success h-4 rounded-full transition-all duration-300"
                                style="width: {{ $approvalRate }}%"></div>
                        </div>
                        <span class="text-lg font-semibold">{{ $approvalRate }}%</span>
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-12">
                    <x-mary-icon name="o-document-chart-bar" class="w-16 h-16 text-base-content/30 mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-base-content/70">No Data Available</h3>
                    <p class="text-sm text-base-content/50">Select report parameters and generate a report to see
                        data
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Success Message --}}
    @if (session()->has('message'))
        <x-mary-toast type="success" title="Success!" description="{{ session('message') }}" />
    @endif
</div>
