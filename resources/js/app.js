import "./bootstrap";
import "./avatar";
import "./calendar";
import { Chart, registerables } from "chart.js";

// Register all Chart.js components
Chart.register(...registerables);

// Make Chart available globally
window.Chart = Chart;

// Register Alpine.js password strength component globally
// This must be in app.js (not inline @push) to work with wire:navigate SPA navigation
document.addEventListener("alpine:init", () => {
    Alpine.data("passwordStrength", (passwordEntangle, showVar) => ({
        password: passwordEntangle,
        show: false,
        get hasMinLength() {
            return this.password && this.password.length >= 8;
        },
        get hasUpperCase() {
            return this.password && /[A-Z]/.test(this.password);
        },
        get hasLowerCase() {
            return this.password && /[a-z]/.test(this.password);
        },
        get hasNumber() {
            return this.password && /[0-9]/.test(this.password);
        },
        get hasSymbol() {
            return (
                this.password && /[!@#$%^&*(),.?":{}|<>]/.test(this.password)
            );
        },
        get strength() {
            let score = 0;
            if (this.hasMinLength) score++;
            if (this.hasUpperCase) score++;
            if (this.hasLowerCase) score++;
            if (this.hasNumber) score++;
            if (this.hasSymbol) score++;
            return score;
        },
        get strengthLabel() {
            if (this.strength === 0) return "Very Weak";
            if (this.strength <= 2) return "Weak";
            if (this.strength <= 3) return "Medium";
            if (this.strength === 4) return "Good";
            return "Strong";
        },
        get strengthColor() {
            if (this.strength === 0) return "bg-error";
            if (this.strength <= 2) return "bg-error";
            if (this.strength <= 3) return "bg-warning";
            if (this.strength === 4) return "bg-info";
            return "bg-success";
        },
    }));
});
