<x-layouts.gso-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('GSO Reports & Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="gsoReports()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Report Controls -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-wrap justify-between items-end gap-4">
                        <div class="flex items-end space-x-4">

                            <div>
                                <x-mary-select label="Time Period" :options="[
                                    ['id' => 'this_week', 'name' => 'This Week'],
                                    ['id' => 'this_month', 'name' => 'This Month'],
                                    ['id' => 'last_month', 'name' => 'Last Month'],
                                    ['id' => 'this_quarter', 'name' => 'This Quarter'],
                                    ['id' => 'this_year', 'name' => 'This Year'],
                                    ['id' => 'custom', 'name' => 'Custom Range'],
                                ]" option-value="id"
                                    option-label="name" x-model="timePeriod" @change="generateReport()"
                                    class="select-emerald" />
                            </div>

                            <div x-show="timePeriod === 'custom'" x-cloak>
                                <x-mary-input label="Start Date" type="date" x-model="customDateRange.start"
                                    class="input-emerald" />
                            </div>

                            <div x-show="timePeriod === 'custom'" x-cloak>
                                <x-mary-input label="End Date" type="date" x-model="customDateRange.end"
                                    class="input-emerald" />
                            </div>
                        </div>

                        <div class="flex space-x-2">
                            <button class="btn btn-emerald" @click="exportReport()">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Export PDF
                            </button>
                            <button class="btn btn-outline btn-emerald" @click="exportCSV()">
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4" x-text="chartTitle">
                        </h3>

                        <!-- Placeholder for Chart - In real app, you'd use Chart.js or similar -->
                        <div
                            class="h-64 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-lg flex items-center justify-center">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-emerald-400 mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                <p class="text-emerald-600 dark:text-emerald-400 font-medium">Interactive Chart</p>
                                <p class="text-sm text-emerald-500 dark:text-emerald-500">Chart visualization would
                                    appear here</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Breakdown Section -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Request Type Breakdown
                        </h3>

                        <div class="space-y-4">
                            <template x-for="item in breakdown" :key="item.type">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-4 h-4 rounded" :style="`background-color: ${item.color}`"></div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                            x-text="item.type"></span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                            x-text="item.count"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400"
                                            x-text="item.percentage + '%'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Report Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="tableTitle"></h3>
                        <div class="flex space-x-2">
                            <x-mary-input placeholder="Search records..." x-model="searchTerm"
                                @input.debounce.300ms="filterRecords()" class="input-emerald input-sm">
                                <x-slot:prepend>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </x-slot:prepend>
                            </x-mary-input>
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
                                    <th class="text-emerald-700 dark:text-emerald-300">Response Time</th>
                                    <th class="text-emerald-700 dark:text-emerald-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="record in filteredRecords" :key="record.id">
                                    <tr class="hover:bg-emerald-50 dark:hover:bg-emerald-900/10">
                                        <td x-text="record.date"></td>
                                        <td>
                                            <span class="font-mono text-sm" x-text="record.ticketId"></span>
                                        </td>
                                        <td x-text="record.eventName"></td>
                                        <td x-text="record.organization"></td>
                                        <td>
                                            <div class="badge badge-outline" x-text="record.requestType"></div>
                                        </td>
                                        <td>
                                            <div class="badge" :class="getDecisionClass(record.decision)"
                                                x-text="record.decision"></div>
                                        </td>
                                        <td>
                                            <span x-text="record.responseTime + ' hrs'"></span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-ghost" @click="viewDetails(record)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex justify-between items-center mt-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Showing <span x-text="filteredRecords.length"></span> of <span
                                x-text="allRecords.length"></span> records
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-sm">«</button>
                            <button class="btn btn-sm btn-active">1</button>
                            <button class="btn btn-sm">2</button>
                            <button class="btn btn-sm">3</button>
                            <button class="btn btn-sm">»</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics (shown when performance report is selected) -->
            <div x-show="selectedReport === 'performance'" x-cloak
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Performance Metrics</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="radial-progress text-emerald-600 border-emerald-200" style="--value:85;"
                                role="progressbar">85%</div>
                            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">On-time Completion</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Meeting deadlines</p>
                        </div>

                        <div class="text-center">
                            <div class="radial-progress text-blue-600 border-blue-200" style="--value:92;"
                                role="progressbar">92%</div>
                            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Accuracy Rate</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Correct decisions</p>
                        </div>

                        <div class="text-center">
                            <div class="radial-progress text-purple-600 border-purple-200" style="--value:78;"
                                role="progressbar">78%</div>
                            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Satisfaction Score</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">User feedback</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Record Details Modal -->
        <div x-show="showDetailsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter.duration.0ms x-transition:leave.duration.0ms>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="showDetailsModal = false" x-transition:enter.opacity.duration.0ms
                    x-transition:leave.opacity.duration.0ms>
                </div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                    x-transition:enter.duration.0ms x-transition:enter.scale.origin.bottom
                    x-transition:leave.duration.0ms x-transition:leave.scale.origin.bottom>
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                x-text="`Record Details - ${selectedRecord?.ticketId}`"></h3>
                            <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <template x-if="selectedRecord">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div><strong>Date:</strong> <span x-text="selectedRecord.date"></span></div>
                                <div><strong>Response Time:</strong> <span
                                        x-text="selectedRecord.responseTime + ' hours'"></span></div>
                                <div><strong>Event:</strong> <span x-text="selectedRecord.eventName"></span></div>
                                <div><strong>Organization:</strong> <span x-text="selectedRecord.organization"></span>
                                </div>
                                <div><strong>Request Type:</strong> <span x-text="selectedRecord.requestType"></span>
                                </div>
                                <div><strong>Decision:</strong> <span x-text="selectedRecord.decision"></span></div>
                                <div class="col-span-2"><strong>Comments:</strong> <span
                                        x-text="selectedRecord.comments || 'No comments'"></span></div>
                            </div>
                        </template>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="btn btn-emerald" @click="showDetailsModal = false">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function gsoReports() {
            return {
                selectedReport: 'approvals',
                timePeriod: 'this_month',
                customDateRange: {
                    start: '',
                    end: ''
                },
                searchTerm: '',
                showDetailsModal: false,
                selectedRecord: null,

                stats: {
                    totalApproved: 45,
                    totalRejected: 8,
                    avgResponseTime: 6.5,
                    approvalRate: 85
                },

                breakdown: [{
                        type: 'Venue Booking',
                        count: 25,
                        percentage: 47,
                        color: '#10b981'
                    },
                    {
                        type: 'Equipment',
                        count: 15,
                        percentage: 28,
                        color: '#3b82f6'
                    },
                    {
                        type: 'Logistics',
                        count: 10,
                        percentage: 19,
                        color: '#8b5cf6'
                    },
                    {
                        type: 'Catering',
                        count: 3,
                        percentage: 6,
                        color: '#f59e0b'
                    }
                ],

                allRecords: [{
                        id: 1,
                        date: '2024-10-28',
                        ticketId: 'TKT-001',
                        eventName: 'Leadership Summit',
                        organization: 'Student Council',
                        requestType: 'Venue Booking',
                        decision: 'Approved',
                        responseTime: 4,
                        comments: 'Approved with additional security requirements'
                    },
                    {
                        id: 2,
                        date: '2024-10-27',
                        ticketId: 'TKT-002',
                        eventName: 'Science Fair',
                        organization: 'Science Club',
                        requestType: 'Equipment',
                        decision: 'Approved',
                        responseTime: 8,
                        comments: 'Equipment approved as requested'
                    },
                    {
                        id: 3,
                        date: '2024-10-26',
                        ticketId: 'TKT-003',
                        eventName: 'Music Concert',
                        organization: 'Music Society',
                        requestType: 'Logistics',
                        decision: 'Rejected',
                        responseTime: 2,
                        comments: 'Insufficient notice period'
                    }
                ],
                filteredRecords: [],

                get timePeriodLabel() {
                    const labels = {
                        'this_week': 'This Week',
                        'this_month': 'This Month',
                        'last_month': 'Last Month',
                        'this_quarter': 'This Quarter',
                        'this_year': 'This Year',
                        'custom': 'Custom Range'
                    };
                    return labels[this.timePeriod] || 'This Month';
                },

                get chartTitle() {
                    const titles = {
                        'approvals': 'Approval Trends',
                        'performance': 'Performance Metrics',
                        'workload': 'Workload Distribution',
                        'trends': 'Request Trends'
                    };
                    return titles[this.selectedReport] || 'Report Chart';
                },

                get tableTitle() {
                    const titles = {
                        'approvals': 'Approval History',
                        'performance': 'Performance Records',
                        'workload': 'Workload Analysis',
                        'trends': 'Trend Data'
                    };
                    return titles[this.selectedReport] || 'Report Data';
                },

                init() {
                    this.filteredRecords = this.allRecords;
                },

                generateReport() {
                    // Here you would make API calls to fetch report data
                    console.log(`Generating ${this.selectedReport} report for ${this.timePeriod}`);
                    this.filterRecords();
                },

                filterRecords() {
                    if (!this.searchTerm) {
                        this.filteredRecords = this.allRecords;
                        return;
                    }

                    this.filteredRecords = this.allRecords.filter(record =>
                        record.eventName.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                        record.organization.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                        record.ticketId.toLowerCase().includes(this.searchTerm.toLowerCase())
                    );
                },

                viewDetails(record) {
                    this.selectedRecord = record;
                    this.showDetailsModal = true;
                },

                exportReport() {
                    alert('PDF export functionality would be implemented here');
                },

                exportCSV() {
                    alert('CSV export functionality would be implemented here');
                },

                getDecisionClass(decision) {
                    const classes = {
                        'Approved': 'badge-success',
                        'Rejected': 'badge-error',
                        'Pending': 'badge-warning'
                    };
                    return classes[decision] || 'badge-ghost';
                }
            }
        }
    </script>
</x-layouts.gso-layout>
