import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";
import interactionPlugin from "@fullcalendar/interaction";

// Make Calendar available globally for Livewire
window.FullCalendar = { Calendar };

// Store calendar plugins globally
window.FullCalendarPlugins = {
    dayGridPlugin,
    timeGridPlugin,
    listPlugin,
    interactionPlugin,
};

console.log("FullCalendar loaded from npm packages");
