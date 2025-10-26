import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            external: [
                "@fullcalendar/core",
                "@fullcalendar/daygrid",
                "@fullcalendar/timegrid",
                "@fullcalendar/interaction",
                "@fullcalendar/list",
            ],
            output: {
                globals: {
                    "@fullcalendar/core": "FullCalendar",
                    "@fullcalendar/daygrid": "FullCalendarDayGrid",
                    "@fullcalendar/timegrid": "FullCalendarTimeGrid",
                    "@fullcalendar/interaction": "FullCalendarInteraction",
                    "@fullcalendar/list": "FullCalendarList",
                },
            },
        },
    },
});
