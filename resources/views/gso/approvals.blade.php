<x-layouts.gso-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Approvals Management') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="approvalsManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="stats shadow bg-emerald-50 dark:bg-emerald-900/20">
                    <div class="stat">
                        <div class="stat-figure text-emerald-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="stat-title text-emerald-700 dark:text-emerald-300">Pending Review</div>
                        <div class="stat-value text-emerald-600" x-text="pendingCount"></div>
                        <div class="stat-desc text-emerald-600">Awaiting your approval</div>
                    </div>
                </div>

                <div class="stats shadow bg-blue-50 dark:bg-blue-900/20">
                    <div class="stat">
                        <div class="stat-figure text-blue-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="stat-title text-blue-700 dark:text-blue-300">Today's Approvals</div>
                        <div class="stat-value text-blue-600" x-text="todayApproved"></div>
                        <div class="stat-desc text-blue-600">Approved today</div>
                    </div>
                </div>

                <div class="stats shadow bg-orange-50 dark:bg-orange-900/20">
                    <div class="stat">
                        <div class="stat-figure text-orange-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="stat-title text-orange-700 dark:text-orange-300">Urgent</div>
                        <div class="stat-value text-orange-600" x-text="urgentCount"></div>
                        <div class="stat-desc text-orange-600">High priority items</div>
                    </div>
                </div>
            </div>

            <!-- Filter and Search -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-64">
                            <x-mary-input label="Search Requests" placeholder="Search by event name, organization..."
                                x-model="searchTerm" @input.debounce.300ms="filterRequests()" class="input-emerald">
                                <x-slot:prepend>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </x-slot:prepend>
                            </x-mary-input>
                        </div>

                        <div>
                            <x-mary-select label="Status Filter" :options="[
                                ['id' => 'all', 'name' => 'All Status'],
                                ['id' => 'pending', 'name' => 'Pending'],
                                ['id' => 'approved', 'name' => 'Approved'],
                                ['id' => 'rejected', 'name' => 'Rejected'],
                                ['id' => 'more_info_needed', 'name' => 'More Info Needed'],
                            ]" option-value="id" option-label="name"
                                x-model="statusFilter" @change="filterRequests()" class="select-emerald" />
                        </div>

                        <div>
                            <x-mary-select label="Priority Filter" :options="[
                                ['id' => 'all', 'name' => 'All Priorities'],
                                ['id' => 'high', 'name' => 'High'],
                                ['id' => 'medium', 'name' => 'Medium'],
                                ['id' => 'low', 'name' => 'Low'],
                            ]" option-value="id"
                                option-label="name" x-model="priorityFilter" @change="filterRequests()"
                                class="select-emerald" />
                        </div>

                        <button class="btn btn-emerald" @click="bulkApprove()" x-show="selectedRequests.length > 0">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Bulk Approve (<span x-text="selectedRequests.length"></span>)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Approval Requests -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Approval Requests</h3>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" class="checkbox checkbox-emerald"
                                    @change="toggleSelectAll($event.target.checked)">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Select All</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <template x-for="request in filteredRequests" :key="request.id">
                            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow"
                                :class="request.priority === 'high' ? 'border-red-200 bg-red-50 dark:bg-red-900/10' :
                                    request.priority === 'medium' ?
                                    'border-yellow-200 bg-yellow-50 dark:bg-yellow-900/10' :
                                    'border-gray-200 bg-white dark:bg-gray-800'">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-4 flex-1">
                                        <input type="checkbox" class="checkbox checkbox-emerald mt-1"
                                            :value="request.id"
                                            @change="toggleSelection(request.id, $event.target.checked)">

                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                                    x-text="request.event_name"></h4>
                                                <div class="badge" :class="getStatusClass(request.status)"
                                                    x-text="request.status"></div>
                                                <div class="badge" :class="getPriorityClass(request.priority)"
                                                    x-text="request.priority + ' priority'"></div>
                                            </div>

                                            <div
                                                class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                                <div>
                                                    <strong>Organization:</strong> <span
                                                        x-text="request.organization"></span>
                                                </div>
                                                <div>
                                                    <strong>Event Date:</strong> <span
                                                        x-text="request.event_date"></span>
                                                </div>
                                                <div>
                                                    <strong>Requested:</strong> <span
                                                        x-text="request.request_type"></span>
                                                </div>
                                            </div>

                                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-3"
                                                x-text="request.description"></p>

                                            <div class="flex flex-wrap gap-2 mb-3">
                                                <template x-for="requirement in request.requirements"
                                                    :key="requirement">
                                                    <span class="badge badge-outline badge-sm"
                                                        x-text="requirement"></span>
                                                </template>
                                            </div>

                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Submitted: <span x-text="request.submitted_date"></span> |
                                                Due: <span x-text="request.due_date"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col space-y-2 ml-4">
                                        <template x-if="request.status === 'pending'">
                                            <div class="flex space-x-2">
                                                <button class="btn btn-sm btn-success"
                                                    @click="approveRequest(request)">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Approve
                                                </button>
                                                <button class="btn btn-sm btn-error" @click="rejectRequest(request)">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    Reject
                                                </button>
                                            </div>
                                        </template>

                                        <button class="btn btn-sm btn-outline btn-emerald"
                                            @click="viewDetails(request)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                            Details
                                        </button>

                                        <template x-if="request.status === 'pending'">
                                            <button class="btn btn-sm btn-warning" @click="requestMoreInfo(request)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                    </path>
                                                </svg>
                                                More Info
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <template x-if="filteredRequests.length === 0">
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No approval requests
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No requests match your current
                                filters.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Approval/Rejection Modal -->
        <div x-show="showActionModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter.duration.0ms x-transition:leave.duration.0ms>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="showActionModal = false" x-transition:enter.opacity.duration.0ms
                    x-transition:leave.opacity.duration.0ms>
                </div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                    x-transition:enter.duration.0ms x-transition:enter.scale.origin.bottom
                    x-transition:leave.duration.0ms x-transition:leave.scale.origin.bottom>
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10"
                                :class="actionType === 'approve' ? 'bg-green-100' : 'bg-red-100'">
                                <template x-if="actionType === 'approve'">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </template>
                                <template x-if="actionType === 'reject'">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </template>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100"
                                    x-text="actionType === 'approve' ? 'Approve Request' : 'Reject Request'"></h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400"
                                        x-text="`Are you sure you want to ${actionType} the request for '${selectedRequest?.event_name}'?`">
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <x-mary-textarea label="Comments (Optional)"
                                        placeholder="Add any comments or feedback..." x-model="actionComments"
                                        rows="3" class="textarea-emerald" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="btn w-full sm:w-auto sm:ml-3"
                            :class="actionType === 'approve' ? 'btn-success' : 'btn-error'" @click="confirmAction()">
                            <span x-text="actionType === 'approve' ? 'Approve' : 'Reject'"></span>
                        </button>
                        <button type="button" class="btn btn-ghost mt-3 w-full sm:mt-0 sm:w-auto"
                            @click="showActionModal = false">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function approvalsManager() {
            return {
                showActionModal: false,
                actionType: '',
                selectedRequest: null,
                actionComments: '',
                searchTerm: '',
                statusFilter: 'all',
                priorityFilter: 'all',
                selectedRequests: [],

                allRequests: [{
                        id: 'REQ-001',
                        event_name: 'Leadership Summit 2024',
                        organization: 'Student Council',
                        event_date: 'Nov 15, 2024',
                        request_type: 'Venue Booking',
                        status: 'pending',
                        priority: 'high',
                        description: 'Annual leadership summit requiring main auditorium with full AV setup.',
                        requirements: ['Audio System', 'Projector', 'Seating for 200', 'Stage Lighting'],
                        submitted_date: 'Oct 1, 2024',
                        due_date: 'Oct 15, 2024'
                    },
                    {
                        id: 'REQ-002',
                        event_name: 'Science Fair',
                        organization: 'Science Club',
                        event_date: 'Nov 20, 2024',
                        request_type: 'Equipment',
                        status: 'pending',
                        priority: 'medium',
                        description: 'Equipment needed for student science project presentations.',
                        requirements: ['Display Tables', 'Power Extensions', 'Internet Access'],
                        submitted_date: 'Sep 28, 2024',
                        due_date: 'Oct 20, 2024'
                    },
                    {
                        id: 'REQ-003',
                        event_name: 'Cultural Night',
                        organization: 'Cultural Society',
                        event_date: 'Dec 1, 2024',
                        request_type: 'Logistics',
                        status: 'approved',
                        priority: 'low',
                        description: 'Support for multicultural celebration event.',
                        requirements: ['Security', 'Cleanup Crew', 'Parking Management'],
                        submitted_date: 'Sep 25, 2024',
                        due_date: 'Oct 25, 2024'
                    }
                ],
                filteredRequests: [],

                get pendingCount() {
                    return this.allRequests.filter(r => r.status === 'pending').length;
                },

                get todayApproved() {
                    // In a real app, this would filter by today's date
                    return this.allRequests.filter(r => r.status === 'approved').length;
                },

                get urgentCount() {
                    return this.allRequests.filter(r => r.priority === 'high' && r.status === 'pending').length;
                },

                init() {
                    this.filteredRequests = this.allRequests;
                },

                filterRequests() {
                    this.filteredRequests = this.allRequests.filter(request => {
                        const matchesSearch = !this.searchTerm ||
                            request.event_name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            request.organization.toLowerCase().includes(this.searchTerm.toLowerCase());

                        const matchesStatus = this.statusFilter === 'all' || request.status === this.statusFilter;
                        const matchesPriority = this.priorityFilter === 'all' || request.priority === this
                            .priorityFilter;

                        return matchesSearch && matchesStatus && matchesPriority;
                    });
                },

                toggleSelection(requestId, checked) {
                    if (checked) {
                        this.selectedRequests.push(requestId);
                    } else {
                        this.selectedRequests = this.selectedRequests.filter(id => id !== requestId);
                    }
                },

                toggleSelectAll(checked) {
                    if (checked) {
                        this.selectedRequests = this.filteredRequests.map(r => r.id);
                    } else {
                        this.selectedRequests = [];
                    }
                },

                approveRequest(request) {
                    this.selectedRequest = request;
                    this.actionType = 'approve';
                    this.actionComments = '';
                    this.showActionModal = true;
                },

                rejectRequest(request) {
                    this.selectedRequest = request;
                    this.actionType = 'reject';
                    this.actionComments = '';
                    this.showActionModal = true;
                },

                confirmAction() {
                    if (this.actionType === 'approve') {
                        this.selectedRequest.status = 'approved';
                    } else {
                        this.selectedRequest.status = 'rejected';
                    }

                    this.showActionModal = false;
                    this.filterRequests();

                    // Here you would make an API call
                    alert(`Request ${this.selectedRequest.id} ${this.actionType}d successfully!`);
                },

                requestMoreInfo(request) {
                    request.status = 'more_info_needed';
                    this.filterRequests();
                    // Here you would typically open a communication modal
                    alert(`More information requested for ${request.id}`);
                },

                viewDetails(request) {
                    // Here you would open a detailed view modal
                    alert(`Viewing details for ${request.id}`);
                },

                bulkApprove() {
                    const selectedItems = this.allRequests.filter(r => this.selectedRequests.includes(r.id));
                    selectedItems.forEach(request => {
                        if (request.status === 'pending') {
                            request.status = 'approved';
                        }
                    });

                    this.selectedRequests = [];
                    this.filterRequests();
                    alert(`${selectedItems.length} requests approved successfully!`);
                },

                getStatusClass(status) {
                    const classes = {
                        'pending': 'badge-warning',
                        'approved': 'badge-success',
                        'rejected': 'badge-error',
                        'more_info_needed': 'badge-info'
                    };
                    return classes[status] || 'badge-ghost';
                },

                getPriorityClass(priority) {
                    const classes = {
                        'high': 'badge-error',
                        'medium': 'badge-warning',
                        'low': 'badge-success'
                    };
                    return classes[priority] || 'badge-ghost';
                }
            }
        }
    </script>
</x-layouts.gso-layout>
