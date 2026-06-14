import { Chart, registerables } from "chart.js";

// Register all Chart.js components
Chart.register(...registerables);

// Make Chart available globally
window.Chart = Chart;
