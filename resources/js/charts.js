import { Chart, registerables } from "chart.js";

// Register all Chart.js components
Chart.register(...registerables);

// Make Chart available globally
window.Chart = Chart;

/**
 * Alpine factory backing the <x-ui.chart> component (replacement for <x-mary-chart>).
 *
 * Usage (from Blade):
 *   x-data="chartComponent({ settings: <full Chart.js config> })"
 *   with a <canvas x-ref="canvas"> inside.
 *
 * Responsibilities:
 *  - Create the chart in init() via $nextTick (canvas is in the DOM by then).
 *  - Recreate the chart whenever `settings` changes ($watch) — covers wire:model / @entangle updates.
 *  - Re-initialize after SPA navigation (`livewire:navigated`, i.e. wire:navigate).
 *  - Destroy the chart on teardown.
 *  - Guard against an undefined Chart global and against empty data (render nothing, no throw).
 */
window.chartComponent = function (config) {
    return {
        settings: config && config.settings ? config.settings : null,
        chart: null,
        _navHandler: null,

        init() {
            // Draw once the canvas is present in the DOM.
            this.$nextTick(() => this.renderChart());

            // Recreate when the (possibly entangled) settings change.
            this.$watch("settings", () => this.renderChart());

            // Re-initialize after wire:navigate SPA navigations.
            this._navHandler = () => this.$nextTick(() => this.renderChart());
            document.addEventListener("livewire:navigated", this._navHandler);
        },

        renderChart() {
            const canvas = this.$refs.canvas;
            if (!canvas) {
                return;
            }

            // Guard: Chart.js global not available.
            if (typeof Chart === "undefined") {
                console.error("Chart.js is not loaded");
                return;
            }

            const settings = this.settings;
            const data = settings && settings.data ? settings.data : null;
            const labels =
                data && Array.isArray(data.labels) ? data.labels : [];
            const datasets =
                data && Array.isArray(data.datasets) ? data.datasets : [];

            // Guard: empty data — tear down any existing chart and bail.
            const hasData =
                labels.length > 0 &&
                datasets.some((dataset) => (dataset.data || []).length > 0);

            if (!settings || !hasData) {
                this.destroyChart();
                return;
            }

            // Recreate cleanly each time to avoid duplicate canvases/leaks.
            this.destroyChart();
            this.chart = new Chart(canvas, settings);
        },

        destroyChart() {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
        },

        // Alpine calls destroy() automatically when the element is removed.
        destroy() {
            if (this._navHandler) {
                document.removeEventListener(
                    "livewire:navigated",
                    this._navHandler,
                );
                this._navHandler = null;
            }
            this.destroyChart();
        },
    };
};
