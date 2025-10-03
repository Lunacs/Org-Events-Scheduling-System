<x-layouts.gso-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Event Calendar') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="eventCalendar()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Calendar Controls -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-wrap justify-between items-center gap-4">
                        <div class="flex items-center space-x-4">
                            <button class="btn btn-circle btn-emerald" @click="previousMonth()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>

                            <h3 class="text-2xl font-bold text-emerald-700 dark:text-emerald-300"
                                x-text="currentMonthYear"></h3>

                            <button class="btn btn-circle btn-emerald" @click="nextMonth()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="flex items-center space-x-4">
                            <button class="btn btn-outline btn-emerald" @click="goToToday()">
                                Today
                            </button>

                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-emerald">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z">
                                        </path>
                                    </svg>
                                    Filter
                                </label>
                                <div tabindex="0"
                                    class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                    <li><a @click="filterByStatus('all')">All Events</a></li>
                                    <li><a @click="filterByStatus('approved')">Approved Only</a></li>
                                    <li><a @click="filterByStatus('pending')">Pending Only</a></li>
                                    <li><a @click="filterByOffice('my_office')">My Office Events</a></li>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">View:</span>
                                <div class="btn-group">
                                    <button class="btn btn-sm"
                                        :class="viewMode === 'month' ? 'btn-emerald' : 'btn-outline'"
                                        @click="viewMode = 'month'">Month</button>
                                    <button class="btn btn-sm"
                                        :class="viewMode === 'week' ? 'btn-emerald' : 'btn-outline'"
                                        @click="viewMode = 'week'">Week</button>
                                    <button class="btn btn-sm"
                                        :class="viewMode === 'list' ? 'btn-emerald' : 'btn-outline'"
                                        @click="viewMode = 'list'">List</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Legend:</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-emerald-500 rounded"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Approved</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-yellow-500 rounded"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Pending</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-red-500 rounded"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Rejected</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-purple-500 rounded"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">My Office Involved</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar View -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Month View -->
                    <div x-show="viewMode === 'month'" class="calendar-grid">
                        <!-- Days of Week Header -->
                        <div class="grid grid-cols-7 gap-px mb-2">
                            <template x-for="day in daysOfWeek" :key="day">
                                <div class="bg-emerald-50 dark:bg-emerald-900/20 p-2 text-center text-sm font-medium text-emerald-700 dark:text-emerald-300"
                                    x-text="day"></div>
                            </template>
                        </div>

                        <!-- Calendar Days -->
                        <div class="grid grid-cols-7 gap-px border border-gray-200 dark:border-gray-600">
                            <template x-for="week in calendarWeeks" :key="week.weekIndex">
                                <template x-for="day in week.days" :key="day.date">
                                    <div class="bg-white dark:bg-gray-800 min-h-24 p-1 border-r border-b border-gray-200 dark:border-gray-600"
                                        :class="day.isCurrentMonth ? '' : 'bg-gray-50 dark:bg-gray-700'">
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="text-xs font-medium"
                                                :class="day.isToday ? 'text-emerald-600 dark:text-emerald-400 font-bold' :
                                                    day.isCurrentMonth ? 'text-gray-900 dark:text-gray-100' :
                                                    'text-gray-400'"
                                                x-text="day.dayNumber"></span>
                                        </div>

                                        <div class="space-y-1">
                                            <template x-for="event in getEventsForDay(day.date)" :key="event.id">
                                                <div class="text-xs p-1 rounded cursor-pointer hover:opacity-80"
                                                    :class="getEventClass(event)" @click="viewEventDetails(event)"
                                                    x-text="event.title"></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>

                    <!-- Week View -->
                    <div x-show="viewMode === 'week'" x-cloak>
                        <div class="overflow-x-auto">
                            <div class="grid grid-cols-8 gap-px min-w-full">
                                <div
                                    class="bg-emerald-50 dark:bg-emerald-900/20 p-3 text-center text-sm font-medium text-emerald-700 dark:text-emerald-300">
                                    Time</div>
                                <template x-for="day in weekDays" :key="day.date">
                                    <div class="bg-emerald-50 dark:bg-emerald-900/20 p-3 text-center">
                                        <div class="text-sm font-medium text-emerald-700 dark:text-emerald-300"
                                            x-text="day.dayName"></div>
                                        <div class="text-xs text-emerald-600 dark:text-emerald-400" x-text="day.date">
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <template x-for="hour in timeSlots" :key="hour">
                                <div class="grid grid-cols-8 gap-px border-b border-gray-200 dark:border-gray-600">
                                    <div class="bg-gray-50 dark:bg-gray-700 p-2 text-xs text-gray-600 dark:text-gray-400 text-center"
                                        x-text="hour"></div>
                                    <template x-for="day in weekDays" :key="day.date + hour">
                                        <div class="bg-white dark:bg-gray-800 min-h-12 p-1">
                                            <template x-for="event in getEventsForDayHour(day.date, hour)"
                                                :key="event.id">
                                                <div class="text-xs p-1 rounded mb-1 cursor-pointer"
                                                    :class="getEventClass(event)" @click="viewEventDetails(event)"
                                                    x-text="event.title"></div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- List View -->
                    <div x-show="viewMode === 'list'" x-cloak>
                        <div class="space-y-4">
                            <template x-for="event in filteredEvents" :key="event.id">
                                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                                    @click="viewEventDetails(event)">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                                    x-text="event.title"></h4>
                                                <div class="w-3 h-3 rounded" :class="getEventColorClass(event)"></div>
                                                <div class="badge" :class="getStatusClass(event.status)"
                                                    x-text="event.status"></div>
                                            </div>

                                            <div
                                                class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                <div>
                                                    <strong>Date:</strong> <span x-text="event.date"></span>
                                                </div>
                                                <div>
                                                    <strong>Time:</strong> <span x-text="event.time"></span>
                                                </div>
                                                <div>
                                                    <strong>Venue:</strong> <span x-text="event.venue"></span>
                                                </div>
                                            </div>

                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <strong>Organization:</strong> <span
                                                    x-text="event.organization"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Details Modal -->
        <div x-show="showEventModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="showEventModal = false"></div>

                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                x-text="selectedEvent?.title"></h3>
                            <button @click="showEventModal = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <template x-if="selectedEvent">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Organization</label>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                            x-text="selectedEvent.organization"></p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                        <div class="mt-1">
                                            <div class="badge" :class="getStatusClass(selectedEvent.status)"
                                                x-text="selectedEvent.status"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                            x-text="selectedEvent.date"></p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Time</label>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                            x-text="selectedEvent.time"></p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Venue</label>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                            x-text="selectedEvent.venue"></p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expected
                                            Attendees</label>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                            x-text="selectedEvent.attendees"></p>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                        x-text="selectedEvent.description"></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">GSO
                                        Requirements</label>
                                    <div class="mt-1">
                                        <template
                                            x-if="selectedEvent.gso_requirements && selectedEvent.gso_requirements.length > 0">
                                            <ul class="list-disc list-inside text-sm text-gray-900 dark:text-gray-100">
                                                <template x-for="requirement in selectedEvent.gso_requirements"
                                                    :key="requirement">
                                                    <li x-text="requirement"></li>
                                                </template>
                                            </ul>
                                        </template>
                                        <template
                                            x-if="!selectedEvent.gso_requirements || selectedEvent.gso_requirements.length === 0">
                                            <p class="text-sm text-gray-500 dark:text-gray-400">No GSO requirements</p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="btn btn-emerald" @click="showEventModal = false">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function eventCalendar() {
            return {
                currentDate: new Date(),
                viewMode: 'month',
                showEventModal: false,
                selectedEvent: null,
                statusFilter: 'all',
                officeFilter: 'all',

                daysOfWeek: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                timeSlots: ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00',
                    '18:00'
                ],

                events: [{
                        id: 1,
                        title: 'Leadership Summit',
                        organization: 'Student Council',
                        date: '2024-11-15',
                        time: '09:00 - 17:00',
                        venue: 'Main Auditorium',
                        status: 'approved',
                        attendees: '200',
                        description: 'Annual leadership summit for student leaders.',
                        gso_requirements: ['Venue Booking', 'Audio System', 'Security'],
                        office_involved: true
                    },
                    {
                        id: 2,
                        title: 'Science Fair',
                        organization: 'Science Club',
                        date: '2024-11-20',
                        time: '10:00 - 16:00',
                        venue: 'Science Building',
                        status: 'pending',
                        attendees: '150',
                        description: 'Student science project presentations.',
                        gso_requirements: ['Equipment Setup', 'Power Supply'],
                        office_involved: true
                    },
                    {
                        id: 3,
                        title: 'Cultural Night',
                        organization: 'Cultural Society',
                        date: '2024-12-01',
                        time: '18:00 - 22:00',
                        venue: 'University Plaza',
                        status: 'approved',
                        attendees: '300',
                        description: 'Multicultural celebration event.',
                        gso_requirements: ['Logistics Support', 'Cleanup'],
                        office_involved: false
                    }
                ],

                get currentMonthYear() {
                    return this.currentDate.toLocaleDateString('en-US', {
                        month: 'long',
                        year: 'numeric'
                    });
                },

                get calendarWeeks() {
                    const year = this.currentDate.getFullYear();
                    const month = this.currentDate.getMonth();

                    const firstDay = new Date(year, month, 1);
                    const lastDay = new Date(year, month + 1, 0);
                    const startDate = new Date(firstDay);
                    startDate.setDate(startDate.getDate() - firstDay.getDay());

                    const weeks = [];
                    let currentWeek = [];
                    let currentDate = new Date(startDate);

                    for (let i = 0; i < 42; i++) {
                        currentWeek.push({
                            date: currentDate.toISOString().split('T')[0],
                            dayNumber: currentDate.getDate(),
                            isCurrentMonth: currentDate.getMonth() === month,
                            isToday: this.isToday(currentDate)
                        });

                        if (currentWeek.length === 7) {
                            weeks.push({
                                weekIndex: weeks.length,
                                days: currentWeek
                            });
                            currentWeek = [];
                        }

                        currentDate.setDate(currentDate.getDate() + 1);
                    }

                    return weeks;
                },

                get weekDays() {
                    const startOfWeek = new Date(this.currentDate);
                    startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());

                    const days = [];
                    for (let i = 0; i < 7; i++) {
                        const day = new Date(startOfWeek);
                        day.setDate(day.getDate() + i);
                        days.push({
                            date: day.toISOString().split('T')[0],
                            dayName: day.toLocaleDateString('en-US', {
                                weekday: 'short'
                            })
                        });
                    }
                    return days;
                },

                get filteredEvents() {
                    return this.events.filter(event => {
                        const matchesStatus = this.statusFilter === 'all' || event.status === this.statusFilter;
                        const matchesOffice = this.officeFilter === 'all' ||
                            (this.officeFilter === 'my_office' && event.office_involved);
                        return matchesStatus && matchesOffice;
                    });
                },

                isToday(date) {
                    const today = new Date();
                    return date.toDateString() === today.toDateString();
                },

                previousMonth() {
                    this.currentDate.setMonth(this.currentDate.getMonth() - 1);
                    this.currentDate = new Date(this.currentDate);
                },

                nextMonth() {
                    this.currentDate.setMonth(this.currentDate.getMonth() + 1);
                    this.currentDate = new Date(this.currentDate);
                },

                goToToday() {
                    this.currentDate = new Date();
                },

                getEventsForDay(date) {
                    return this.filteredEvents.filter(event => event.date === date);
                },

                getEventsForDayHour(date, hour) {
                    return this.getEventsForDay(date).filter(event => {
                        // Simple time matching - in real app, you'd parse time ranges
                        return event.time.includes(hour);
                    });
                },

                viewEventDetails(event) {
                    this.selectedEvent = event;
                    this.showEventModal = true;
                },

                filterByStatus(status) {
                    this.statusFilter = status;
                },

                filterByOffice(office) {
                    this.officeFilter = office;
                },

                getEventClass(event) {
                    const baseClass = 'text-white text-xs p-1 rounded truncate';
                    if (event.office_involved) {
                        return `${baseClass} bg-purple-500`;
                    }
                    return `${baseClass} ${this.getEventColorClass(event)}`;
                },

                getEventColorClass(event) {
                    const colors = {
                        'approved': 'bg-emerald-500',
                        'pending': 'bg-yellow-500',
                        'rejected': 'bg-red-500'
                    };
                    return colors[event.status] || 'bg-gray-500';
                },

                getStatusClass(status) {
                    const classes = {
                        'approved': 'badge-success',
                        'pending': 'badge-warning',
                        'rejected': 'badge-error'
                    };
                    return classes[status] || 'badge-ghost';
                }
            }
        }
    </script>
</x-layouts.gso-layout>
