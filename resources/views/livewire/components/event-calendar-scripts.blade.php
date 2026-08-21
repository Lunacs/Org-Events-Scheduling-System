<script>
    window.osaCalendar = function() {
        return {
            calendar: null,
            currentView: '{{ $viewMode }}',
            suppressUrlUpdate: true,
            allEvents: @json($events), // Store events in component state for dynamic updates
            init() {
                if (typeof window.FullCalendar === 'undefined' || typeof window.FullCalendarPlugins ===
                    'undefined') {
                    console.error('FullCalendar not loaded. Run your asset build.');
                    return;
                }

                // Responsive height calculation helper
                const getHeight = () => {
                    if (window.innerWidth >= 1024) return 750;
                    if (window.innerWidth >= 768) return 650;
                    return 500; // Mobile screens
                };

                // Store getHeight for resize handler
                this.getHeight = getHeight;

                const height = getHeight();
                const initialView = '{{ $viewMode }}';
                const component = this; // Store component reference for use in callbacks

                this.calendar = new window.FullCalendar.Calendar(this.$refs.cal, {
                    plugins: [
                        window.FullCalendarPlugins.dayGridPlugin,
                        window.FullCalendarPlugins.timeGridPlugin,
                        window.FullCalendarPlugins.listPlugin,
                        window.FullCalendarPlugins.interactionPlugin,
                    ],
                    initialView: initialView,
                    headerToolbar: false,
                    height,
                    // Filter events based on current view
                    // Month view: Show spanning events (forMonthView: true) or single-day events
                    // Week/Day/List views: Show recurring events (forTimeView: true) or single-day events
                    events: function(info, successCallback, failureCallback) {
                        // Safely get current view
                        let currentView = null;

                        // Try to get view from info parameter first
                        if (info && info.view && info.view.type) {
                            currentView = info.view.type;
                        }
                        // Fallback to component's tracked current view
                        else if (component && component.currentView) {
                            currentView = component.currentView;
                        }
                        // Fallback to calendar's view if available
                        else if (component && component.calendar && component.calendar.view && component
                            .calendar.view.type) {
                            currentView = component.calendar.view.type;
                        }
                        // Final fallback to initial view
                        else {
                            currentView = initialView;
                        }

                        // Filter events based on view type
                        // Use component's allEvents which gets updated when filters change
                        const eventsToFilter = component.allEvents || [];
                        const filteredEvents = eventsToFilter.filter(event => {
                            const props = event.extendedProps || {};
                            const isMultiDay = props.forMonthView || props.forTimeView;

                            // Single-day events: Show in all views
                            if (!isMultiDay) {
                                return true;
                            }

                            // Month view: Only show spanning events
                            if (currentView === 'dayGridMonth') {
                                return props.forMonthView === true;
                            }

                            // Week/Day/List views: Only show recurring events
                            return props.forTimeView === true;
                        });

                        successCallback(filteredEvents);
                    },
                    themeSystem: 'standard',

                    // CRITICAL: Time handling configuration
                    // FullCalendar receives ISO strings WITH timezone offset (+08:00)
                    // Set timeZone to 'Asia/Manila' so FullCalendar interprets these times
                    // as Asia/Manila time WITHOUT converting to user's local timezone
                    // This ensures events display at the exact times stored in the database
                    // Example: "2025-11-05T08:00:00+08:00" will display as 8:00 AM in Manila time
                    timeZone: 'Asia/Manila',
                    nowIndicator: true,
                    eventDataTransform: function(eventData) {
                        // Ensure allDay is explicitly false for timed events
                        if (eventData.allDay === undefined && eventData.start && eventData.end) {
                            eventData.allDay = false;
                        }

                        return eventData;
                    },

                    // View-specific options
                    views: {
                        dayGridMonth: {
                            dayMaxEvents: 3, // Show max 3 events, rest in "more" link
                            eventMaxStack: 3,
                            moreLinkText: 'more',
                            dayHeaderFormat: {
                                weekday: 'short'
                            },
                        },
                        timeGridWeek: {
                            eventDisplay: 'auto',
                            dayHeaderFormat: {
                                weekday: 'short',
                                month: 'numeric',
                                day: 'numeric',
                                omitCommas: true
                            },
                            // slotDuration: '00:30:00', // 30-minute slots
                            // snapDuration: '00:15:00', // Snap to 15-minute intervals
                            eventMinHeight: 15, // Minimum height in pixels
                            eventShortHeight: 20, // Height for short events
                            expandRows: false, // Let events size based on duration
                            slotEventOverlap: true, // Allow overlapping events
                        },
                        timeGridDay: {
                            dayHeaderFormat: {
                                weekday: 'long',
                                month: 'long',
                                day: 'numeric',
                                year: 'numeric'
                            },
                            slotLabelFormat: {
                                hour: 'numeric',
                                minute: '2-digit',
                                meridiem: 'short'
                            },
                            slotDuration: '00:30:00', // 30-minute slots
                            snapDuration: '00:15:00', // Snap to 15-minute intervals
                            eventMinHeight: 15, // Minimum height in pixels
                            eventShortHeight: 20, // Height for short events
                            expandRows: false, // Let events size based on duration
                            slotEventOverlap: true, // Allow overlapping events
                        },
                        listWeek: {
                            listDayFormat: {
                                weekday: 'long',
                                month: 'short',
                                day: 'numeric'
                            },
                            listDaySideFormat: false,
                            noEventsContent: 'No events scheduled for this period'
                        }
                    },

                    // Interaction & behavior
                    eventClick: (info) => {
                        this.openEventModal(info.event.id)
                    },

                    // Display settings
                    eventDisplay: 'auto', // Auto sizing based on duration
                    eventTextColor: '#ffffff',
                    weekNumbers: false,
                    weekText: 'W',
                    allDaySlot: false, // Hide all-day slot since events have specific times
                    nextDayThreshold: '09:00:00', // Events ending before 9am count as ending on previous day

                    // Time settings
                    slotMinTime: '07:00:00',
                    slotMaxTime: '23:00:00',
                    slotLabelInterval: '01:00:00', // Show labels every hour
                    scrollTime: '08:00:00',
                    scrollTimeReset: true,
                    slotEventOverlap: true, // Allow overlapping events

                    // Event rendering - CRITICAL for height calculation
                    eventMinHeight: 15, // Minimum height in pixels
                    eventShortHeight: 20, // Height threshold for "short" events

                    // Ensure proper duration calculation
                    defaultTimedEventDuration: '01:00:00', // Default 1 hour if no end time
                    forceEventDuration: false, // Use actual end times, not forced duration

                    // Week settings
                    firstDay: 1, // Monday

                    // List view settings
                    listDayFormat: {
                        weekday: 'long',
                        month: 'short',
                        day: 'numeric'
                    },
                    listDaySideFormat: false,
                    eventDidMount: (info) => {
                        const e = info.event;
                        const p = e.extendedProps || {};

                        // Format time for tooltip using raw database times to avoid timezone conversion
                        // Raw times are stored in HH:MM:SS format from the database (Asia/Manila timezone)
                        const formatTimeFromString = (timeStr) => {
                            if (!timeStr) return '';
                            try {
                                // Parse HH:MM:SS format
                                const [hours, minutes] = timeStr.split(':');
                                const h = parseInt(hours, 10);
                                const m = parseInt(minutes, 10);

                                // Convert to 12-hour format
                                const period = h >= 12 ? 'PM' : 'AM';
                                const hour12 = h % 12 || 12;

                                return `${hour12}:${m.toString().padStart(2, '0')} ${period}`;
                            } catch (e) {
                                return timeStr; // Return as-is if parsing fails
                            }
                        };

                        // Use raw times from extendedProps (database values) for accurate tooltip
                        const rawStartTime = p.rawStartTime || '';
                        const rawEndTime = p.rawEndTime || '';
                        const startTimeFormatted = formatTimeFromString(rawStartTime);
                        const endTimeFormatted = formatTimeFromString(rawEndTime);
                        const timeRange = (startTimeFormatted && endTimeFormatted) ?
                            `\n${startTimeFormatted} - ${endTimeFormatted}` :
                            '';

                        info.el.title =
                            `${e.title}${timeRange}\n${p.organization || ''}\n${p.eventType || ''}\n${p.venue || ''}`;

                        // Apply consistent styling
                        info.el.style.borderRadius = '6px';
                        info.el.style.fontSize = '12px';
                        info.el.style.padding = '2px 4px';

                        // Add status-based CSS classes for better theming
                        if (p.status) {
                            info.el.classList.add(`event-${p.status}`);
                        }

                        // Special styling for list view
                        if (info.view.type === 'listWeek') {
                            const c = e.backgroundColor || e.borderColor || '#10b981';
                            info.el.style.setProperty('--event-color', c);

                            // Add colored indicator dot
                            const dotEl = info.el.querySelector('.fc-list-event-dot');
                            if (dotEl) {
                                dotEl.style.borderColor = c;
                                dotEl.style.backgroundColor = c;
                            }
                        }

                        // Add accessibility attributes
                        info.el.setAttribute('role', 'button');
                        info.el.setAttribute('aria-label', `Event: ${e.title}`);
                        info.el.setAttribute('tabindex', '0');

                        // Keyboard accessibility
                        info.el.addEventListener('keydown', (ev) => {
                            if (ev.key === 'Enter' || ev.key === ' ') {
                                ev.preventDefault();
                                this.openEventModal(e.id);
                            }
                        });
                    },
                    viewDidMount: () => {
                        this.updateTitle()
                        this.currentView = this.calendar.view.type
                        // Refetch events when view changes to apply correct filtering
                        this.calendar.refetchEvents()
                    },
                    datesSet: () => {
                        if (this.suppressUrlUpdate) return;
                        const d = this.calendar.getDate();
                        const y = d.getFullYear();
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        const dateStr = `${y}-${m}-${day}`;
                        const view = this.calendar.view.type;
                        const params = new URLSearchParams(window.location.search);
                        params.set('date', dateStr);
                        if (view && view !== 'dayGridMonth') params.set('viewMode', view);
                        else params.delete('viewMode');
                        const newUrl = window.location.pathname + (params.toString() ? '?' + params
                            .toString() : '');
                        window.history.replaceState({}, '', newUrl);
                        this.$wire.set('dateParam', dateStr);
                        this.$wire.set('viewMode', view);
                        this.currentView = view;
                    },
                });

                this.calendar.render();
                // expose for other parts (e.g., filterPanel) and stability
                window.fullCalendar = this.calendar;
                window.osaCalendarInstance = this; // Expose component instance for event updates
                this.updateTitle();

                // Navigate to initial date from server
                const initialDate = '{{ $currentDate->format('Y-m-d') }}';
                if (initialDate) this.calendar.gotoDate(new Date(initialDate));

                setTimeout(() => {
                    this.suppressUrlUpdate = false
                }, 100);

                // Livewire bridge
                this.$wire.on('calendar-prev', () => this.prev());
                this.$wire.on('calendar-next', () => this.next());
                this.$wire.on('calendar-today', () => this.today());
                this.$wire.on('calendar-change-view', (data) => this.changeView(data.view));
                this.$wire.on('calendar-refetch', async () => {
                    const currentDate = this.calendar.getDate();
                    const currentView = this.calendar.view.type;
                    const updated = await this.$wire.getUpdatedEvents();
                    // Update component's allEvents state
                    this.allEvents = updated || [];
                    // Refetch events to apply view-based filtering
                    this.calendar.refetchEvents();
                    this.calendar.gotoDate(currentDate);
                    this.calendar.changeView(currentView);
                    this.updateTitle();
                });

                // Responsive resize handling
                let resizeTimer;
                const handleResize = () => {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(() => {
                        // Update height for different screen sizes
                        const newHeight = this.getHeight();
                        this.calendar.setOption('height', newHeight);
                        // Call updateSize to recalculate calendar layout and adjust to container
                        this.calendar.updateSize();
                    }, 250);
                };
                window.addEventListener('resize', handleResize);
                // Handle orientation change on mobile devices
                window.addEventListener('orientationchange', () => {
                    setTimeout(() => {
                        handleResize();
                    }, 100);
                });

                // Re-render FullCalendar when theme changes so dark/light CSS vars are picked up
                window.addEventListener('theme-changed', () => {
                    if (this.calendar) {
                        this.calendar.updateSize();
                        this.calendar.render();
                    }
                });
            },
            updateTitle() {
                const el = document.getElementById('calendar-title');
                if (el && this.calendar) el.textContent = this.calendar.view.title;
            },
            prev() {
                this.calendar.prev();
                this.updateTitle()
            },
            next() {
                this.calendar.next();
                this.updateTitle()
            },
            today() {
                this.calendar.today();
                this.updateTitle()
            },
            changeView(v) {
                this.currentView = v;
                this.calendar.changeView(v);
                this.updateTitle();
            },
            openEventModal(id) {
                // Extract original event_id from compound IDs (event_id_span or event_id_recur)
                // For example: "123_span" or "123_recur" -> "123"
                let eventId = id;
                if (typeof id === 'string' && id.includes('_')) {
                    const lastUnderscore = id.lastIndexOf('_');
                    const suffix = id.substring(lastUnderscore + 1);
                    if (suffix === 'span' || suffix === 'recur') {
                        // Extract everything before the last underscore
                        eventId = id.substring(0, lastUnderscore);
                        // Convert to number if it's numeric
                        const numericId = parseInt(eventId, 10);
                        if (!isNaN(numericId)) {
                            eventId = numericId;
                        }
                    }
                }

                this.$dispatch('open-event', {
                    id: eventId
                })
            },
        }
    }

    window.filterPanel = function(init) {
        return {
            open: false,
            status: init.initialStatus || 'approved',
            org: init.initialOrg || '',
            etype: init.initialType || '',
            toast: {
                show: false,
                message: ''
            },
            init() {
                this.syncStore()
            },
            syncStore() {
                if (window.Alpine) {
                    if (!Alpine.store('filters')) {
                        Alpine.store('filters', {
                            status: this.status,
                            org: this.org,
                            etype: this.etype
                        });
                    } else {
                        Alpine.store('filters').status = this.status;
                        Alpine.store('filters').org = this.org;
                        Alpine.store('filters').etype = this.etype;
                    }
                }
            },
            showToast(msg) {
                this.toast.message = msg;
                this.toast.show = true;
                setTimeout(() => this.toast.show = false, 2000);
            },
            summaryLabel() {
                const parts = [];
                // For SuperAdmin: Only include status if it's not 'all'
                // For others: Only include status if it's set
                if (this.status && this.status !== 'all') {
                    const statusLabels = {
                        'approved': 'Approved',
                        'rescheduled': 'Rescheduled',
                        'pending': 'Pending',
                        'cancelled': 'Cancelled'
                    };
                    parts.push(statusLabels[this.status] || this.status.charAt(0).toUpperCase() + this.status.slice(
                        1));
                }
                // Only include org if it's actually filtered (not empty)
                if (this.org) {
                    parts.push('Selected Org');
                }
                // Only include event type if it's actually filtered (not empty)
                if (this.etype) {
                    parts.push('Selected Type');
                }
                // Return message only if there are active filters
                return parts.length > 0 ? `Filters applied: ${parts.join(' · ')}` : 'No filters applied';
            },
            async apply() {
                // Instant toast
                this.showToast(this.summaryLabel());
                this.open = false;
                this.syncStore();
                // Single roundtrip: set filters server-side and fetch events
                const updated = await this.$wire.setFiltersAndGetEvents(this.status, this.org, this.etype);
                // Update calendar component's events state and refetch
                if (window.osaCalendarInstance) {
                    window.osaCalendarInstance.allEvents = updated || [];
                    window.osaCalendarInstance.calendar.refetchEvents();
                } else if (window.fullCalendar) {
                    // Fallback: update global calendar if component instance not available
                    const currentDate = window.fullCalendar.getDate();
                    const currentView = window.fullCalendar.view.type;
                    window.fullCalendar.removeAllEvents();
                    if (updated && updated.length) window.fullCalendar.addEventSource(updated);
                    window.fullCalendar.gotoDate(currentDate);
                    window.fullCalendar.changeView(currentView);
                } else {
                    // Fallback: trigger existing refetch behavior
                    this.$wire.dispatch('calendar-refetch');
                }
            },
            async clearAll() {
                // For SuperAdmin, default to 'all', for others default to 'approved'
                const defaultStatus = '{{ $statusFilter }}' === 'all' ? 'all' : 'approved';
                this.status = defaultStatus;
                this.org = '';
                this.etype = '';
                this.showToast('All filters cleared');
                this.open = false;
                this.syncStore();
                const updated = await this.$wire.setFiltersAndGetEvents(this.status, this.org, this.etype);
                // Update calendar component's events state and refetch
                if (window.osaCalendarInstance) {
                    window.osaCalendarInstance.allEvents = updated || [];
                    window.osaCalendarInstance.calendar.refetchEvents();
                } else if (window.fullCalendar) {
                    // Fallback: update global calendar if component instance not available
                    const currentDate = window.fullCalendar.getDate();
                    const currentView = window.fullCalendar.view.type;
                    window.fullCalendar.removeAllEvents();
                    if (updated && updated.length) window.fullCalendar.addEventSource(updated);
                    window.fullCalendar.gotoDate(currentDate);
                    window.fullCalendar.changeView(currentView);
                } else {
                    this.$wire.dispatch('calendar-refetch');
                }
            }
        }
    }
</script>
<script>
    window.eventDetailsModal = function() {
        return {
            open: false,
            loading: false,
            data: null,
            // simple in-memory cache: id -> data or Promise
            cache: {},
            async openById(id) {
                this.open = true;
                const cached = this.cache[id];
                if (cached) {
                    // cached may be data or a pending Promise
                    if (cached.then) {
                        this.loading = true;
                        try {
                            const details = await cached;
                            this.data = details || null;
                        } catch (e) {
                            console.error('Failed to load event details', e);
                        } finally {
                            this.loading = false;
                        }
                    } else {
                        this.data = cached;
                        this.loading = false;
                    }
                    return;
                }

                this.loading = true;
                this.data = null;
                // store the pending promise to dedupe concurrent requests
                const promise = this.$wire.getEventDetails(id)
                    .then((details) => {
                        this.cache[id] = details || null;
                        return this.cache[id];
                    })
                    .catch((e) => {
                        console.error('Failed to load event details', e);
                        // bust cache entry on error
                        delete this.cache[id];
                        return null;
                    });

                this.cache[id] = promise;

                try {
                    const details = await promise;
                    this.data = details;
                } finally {
                    this.loading = false;
                }
            },
            close() {
                this.open = false;
                this.data = null;
                this.loading = false;
            },
            formatDate(d) {
                if (!d) return '—';
                try {
                    const dt = new Date(d);
                    return dt.toLocaleDateString(undefined, {
                        month: 'short',
                        day: '2-digit',
                        year: 'numeric'
                    });
                } catch (_) {
                    return d;
                }
            },
            timeRange(a, b) {
                if (!a && !b) return '—';

                // Format time from HH:MM:SS to readable format (e.g., "8:00 AM")
                const formatTime = (timeStr) => {
                    if (!timeStr) return '';
                    try {
                        // Parse HH:MM:SS format
                        const [hours, minutes] = timeStr.split(':');
                        const h = parseInt(hours, 10);
                        const m = parseInt(minutes, 10);

                        // Convert to 12-hour format
                        const period = h >= 12 ? 'PM' : 'AM';
                        const hour12 = h % 12 || 12;

                        return `${hour12}:${m.toString().padStart(2, '0')} ${period}`;
                    } catch (e) {
                        return timeStr; // Return as-is if parsing fails
                    }
                };

                const startFormatted = formatTime(a);
                const endFormatted = formatTime(b);

                return `${startFormatted}${(startFormatted && endFormatted) ? ' - ' : ''}${endFormatted}`;
            }
        }
    }
</script>
