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
                        <button class="btn btn-circle btn-emerald" @click="goPrevious()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>

                        <h3 class="text-2xl font-bold text-emerald-700 dark:text-emerald-300" x-text="currentMonthYear">
                        </h3>

                        <button class="btn btn-circle btn-emerald" @click="goNext()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
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
                            <div tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                <li><a @click="filterByStatus('all')">All Events</a></li>
                                <li><a @click="filterByStatus('approved')">Approved Only</a></li>
                                <li><a @click="filterByStatus('pending')">Pending Only</a></li>
                                <li><a @click="filterByOffice('my_office')">My Office Events</a></li>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">View:</span>
                            <div class="btn-group">
                                <button class="btn btn-sm" :class="viewMode === 'month' ? 'btn-emerald' : 'btn-outline'"
                                    @click="setViewMode('month')">Month</button>
                                <button class="btn btn-sm" :class="viewMode === 'week' ? 'btn-emerald' : 'btn-outline'"
                                    @click="setViewMode('week')">Week</button>
                                <button class="btn btn-sm" :class="viewMode === 'list' ? 'btn-emerald' : 'btn-outline'"
                                    @click="setViewMode('list')">List</button>
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
                        <template x-for="day in days" :key="day.date">
                            <div class="bg-white dark:bg-gray-800 min-h-24 p-1 border-r border-b border-gray-200 dark:border-gray-600"
                                :class="day.isCurrentMonth ? '' : 'bg-gray-50 dark:bg-gray-700'">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-xs font-medium" :class="day.isToday ? 'text-emerald-600 dark:text-emerald-400 font-bold' :
                                                day.isCurrentMonth ? 'text-gray-900 dark:text-gray-100' :
                                                'text-gray-400'" x-text="day.dayNumber"></span>
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
                    </div>
                </div>

                <!-- Week View -->
                <div x-show="viewMode === 'week'" x-cloak>
                    <div class="overflow-x-auto">
                        <div class="flex items-center justify-between mb-4">
                            <button class="btn btn-outline btn-sm btn-emerald" @click="goPrevious()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7"></path>
                                </svg>
                                <span class="ml-1">Previous Week</span>
                            </button>

                            <div class="text-lg font-semibold text-emerald-700 dark:text-emerald-300" x-text="weekRangeLabel"></div>

                            <button class="btn btn-outline btn-sm btn-emerald" @click="goNext()">
                                <span class="mr-1">Next Week</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-8 min-w-full border border-gray-200 dark:border-gray-600">
                            <div
                                class="bg-emerald-50 dark:bg-emerald-900/20 p-3 text-center text-sm font-medium text-emerald-700 dark:text-emerald-300 border-r border-gray-200 dark:border-gray-700">
                                Time
                            </div>
                            <template x-for="day in weekDays" :key="`week-header-${day.date}`">
                                <div class="bg-emerald-50 dark:bg-emerald-900/20 p-3 text-center border-l border-gray-200 dark:border-gray-700">
                                    <div class="text-sm font-medium text-emerald-700 dark:text-emerald-300"
                                        x-text="day.dayName"></div>
                                    <div class="text-xs text-emerald-600 dark:text-emerald-400" x-text="day.date">
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="grid grid-cols-8 min-w-full border border-t-0 border-gray-200 dark:border-gray-600 relative">
                            <div class="relative bg-white dark:bg-gray-800 pt-6">
                                <div class="grid" :style="weekGridTemplateStyle()">
                                    <template x-for="hour in timeSlots" :key="`time-label-${hour}`">
                                        <div class="border-b border-gray-200 dark:border-gray-700 flex items-start justify-center text-xs text-gray-600 dark:text-gray-400 pt-2">
                                            <span x-text="hour"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <template x-for="day in weekDays" :key="`week-column-${day.date}`">
                                <div class="relative border-l border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden pt-6">
                                    <div class="grid h-full" :style="weekGridTemplateStyle()">
                                        <template x-for="hour in timeSlots" :key="`gridline-${day.date}-${hour}`">
                                            <div class="border-b border-gray-200 dark:border-gray-700"></div>
                                        </template>
                                    </div>

                                    <template x-for="layout in getWeekDayLayouts(day.date)" :key="layout.key">
                                        <div class="absolute rounded-md shadow-sm hover:shadow-md transition-shadow cursor-pointer px-2 py-1 text-xs text-white"
                                            :class="layout.event.colorClass"
                                            :style="layout.style"
                                            @click="viewEventDetails(layout.event)">
                                            <div class="font-semibold whitespace-normal wrap-break-word" x-text="layout.event.title"></div>
                                            <div class="text-[10px] opacity-90" x-text="layout.event.time"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
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
                                            <strong>Organization:</strong> <span x-text="event.organization"></span>
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
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showEventModal = false">
            </div>

            <div
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" x-text="selectedEvent?.title">
                        </h3>
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
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expected
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
    const gsoCalendarEvents = @json($events);

    function eventCalendar() {
        return {
            currentDate: new Date(),
            viewMode: 'month',
            showEventModal: false,
            selectedEvent: null,
            statusFilter: 'all',
            officeFilter: 'all',
            weeks: [],
            days: [],

            daysOfWeek: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            timeSlots: [
                '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00',
                '18:00', '19:00', '20:00', '21:00'
            ],

            eventPalette: [
                'bg-emerald-500',
                'bg-blue-500',
                'bg-sky-500',
                'bg-amber-500',
                'bg-rose-500',
                'bg-lime-500',
                'bg-indigo-500',
                'bg-cyan-500'
            ],

            events: [],
            timelineStartMinutes: 0,
            timelineEndMinutes: 0,
            timelineDurationMinutes: 0,
            weekMinEventHeightPercent: 4,
            weekColumnGapPercent: 1,

            init() {
                this.events = gsoCalendarEvents.map((event, index) => this.enhanceEvent(event, index));
                this.timelineStartMinutes = this.parseTimeToMinutes(this.timeSlots[0]) ?? (7 * 60);
                const lastSlotMinutes = this.parseTimeToMinutes(this.timeSlots[this.timeSlots.length - 1]) ?? (21 * 60);
                this.timelineEndMinutes = lastSlotMinutes + 60;

                if (this.timelineEndMinutes <= this.timelineStartMinutes) {
                    this.timelineEndMinutes = this.timelineStartMinutes + (12 * 60);
                }

                this.timelineDurationMinutes = Math.max(this.timelineEndMinutes - this.timelineStartMinutes, 60);

                const today = new Date();
                this.currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                this.computeCalendar();
            },

            get currentMonthYear() {
                return this.currentDate.toLocaleDateString('en-US', {
                    month: 'long',
                    year: 'numeric'
                });
            },

            computeCalendar() {
                const year = this.currentDate.getFullYear();
                const month = this.currentDate.getMonth();

                const firstDay = new Date(year, month, 1);
                const startDate = new Date(firstDay);
                startDate.setDate(startDate.getDate() - firstDay.getDay());

                const weeks = [];
                const days = [];
                let currentWeek = [];
                let currentDate = new Date(startDate);

                for (let i = 0; i < 42; i++) {
                    const day = {
                        date: this.formatDateKey(currentDate),
                        dayNumber: currentDate.getDate(),
                        isCurrentMonth: currentDate.getMonth() === month,
                        isToday: this.isToday(currentDate)
                    };

                    currentWeek.push(day);
                    days.push(day);

                    if (currentWeek.length === 7) {
                        weeks.push({
                            weekIndex: weeks.length,
                            days: currentWeek
                        });
                        currentWeek = [];
                    }

                    currentDate.setDate(currentDate.getDate() + 1);
                }

                this.weeks = weeks;
                this.days = days;
            },

            setViewMode(mode) {
                this.viewMode = mode;
                this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), this.currentDate.getDate());
                this.computeCalendar();
            },

            get weekDays() {
                const startOfWeek = new Date(this.currentDate);
                startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());

                const days = [];
                for (let i = 0; i < 7; i++) {
                    const day = new Date(startOfWeek);
                    day.setDate(day.getDate() + i);
                    days.push({
                        date: this.formatDateKey(day),
                        dayName: day.toLocaleDateString('en-US', {
                            weekday: 'short'
                        })
                    });
                }
                return days;
            },

            get weekRangeLabel() {
                const startOfWeek = new Date(this.currentDate);
                startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());

                const endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(endOfWeek.getDate() + 6);

                return `${this.formatDisplayDate(startOfWeek)} — ${this.formatDisplayDate(endOfWeek)}`;
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

            goPrevious() {
                const baseDate = new Date(this.currentDate);

                if (this.viewMode === 'week') {
                    baseDate.setDate(baseDate.getDate() - 7);
                    this.currentDate = new Date(baseDate.getFullYear(), baseDate.getMonth(), baseDate.getDate());
                } else {
                    this.currentDate = new Date(baseDate.getFullYear(), baseDate.getMonth() - 1, 1);
                }

                this.computeCalendar();
            },

            goNext() {
                const baseDate = new Date(this.currentDate);

                if (this.viewMode === 'week') {
                    baseDate.setDate(baseDate.getDate() + 7);
                    this.currentDate = new Date(baseDate.getFullYear(), baseDate.getMonth(), baseDate.getDate());
                } else {
                    this.currentDate = new Date(baseDate.getFullYear(), baseDate.getMonth() + 1, 1);
                }

                this.computeCalendar();
            },

            goToToday() {
                const today = new Date();
                this.currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                this.computeCalendar();
            },

            getEventsForDay(date) {
                return this.filteredEvents.filter(event => event.date === date);
            },

            weekGridTemplateStyle() {
                return `grid-template-rows: repeat(${this.timeSlots.length}, minmax(3.5rem, 1fr));`;
            },

            getWeekDayLayouts(date) {
                const timelineStart = this.timelineStartMinutes;
                const timelineEnd = this.timelineEndMinutes;
                const totalMinutes = this.timelineDurationMinutes || 1;
                const gapPercent = this.weekColumnGapPercent ?? 1;
                const minHeightPercent = this.weekMinEventHeightPercent ?? 2;

                const events = this.getEventsForDay(date)
                    .map((event, index) => {
                        let start = event.startMinutes ?? timelineStart;
                        let end = event.endMinutes ?? (event.startMinutes ?? (timelineStart + 60));

                        if (end <= start) {
                            end = start + 60;
                        }

                        start = Math.max(start, timelineStart);
                        end = Math.min(Math.max(end, start + 15), timelineEnd);

                        return {
                            event,
                            start,
                            end,
                            key: `${event.id ?? event.title ?? 'event'}-${index}`
                        };
                    })
                    .filter(entry => entry.end > entry.start);

                if (! events.length) {
                    return [];
                }

                const sorted = [...events].sort((a, b) => {
                    if (a.start !== b.start) {
                        return a.start - b.start;
                    }

                    return a.end - b.end;
                });

                const clusterMaxColumns = {};
                const active = [];
                let clusterSequence = 0;

                sorted.forEach(entry => {
                    for (let i = active.length - 1; i >= 0; i--) {
                        if (active[i].end <= entry.start) {
                            active.splice(i, 1);
                        }
                    }

                    let clusterId;
                    if (active.length) {
                        clusterId = active[0].clusterId;
                    } else {
                        clusterId = `cluster-${date}-${clusterSequence++}`;
                    }

                    const usedColumns = active.map(item => item.column);
                    let column = 0;
                    while (usedColumns.includes(column)) {
                        column++;
                    }

                    entry.clusterId = clusterId;
                    entry.column = column;

                    active.push(entry);

                    const currentMax = clusterMaxColumns[clusterId] ?? 0;
                    clusterMaxColumns[clusterId] = Math.max(currentMax, column + 1);
                });

                return events.map(entry => {
                    const clusterColumns = clusterMaxColumns[entry.clusterId] ?? 1;
                    const widthPercent = 100 / clusterColumns;
                    const leftPercent = widthPercent * entry.column;
                    let topPercent = ((entry.start - timelineStart) / totalMinutes) * 100;
                    let heightPercent = ((entry.end - entry.start) / totalMinutes) * 100;

                    heightPercent = Math.max(heightPercent, minHeightPercent);

                    topPercent = Math.max(0, Math.min(topPercent, 100));
                    if (topPercent + heightPercent > 100) {
                        heightPercent = Math.max(minHeightPercent, 100 - topPercent);
                    }

                    let adjustedWidth = Math.max(widthPercent - gapPercent, widthPercent * 0.6);
                    let adjustedLeft = leftPercent + (gapPercent / 2);

                    adjustedLeft = Math.max(0, Math.min(adjustedLeft, 100));
                    if (adjustedLeft + adjustedWidth > 100) {
                        adjustedWidth = Math.max(0, 100 - adjustedLeft);
                    }

                    const formatPercent = value => Number(value.toFixed(4));

                    topPercent = formatPercent(topPercent);
                    heightPercent = formatPercent(heightPercent);
                    adjustedLeft = formatPercent(adjustedLeft);
                    adjustedWidth = formatPercent(adjustedWidth);

                    const style = `top:${topPercent}%;height:${heightPercent}%;left:${adjustedLeft}%;width:${adjustedWidth}%;z-index:${10 + entry.column};`;

                    return {
                        ...entry,
                        style
                    };
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

            formatDateKey(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}`;
            },

            formatDisplayDate(date) {
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
            },

            getEventClass(event) {
                const baseClass = 'text-white text-xs p-1 rounded truncate cursor-pointer';
                const colorClass = event.colorClass || 'bg-emerald-500';
                const emphasis = event.office_involved ? ' ring-2 ring-offset-1 ring-purple-200' : '';
                return `${baseClass} ${colorClass}${emphasis}`;
            },

            getEventColorClass(event) {
                return event.colorClass || 'bg-emerald-500';
            },

            parseTimeToMinutes(value) {
                if (typeof value === 'number') {
                    return value;
                }

                if (! value || value === '—') {
                    return null;
                }

                const parts = String(value).split(':');
                if (parts.length < 2) {
                    return null;
                }

                const hours = Number(parts[0]);
                const minutes = Number(parts[1]);

                if (Number.isNaN(hours) || Number.isNaN(minutes)) {
                    return null;
                }

                return hours * 60 + minutes;
            },

            enhanceEvent(event, index) {
                let startMinutes = typeof event.start_minutes === 'number'
                    ? event.start_minutes
                    : this.parseTimeToMinutes(event.start_time);

                let endMinutes = typeof event.end_minutes === 'number'
                    ? event.end_minutes
                    : this.parseTimeToMinutes(event.end_time);

                if (startMinutes !== null && endMinutes !== null && endMinutes < startMinutes) {
                    endMinutes = startMinutes + 60;
                }

                if (startMinutes === null && endMinutes !== null) {
                    startMinutes = endMinutes - 60;
                }

                if (startMinutes !== null && endMinutes === null) {
                    endMinutes = startMinutes + 60;
                }

                if (startMinutes === null && endMinutes === null) {
                    startMinutes = 7 * 60;
                    endMinutes = 8 * 60;
                }

                return {
                    ...event,
                    startMinutes,
                    endMinutes,
                    colorClass: this.getPaletteColor(event, index)
                };
            },

            getPaletteColor(event, index) {
                const palette = this.eventPalette;
                if (! palette.length) {
                    return 'bg-emerald-500';
                }

                const key = event.id ?? `${event.title ?? ''}-${index}`;
                const hash = this.hashString(String(key));

                return palette[Math.abs(hash) % palette.length];
            },

            hashString(value) {
                let hash = 0;
                for (let i = 0; i < value.length; i++) {
                    hash = ((hash << 5) - hash) + value.charCodeAt(i);
                    hash |= 0;
                }

                return hash;
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