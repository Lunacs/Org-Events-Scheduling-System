<x-slot name="header">
    <h2 class="font-semibold text-xl font-heading text-base-content leading-tight">
        {{ __('GSO Reports & Analytics') }}
    </h2>
</x-slot>

<div class="py-12" x-data="gsoReports()" x-ref="reportsContainer">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Report Controls -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex flex-wrap justify-between items-end gap-4">
                    <div class="flex gap-4">
                        <div x-show="timePeriod === 'custom'" x-cloak>
                            <x-mary-input label="Start Date" type="date" x-model="customDateRange.start"
                                class="input-emerald" @change="applyFilters()" />
                        </div>

                        <div x-show="timePeriod === 'custom'" x-cloak>
                            <x-mary-input label="End Date" type="date" x-model="customDateRange.end"
                                class="input-emerald" @change="applyFilters()" />
                        </div>
                    </div>

                    <div class="flex space-x-2">
                        <button class="btn btn-emerald" @click.prevent="exportReport()">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Export PDF
                        </button>
                        <button class="btn btn-outline btn-emerald" @click.prevent="exportCSV()">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stats shadow bg-emerald-50 dark:bg-emerald-900/20">
                <div class="stat">
                    <div class="stat-figure text-emerald-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-title text-emerald-700 dark:text-emerald-300">Total Approved</div>
                    <div class="stat-value text-emerald-600" x-text="stats.totalApproved"></div>
                    <div class="stat-desc text-emerald-600" x-text="timePeriodLabel"></div>
                </div>
            </div>

            <div class="stats shadow bg-red-50 dark:bg-red-900/20">
                <div class="stat">
                    <div class="stat-figure text-red-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-title text-red-700 dark:text-red-300">Total Rejected</div>
                    <div class="stat-value text-red-600" x-text="stats.totalRejected"></div>
                    <div class="stat-desc text-red-600" x-text="timePeriodLabel"></div>
                </div>
            </div>

            <div class="stats shadow bg-purple-50 dark:bg-purple-900/20">
                <div class="stat">
                    <div class="stat-figure text-purple-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="stat-title text-purple-700 dark:text-purple-300">Approval Rate</div>
                    <div class="stat-value text-purple-600" x-text="stats.approvalRate + '%'"></div>
                    <div class="stat-desc text-purple-600" x-text="timePeriodLabel"></div>
                </div>
            </div>
        </div>

        <!-- Main Report Content -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Chart Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                        <h3 class="text-lg font-semibold font-heading text-base-content" x-text="chartTitle"></h3>
                        <x-mary-select x-model="selectedChart" :options="[
                            ['id' => 'request_types', 'name' => 'Request Types'],
                            ['id' => 'approvals', 'name' => 'Approvals'],
                        ]" option-label="name" option-value="id"
                            @change="refreshChart({ forceReinit: true })"
                            class="select-emerald w-full sm:w-60 text-center" input-class="text-center" />
                    </div>

                    <div
                        class="relative h-64 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-lg overflow-hidden">
                        <div class="h-full w-full p-3 box-border">
                            <canvas x-ref="approvalChart" class="w-full h-full transition-opacity duration-200"
                                :class="{ 'opacity-0 pointer-events-none': !hasStatusData }"></canvas>
                        </div>

                        <div x-show="!hasStatusData"
                            class="absolute inset-0 flex flex-col items-center justify-center text-center space-y-2 px-6">
                            <svg class="mx-auto h-12 w-12 text-emerald-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                            <p class="text-emerald-600 dark:text-emerald-400 font-medium">Interactive Chart</p>
                            <p class="text-sm text-emerald-500 dark:text-emerald-500">Waiting for approval data
                                to display</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Type Breakdown -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold font-heading text-base-content mb-4">Request Type Breakdown
                    </h3>
                    <div class="space-y-4">
                        <template x-for="item in breakdown" :key="item.type">
                            <div class="flex items-center justify-between p-3 bg-base-200 dark:bg-base-700 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 rounded" :style="`background-color: ${item.color}`"></div>
                                    <span class="text-sm font-medium text-base-content" x-text="item.type"></span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-base-content" x-text="item.count"></div>
                                    <div class="text-xs text-base-content/70" x-text="item.percentage + '%'"></div>
                                </div>
                            </div>
                        </template>
                        <div x-show="breakdown.length === 0" class="text-center py-8 text-base-content/50">
                            <p>No data available</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Report Table -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <h3 class="text-lg font-semibold font-heading text-base-content" x-text="tableTitle"></h3>
                    <div class="relative">
                        <input type="text" placeholder="Search records..." x-model="searchTerm"
                            @input.debounce.300ms="filterRecords()"
                            class="input input-bordered input-emerald input-sm w-64 pl-10">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-base-content/40" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                <th class="text-emerald-700 dark:text-emerald-300">Date</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Ticket ID</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Event</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Organization</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Request Type</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Decision</th>
                                <th class="text-emerald-700 dark:text-emerald-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="record in paginatedRecords" :key="record.id">
                                <tr class="hover:bg-emerald-50 dark:hover:bg-emerald-900/10">
                                    <td x-text="record.date"></td>
                                    <td>
                                        <span class="font-mono text-sm" x-text="record.ticketId"></span>
                                    </td>
                                    <td x-text="record.eventName"></td>
                                    <td x-text="record.organization"></td>
                                    <td>
                                        <span
                                            :class="'badge border-none badge-lg h-auto flex-wrap whitespace-normal leading-tight px-3 py-1 max-w-48 text-left text-sm font-medium shadow-sm ' +
                                            getRequestTypeClass(record.requestType)"
                                            x-text="record.requestType || 'N/A'"></span>
                                    </td>
                                    <td>
                                        <div class="badge" :class="getDecisionClass(record.decision)"
                                            x-text="record.decision"></div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-ghost" @click="goToDetails(record)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="paginatedRecords.length === 0">
                                <td colspan="7" class="text-center py-8 text-base-content/50">
                                    No records found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
                    <div class="text-sm font-medium text-base-content/70">
                        Showing <span class="font-semibold text-base-content"
                            x-text="Math.min((currentPage - 1) * perPage + 1, filteredRecords.length)"></span> to <span
                            class="font-semibold text-base-content"
                            x-text="Math.min(currentPage * perPage, filteredRecords.length)"></span> of <span
                            class="font-semibold text-base-content" x-text="filteredRecords.length"></span> records
                    </div>
                    <div class="btn-group" x-show="totalPages > 1">
                        <button class="btn btn-sm btn-emerald" @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1" :class="{ 'btn-disabled opacity-50': currentPage === 1 }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Previous
                        </button>
                        <template x-for="page in visiblePages" :key="page">
                            <button class="btn btn-sm"
                                :class="{ 'btn-emerald btn-active': page === currentPage, 'btn-ghost': page !== currentPage }"
                                @click="goToPage(page)" x-text="page"></button>
                        </template>
                        <button class="btn btn-sm btn-emerald" @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            :class="{ 'btn-disabled opacity-50': currentPage === totalPages }">
                            Next
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const gsoReportSeed = @json($reportSeed);

    function gsoReports() {
        return {
            searchTerm: '',
            selectedChart: 'request_types',
            timePeriod: 'this_month',
            customDateRange: {
                start: '',
                end: ''
            },

            stats: {
                totalApproved: 0,
                totalRejected: 0,
                approvalRate: 0,
                avgResponseTime: 0
            },

            breakdown: [],

            statusSummary: {
                approved: 0,
                rejected: 0
            },
            statusChart: null,
            chartColors: {
                approved: '#10b981',
                rejected: '#ef4444'
            },
            chartWaitHandle: null,
            chartReadyQueue: [],
            themeObserver: null,
            themeChangeDebounce: null,

            requestTypeClassDefaults: {
                'venue booking': 'badge-primary text-white',
                'venue': 'badge-primary text-white',
                'equipment': 'badge-info text-white',
                'logistics': 'badge-secondary text-white',
                'catering': 'badge-accent text-white'
            },
            requestTypeClassPalette: [
                'badge-success text-white',
                'badge-warning text-white',
                'badge-error text-white',
                'badge-neutral text-white',
                'badge-info text-white',
                'badge-secondary text-white',
                'badge-accent text-white',
                'badge-primary text-white'
            ],
            requestTypeClassMap: {},
            requestTypePaletteIndex: 0,

            records: (gsoReportSeed.records ?? []).map(record => ({
                ...record,
                decidedAt: record.decided_at ? new Date(record.decided_at) : null,
            })),
            currentDataset: [],
            filteredRecords: [],
            currentPage: 1,
            perPage: 10,

            get totalPages() {
                return Math.ceil(this.filteredRecords.length / this.perPage);
            },

            get visiblePages() {
                const total = this.totalPages;
                const current = this.currentPage;
                const maxVisible = 5;
                const pages = [];

                if (total <= maxVisible) {
                    for (let i = 1; i <= total; i++) {
                        pages.push(i);
                    }
                } else {
                    if (current <= 3) {
                        for (let i = 1; i <= 5; i++) {
                            pages.push(i);
                        }
                    } else if (current >= total - 2) {
                        for (let i = total - 4; i <= total; i++) {
                            pages.push(i);
                        }
                    } else {
                        for (let i = current - 2; i <= current + 2; i++) {
                            pages.push(i);
                        }
                    }
                }

                return pages;
            },

            get paginatedRecords() {
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;
                return this.filteredRecords.slice(start, end);
            },

            get timePeriodLabel() {
                const labels = {
                    'this_month': 'This Month',
                    'last_month': 'Last Month',
                    'this_quarter': 'This Quarter',
                    'this_year': 'This Year',
                };
                return labels[this.timePeriod] || 'Custom Period';
            },

            get chartTitle() {
                const titles = {
                    'request_types': 'Request Types Distribution',
                    'approvals': 'Approval Status'
                };
                return titles[this.selectedChart] || 'Report Chart';
            },

            get tableTitle() {
                const titles = {
                    'request_types': 'Request Type Analysis',
                    'approvals': 'Approval Status Report',
                    'workload': 'Workload Analysis',
                    'trends': 'Trend Data'
                };
                return titles[this.selectedChart] || 'Detailed Report';
            },

            get statusDataset() {
                if (this.selectedChart === 'approvals') {
                    return [
                        Number(this.statusSummary.approved || 0),
                        Number(this.statusSummary.rejected || 0)
                    ];
                }

                if (this.selectedChart === 'request_types') {
                    return (this.breakdown || []).map(item => Number(item.count) || 0);
                }

                return [];
            },

            get statusLabels() {
                if (this.selectedChart === 'approvals') {
                    return ['Approved', 'Rejected'];
                }

                if (this.selectedChart === 'request_types') {
                    return (this.breakdown || []).map(item => item.type || 'Unspecified');
                }

                return [];
            },

            get statusColors() {
                if (this.selectedChart === 'approvals') {
                    return [
                        this.chartColors.approved,
                        this.chartColors.rejected
                    ];
                }

                if (this.selectedChart === 'request_types') {
                    return (this.breakdown || []).map(item => item.color || '#10b981');
                }

                return [];
            },

            get hasStatusData() {
                return this.statusDataset.some(value => Number(value) > 0);
            },

            init() {
                this.bootstrapRequestTypeClasses();
                this.applyFilters();
                this.handleThemeChange();
                this.initDownloadListener();
            },

            generateReport() {
                if (this.timePeriod !== 'custom') {
                    this.customDateRange.start = '';
                    this.customDateRange.end = '';
                }
                this.applyFilters();
            },

            filterRecords() {
                this.filteredRecords = this.applySearch(this.currentDataset);
                this.currentPage = 1;
            },

            goToPage(page) {
                if (page >= 1 && page <= this.totalPages) {
                    this.currentPage = page;
                }
            },

            applyFilters() {
                const {
                    start,
                    end
                } = this.resolveRange(this.timePeriod);

                this.currentDataset = this.records.filter(record => {
                    if (!record.decidedAt) {
                        return false;
                    }

                    if (!start || !end) {
                        return true;
                    }

                    return this.isWithinRange(record.decidedAt, start, end);
                });

                this.filteredRecords = this.applySearch(this.currentDataset);
                this.currentPage = 1;
                this.updateStats(this.currentDataset);
                this.updateBreakdown(this.currentDataset);
            },

            applySearch(dataset) {
                if (!this.searchTerm) {
                    return dataset;
                }

                const term = this.searchTerm.toLowerCase();

                return dataset.filter(record =>
                    (record.eventName ?? '').toLowerCase().includes(term) ||
                    (record.organization ?? '').toLowerCase().includes(term) ||
                    (record.ticketId ?? '').toLowerCase().includes(term)
                );
            },

            updateStats(dataset) {
                const approved = dataset.filter(r => (r.decision || '').toLowerCase() === 'approved').length;
                const rejected = dataset.filter(r => (r.decision || '').toLowerCase() === 'rejected').length;
                const total = approved + rejected;

                const avgResponse = dataset.length ?
                    dataset.reduce((sum, record) => sum + Number(record.responseTime ?? 0), 0) / dataset.length :
                    0;

                this.stats = {
                    totalApproved: approved,
                    totalRejected: rejected,
                    approvalRate: total > 0 ? Math.round((approved / total) * 100) : 0,
                    avgResponseTime: Number(avgResponse.toFixed(1))
                };

                this.statusSummary = {
                    approved,
                    rejected
                };
            },

            updateBreakdown(dataset) {
                if (!dataset.length) {
                    this.breakdown = [];
                    this.refreshChart();
                    return;
                }

                const colorPalette = ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#6366f1', '#14b8a6'];
                const counts = dataset.reduce((acc, record) => {
                    const type = record.requestType || 'Unspecified';
                    acc[type] = (acc[type] || 0) + 1;
                    return acc;
                }, {});

                const total = dataset.length;
                let colorIndex = 0;

                this.breakdown = Object.entries(counts).map(([type, count]) => {
                    const color = colorPalette[colorIndex % colorPalette.length];
                    colorIndex += 1;

                    return {
                        type,
                        count,
                        percentage: Number(((count / total) * 100).toFixed(1)),
                        color,
                    };
                });

                this.refreshChart();
            },

            resolveRange(period) {
                const today = new Date();
                const dayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                let start = null;
                let end = new Date(dayStart);
                end.setHours(23, 59, 59, 999);

                switch (period) {
                    case 'this_week': {
                        start = new Date(dayStart);
                        start.setDate(start.getDate() - start.getDay());
                        end = new Date(start);
                        end.setDate(start.getDate() + 6);
                        end.setHours(23, 59, 59, 999);
                        break;
                    }
                    case 'this_month': {
                        start = new Date(today.getFullYear(), today.getMonth(), 1);
                        end = new Date(today.getFullYear(), today.getMonth() + 1, 0, 23, 59, 59, 999);
                        break;
                    }
                    case 'last_month': {
                        start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        end = new Date(today.getFullYear(), today.getMonth(), 0, 23, 59, 59, 999);
                        break;
                    }
                    case 'this_quarter': {
                        const quarterStartMonth = Math.floor(today.getMonth() / 3) * 3;
                        start = new Date(today.getFullYear(), quarterStartMonth, 1);
                        end = new Date(today.getFullYear(), quarterStartMonth + 3, 0, 23, 59, 59, 999);
                        break;
                    }
                    case 'this_year': {
                        start = new Date(today.getFullYear(), 0, 1);
                        end = new Date(today.getFullYear(), 11, 31, 23, 59, 59, 999);
                        break;
                    }
                    case 'custom': {
                        if (this.customDateRange.start && this.customDateRange.end) {
                            start = new Date(this.customDateRange.start + 'T00:00:00');
                            end = new Date(this.customDateRange.end + 'T23:59:59');
                        }
                        break;
                    }
                    default: {
                        start = new Date(today.getFullYear(), today.getMonth(), 1);
                        end = new Date(today.getFullYear(), today.getMonth() + 1, 0, 23, 59, 59, 999);
                    }
                }

                return {
                    start,
                    end
                };
            },

            isWithinRange(date, start, end) {
                if (!start || !end) {
                    return true;
                }

                return date >= start && date <= end;
            },

            goToDetails(record) {
                if (!record.ticketDetailsUrl) {
                    return;
                }

                window.location.href = record.ticketDetailsUrl;
            },

            exportReport() {
                this.$wire.export(
                    'pdf',
                    this.timePeriod,
                    this.customDateRange.start || null,
                    this.customDateRange.end || null,
                    this.searchTerm || null
                );
            },

            exportCSV() {
                this.$wire.export(
                    'csv',
                    this.timePeriod,
                    this.customDateRange.start || null,
                    this.customDateRange.end || null,
                    this.searchTerm || null
                );
            },

            initDownloadListener() {
                window.addEventListener('gso:export-download', (ev) => {
                    try {
                        const url = ev?.detail?.url;
                        if (url) {
                            window.location.href = url;
                        }
                    } catch (err) {
                        // ignore failures silently
                    }
                });
            },

            refreshChart(options = {}) {
                this.waitForChart(() => {
                    if (!this.hasStatusData) {
                        this.destroyChart();
                        return;
                    }

                    if (options.forceReinit && this.statusChart) {
                        this.destroyChart();
                    }

                    if (!this.statusChart || options.forceReinit) {
                        this.initChart();
                    } else {
                        this.updateChartData();
                    }
                });
            },

            waitForChart(callback) {
                if (window.Chart) {
                    this.$nextTick(callback);
                    return;
                }

                this.chartReadyQueue.push(callback);

                if (this.chartWaitHandle) {
                    return;
                }

                this.chartWaitHandle = setInterval(() => {
                    if (window.Chart) {
                        clearInterval(this.chartWaitHandle);
                        this.chartWaitHandle = null;

                        const pendingCallbacks = [...this.chartReadyQueue];
                        this.chartReadyQueue = [];

                        pendingCallbacks.forEach(cb => this.$nextTick(cb));
                    }
                }, 100);
            },

            initChart() {
                const canvas = this.$refs.approvalChart;

                if (!canvas || !window.Chart) {
                    return;
                }

                const context = canvas.getContext('2d');

                if (!context) {
                    return;
                }

                this.statusChart = new Chart(context, {
                    type: 'doughnut',
                    data: {
                        labels: this.statusLabels,
                        datasets: [{
                            data: this.statusDataset,
                            backgroundColor: this.statusColors,
                            hoverBackgroundColor: this.statusColors,
                            borderWidth: 0,
                            hoverOffset: 6,
                            cutout: '55%',
                        }, ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: this.chartLegendColor(),
                                    font: {
                                        size: 12
                                    },
                                    usePointStyle: true,
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) :
                                            0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                },
                            },
                        },
                    },
                });
            },

            updateChartData() {
                if (!this.statusChart) {
                    return;
                }

                this.statusChart.data.labels = this.statusLabels;
                this.statusChart.data.datasets[0].data = this.statusDataset;
                this.statusChart.data.datasets[0].backgroundColor = this.statusColors;
                this.statusChart.data.datasets[0].hoverBackgroundColor = this.statusColors;

                if (this.statusChart.options?.plugins?.legend?.labels) {
                    this.statusChart.options.plugins.legend.labels.color = this.chartLegendColor();
                }

                this.statusChart.update();
            },

            destroyChart() {
                if (this.statusChart) {
                    this.statusChart.destroy();
                    this.statusChart = null;
                }

                if (this.$refs.approvalChart) {
                    const context = this.$refs.approvalChart.getContext('2d');
                    context?.clearRect(0, 0, this.$refs.approvalChart.width, this.$refs.approvalChart.height);
                }
            },

            isDarkMode() {
                const root = document.documentElement;
                const body = document.body;
                const themeAttr = ((root.getAttribute('data-theme') || body.getAttribute('data-theme') || '')
                    .toLowerCase());
                const themeClass = `${root.className} ${body.className}`.toLowerCase();

                if (themeClass.includes('dark') || themeAttr.includes('dark')) {
                    return true;
                }

                if (this.$refs?.reportsContainer) {
                    const container = this.$refs.reportsContainer;
                    const containerThemeAttr = (container.getAttribute('data-theme') || '').toLowerCase();
                    const containerClass = (container.className || '').toLowerCase();

                    if (containerThemeAttr.includes('dark') || containerClass.includes('dark')) {
                        return true;
                    }
                }

                return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            },

            handleThemeChange() {
                const triggerRefresh = () => {
                    if (this.themeChangeDebounce) {
                        clearTimeout(this.themeChangeDebounce);
                    }

                    this.themeChangeDebounce = setTimeout(() => {
                        this.refreshChart({
                            forceReinit: true
                        });
                    }, 60);
                };

                if (window.matchMedia) {
                    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
                    const listener = triggerRefresh;

                    if (mediaQuery.addEventListener) {
                        mediaQuery.addEventListener('change', listener);
                    } else if (mediaQuery.addListener) {
                        mediaQuery.addListener(listener);
                    }
                }

                document.addEventListener('mary-theme-changed', triggerRefresh);

                if (window.MutationObserver) {
                    if (this.themeObserver) {
                        this.themeObserver.disconnect();
                    }

                    const observer = new MutationObserver(triggerRefresh);
                    this.themeObserver = observer;

                    observer.observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['class', 'data-theme']
                    });

                    observer.observe(document.body, {
                        attributes: true,
                        attributeFilter: ['class', 'data-theme']
                    });

                    if (this.$refs?.reportsContainer) {
                        observer.observe(this.$refs.reportsContainer, {
                            attributes: true,
                            attributeFilter: ['class', 'data-theme']
                        });
                    }
                }
            },

            bootstrapRequestTypeClasses() {
                this.requestTypeClassMap = {
                    ...this.requestTypeClassDefaults
                };
                this.requestTypePaletteIndex = 0;

                (this.records || []).forEach(record => {
                    const key = (record.requestType || '').toLowerCase();
                    if (!key) {
                        return;
                    }

                    if (!Object.prototype.hasOwnProperty.call(this.requestTypeClassMap, key)) {
                        this.requestTypeClassMap[key] = this.requestTypeClassPalette[this
                            .requestTypePaletteIndex % this.requestTypeClassPalette.length];
                        this.requestTypePaletteIndex += 1;
                    }
                });
            },

            chartLegendColor() {
                const container = this.$refs?.reportsContainer ?? document.body;

                if (container && window.getComputedStyle) {
                    const computed = window.getComputedStyle(container);
                    if (computed?.color) {
                        return computed.color;
                    }
                }

                return this.isDarkMode() ? '#f3f4f6' : '#1f2937';
            },

            getRequestTypeClass(typeLabel) {
                if (!typeLabel) {
                    return 'badge-ghost';
                }

                const key = typeLabel.toLowerCase();

                if (!Object.prototype.hasOwnProperty.call(this.requestTypeClassMap, key)) {
                    this.requestTypeClassMap[key] = this.requestTypeClassPalette[this.requestTypePaletteIndex % this
                        .requestTypeClassPalette.length];
                    this.requestTypePaletteIndex += 1;
                }

                return this.requestTypeClassMap[key];
            },

            getDecisionClass(decision) {
                const classes = {
                    'Approved': 'badge-success text-white',
                    'Rejected': 'badge-error text-white',
                    'Pending': 'badge-warning text-white'
                };
                return classes[decision] || 'badge-ghost';
            }
        }
    }
</script>
