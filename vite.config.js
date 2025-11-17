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
        // Use esbuild for faster minification (built-in, no extra dependency)
        minify: "esbuild",
        // Enable CSS code splitting for better performance
        cssCodeSplit: true,
        // Inline small assets as base64 (< 4kb)
        assetsInlineLimit: 4096,
        // Optimize chunk sizes
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
        // Increase chunk size warning limit
        chunkSizeWarningLimit: 1000,
        // Target modern browsers for smaller bundles
        target: "es2015",
        // Enable source maps for production debugging (optional)
        sourcemap: false,
        // Report compressed file sizes
        reportCompressedSize: true,
    },
    // Optimize dev server for faster HMR
    server: {
        hmr: {
            overlay: false, // Disable error overlay for faster dev
        },
    },
});
