<x-layouts.gso-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Ticket Review') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="ticketReview()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Filter Tickets</h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <x-mary-select label="Request Type" :options="[
                                ['id' => '', 'name' => 'All Types'],
                                ['id' => 'venue', 'name' => 'Venue Booking'],
                                ['id' => 'equipment', 'name' => 'Equipment'],
                                ['id' => 'logistics', 'name' => 'Logistics'],
                                ['id' => 'catering', 'name' => 'Catering'],
                            ]" option-value="id" option-label="name"
                                x-model="filters.type" @change="applyFilters()" class="select-emerald" />
                        </div>

                        <div>
                            <x-mary-select label="Priority" :options="[
                                ['id' => '', 'name' => 'All Priorities'],
                                ['id' => 'high', 'name' => 'High'],
                                ['id' => 'medium', 'name' => 'Medium'],
                                ['id' => 'low', 'name' => 'Low'],
                            ]" option-value="id" option-label="name"
                                x-model="filters.priority" @change="applyFilters()" class="select-emerald" />
                        </div>

                        <div>
                            <x-mary-select label="Status" :options="[
                                ['id' => '', 'name' => 'All Status'],
                                ['id' => 'pending', 'name' => 'Pending'],
                                ['id' => 'under_review', 'name' => 'Under Review'],
                                ['id' => 'approved', 'name' => 'Approved'],
                                ['id' => 'rejected', 'name' => 'Rejected'],
                            ]" option-value="id" option-label="name"
                                x-model="filters.status" @change="applyFilters()" class="select-emerald" />
                        </div>

                        <div>
                            <x-mary-input label="Search" placeholder="Search by event name..." x-model="filters.search"
                                @input.debounce.300ms="applyFilters()" class="input-emerald" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Assigned Tickets</h3>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Showing <span x-text="filteredTickets.length"></span> of <span
                                x-text="allTickets.length"></span> tickets
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                    <th class="text-emerald-700 dark:text-emerald-300">Ticket ID</th>
                                    <th class="text-emerald-700 dark:text-emerald-300">Event Name</th>
                                    <th class="text-emerald-700 dark:text-emerald-300">Organization</th>
                                    <th class="text-emerald-700 dark:text-emerald-300">Request Type</th>
                                    <th class="text-emerald-700 dark:text-emerald-300">Event Date</th>
                                    <th class="text-emerald-700 dark:text-emerald-300">Priority</th>
                                    <th class="text-emerald-700 dark:text-emerald-300">Status</th>
                                    <th class="text-emerald-700 dark:text-emerald-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="ticket in filteredTickets" :key="ticket.id">
                                    <tr class="hover:bg-emerald-50 dark:hover:bg-emerald-900/10">
                                        <td>
                                            <span class="font-mono text-sm" x-text="ticket.id"></span>
                                        </td>
                                        <td>
                                            <div class="font-medium" x-text="ticket.event_name"></div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400" x-text="ticket.venue">
                                            </div>
                                        </td>
                                        <td x-text="ticket.organization"></td>
                                        <td>
                                            <div class="badge" :class="getTypeClass(ticket.type)" x-text="ticket.type">
                                            </div>
                                        </td>
                                        <td x-text="ticket.event_date"></td>
                                        <td>
                                            <div class="badge" :class="getPriorityClass(ticket.priority)"
                                                x-text="ticket.priority"></div>
                                        </td>
                                        <td>
                                            <div class="badge" :class="getStatusClass(ticket.status)"
                                                x-text="ticket.status"></div>
                                        </td>
                                        <td>
                                            <div class="flex space-x-2">
                                                <button class="btn btn-sm btn-outline btn-emerald"
                                                    @click="viewTicket(ticket)">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                        </path>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                        </path>
                                                    </svg>
                                                    View
                                                </button>
                                                <template x-if="ticket.status === 'pending'">
                                                    <button class="btn btn-sm btn-emerald"
                                                        @click="processTicket(ticket)">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                            </path>
                                                        </svg>
                                                        Process
                                                    </button>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Detail Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="showModal = false" x-transition:enter.duration.0ms x-transition:leave.duration.0ms>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"
                    x-transition:enter.opacity.duration.0ms x-transition:leave.opacity.duration.0ms>
                </div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
                    x-transition:enter.duration.0ms x-transition:enter.scale.origin.bottom
                    x-transition:leave.duration.0ms x-transition:leave.scale.origin.bottom>
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                x-text="`Ticket Details - ${selectedTicket?.id}`"></h3>
                            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <template x-if="selectedTicket">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-semibold text-emerald-700 dark:text-emerald-300 mb-3">Event
                                        Information</h4>
                                    <div class="space-y-2 text-sm">
                                        <div><strong>Event Name:</strong> <span
                                                x-text="selectedTicket.event_name"></span></div>
                                        <div><strong>Organization:</strong> <span
                                                x-text="selectedTicket.organization"></span></div>
                                        <div><strong>Event Date:</strong> <span
                                                x-text="selectedTicket.event_date"></span></div>
                                        <div><strong>Venue:</strong> <span x-text="selectedTicket.venue"></span></div>
                                        <div><strong>Expected Attendees:</strong> <span
                                                x-text="selectedTicket.attendees"></span></div>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-semibold text-emerald-700 dark:text-emerald-300 mb-3">Request
                                        Details</h4>
                                    <div class="space-y-2 text-sm">
                                        <div><strong>Request Type:</strong> <span x-text="selectedTicket.type"></span>
                                        </div>
                                        <div><strong>Priority:</strong> <span x-text="selectedTicket.priority"></span>
                                        </div>
                                        <div><strong>Status:</strong> <span x-text="selectedTicket.status"></span>
                                        </div>
                                        <div><strong>Submitted:</strong> <span
                                                x-text="selectedTicket.submitted_date"></span></div>
                                        <div><strong>Due Date:</strong> <span x-text="selectedTicket.due_date"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-span-2">
                                    <h4 class="font-semibold text-emerald-700 dark:text-emerald-300 mb-3">Description
                                    </h4>
                                    <p class="text-sm text-gray-700 dark:text-gray-300"
                                        x-text="selectedTicket.description"></p>
                                </div>

                                <div class="col-span-2">
                                    <h4 class="font-semibold text-emerald-700 dark:text-emerald-300 mb-3">Requirements
                                    </h4>
                                    <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                        <ul class="text-sm space-y-1">
                                            <template x-for="requirement in selectedTicket.requirements"
                                                :key="requirement">
                                                <li x-text="requirement" class="flex items-center">
                                                    <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <template x-if="selectedTicket?.status === 'pending'">
                            <div class="flex space-x-3">
                                <button type="button" class="btn btn-emerald"
                                    @click="approveTicket(selectedTicket)">
                                    Approve
                                </button>
                                <button type="button" class="btn btn-error" @click="rejectTicket(selectedTicket)">
                                    Reject
                                </button>
                                <button type="button" class="btn btn-outline"
                                    @click="requestMoreInfo(selectedTicket)">
                                    Request More Info
                                </button>
                            </div>
                        </template>
                        <button type="button" class="btn btn-ghost mt-3 sm:mt-0 sm:mr-3" @click="showModal = false">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function ticketReview() {
            return {
                showModal: false,
                selectedTicket: null,
                filters: {
                    type: '',
                    priority: '',
                    status: '',
                    search: ''
                },
                allTickets: [{
                        id: 'TKT-001',
                        event_name: 'Leadership Summit 2024',
                        organization: 'Student Council',
                        type: 'Venue Booking',
                        event_date: 'Nov 15, 2024',
                        venue: 'Main Auditorium',
                        priority: 'High',
                        status: 'pending',
                        attendees: '200',
                        submitted_date: 'Oct 1, 2024',
                        due_date: 'Oct 15, 2024',
                        description: 'Annual leadership summit for student leaders across all organizations.',
                        requirements: ['Audio/Visual Equipment', 'Seating for 200', 'Stage Setup', 'Lighting']
                    },
                    {
                        id: 'TKT-002',
                        event_name: 'Science Fair',
                        organization: 'Science Club',
                        type: 'Equipment',
                        event_date: 'Nov 20, 2024',
                        venue: 'Science Building',
                        priority: 'Medium',
                        status: 'under_review',
                        attendees: '150',
                        submitted_date: 'Sep 28, 2024',
                        due_date: 'Oct 20, 2024',
                        description: 'Annual science fair showcasing student research projects.',
                        requirements: ['Display Tables', 'Power Outlets', 'Projectors', 'Extension Cords']
                    },
                    {
                        id: 'TKT-003',
                        event_name: 'Cultural Night',
                        organization: 'Cultural Society',
                        type: 'Logistics',
                        event_date: 'Dec 1, 2024',
                        venue: 'University Plaza',
                        priority: 'Low',
                        status: 'approved',
                        attendees: '300',
                        submitted_date: 'Sep 25, 2024',
                        due_date: 'Oct 25, 2024',
                        description: 'Multicultural celebration featuring performances and food.',
                        requirements: ['Stage Platform', 'Sound System', 'Food Stalls', 'Security']
                    }
                ],
                filteredTickets: [],

                init() {
                    this.filteredTickets = this.allTickets;
                },

                applyFilters() {
                    this.filteredTickets = this.allTickets.filter(ticket => {
                        const matchesType = !this.filters.type || ticket.type.toLowerCase().includes(this.filters
                            .type.toLowerCase());
                        const matchesPriority = !this.filters.priority || ticket.priority.toLowerCase() === this
                            .filters.priority.toLowerCase();
                        const matchesStatus = !this.filters.status || ticket.status.toLowerCase() === this.filters
                            .status.toLowerCase();
                        const matchesSearch = !this.filters.search ||
                            ticket.event_name.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                            ticket.organization.toLowerCase().includes(this.filters.search.toLowerCase());

                        return matchesType && matchesPriority && matchesStatus && matchesSearch;
                    });
                },

                viewTicket(ticket) {
                    this.selectedTicket = ticket;
                    this.showModal = true;
                },

                processTicket(ticket) {
                    this.viewTicket(ticket);
                },

                approveTicket(ticket) {
                    ticket.status = 'approved';
                    this.showModal = false;
                    this.applyFilters();
                    // Here you would typically make an API call
                    alert(`Ticket ${ticket.id} approved successfully!`);
                },

                rejectTicket(ticket) {
                    ticket.status = 'rejected';
                    this.showModal = false;
                    this.applyFilters();
                    // Here you would typically make an API call
                    alert(`Ticket ${ticket.id} rejected.`);
                },

                requestMoreInfo(ticket) {
                    ticket.status = 'under_review';
                    this.showModal = false;
                    this.applyFilters();
                    // Here you would typically open a communication modal
                    alert(`More information requested for ticket ${ticket.id}.`);
                },

                getTypeClass(type) {
                    const classes = {
                        'Venue Booking': 'badge-primary',
                        'Equipment': 'badge-secondary',
                        'Logistics': 'badge-accent',
                        'Catering': 'badge-info'
                    };
                    return classes[type] || 'badge-ghost';
                },

                getPriorityClass(priority) {
                    const classes = {
                        'High': 'badge-error',
                        'Medium': 'badge-warning',
                        'Low': 'badge-success'
                    };
                    return classes[priority] || 'badge-ghost';
                },

                getStatusClass(status) {
                    const classes = {
                        'pending': 'badge-warning',
                        'under_review': 'badge-info',
                        'approved': 'badge-success',
                        'rejected': 'badge-error'
                    };
                    return classes[status] || 'badge-ghost';
                }
            }
        }
    </script>
</x-layouts.gso-layout>
