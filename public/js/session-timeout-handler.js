function sessionTimeout(config) {
    return {
        lifetime: config.lifetime * 60, // Convert minutes to seconds
        warningTime: config.warningTime * 60, // Convert minutes to seconds
        countdown: 300, // Default 5 minutes in seconds
        timer: null,
        countdownTimer: null,
        warningShown: false,
        livewireComponent: null,

        init() {
            // Find the Livewire component instance
            this.findLivewireComponent();
            this.startTimer();
            this.setupActivityListeners();

            // Listen for the custom event to reset the timer from Livewire
            window.addEventListener("session-refreshed", () => {
                this.resetTimer();
            });
        },

        findLivewireComponent() {
            const componentEl = document.querySelector("[wire\\:id]");
            if (componentEl) {
                this.livewireComponent = Livewire.find(
                    componentEl.getAttribute("wire:id")
                );
            }
        },

        startTimer() {
            // Clear any existing timers
            if (this.timer) clearTimeout(this.timer);
            if (this.countdownTimer) clearInterval(this.countdownTimer);

            // Calculate when to show warning
            const warningDelay = this.warningTime * 1000; // Convert to milliseconds

            // Set timer to show warning
            this.timer = setTimeout(() => {
                this.showWarning();
            }, warningDelay);
        },

        showWarning() {
            if (this.warningShown) return;

            this.warningShown = true;
            this.countdown = this.lifetime - this.warningTime; // Time remaining until logout

            // Show the modal via Livewire
            if (this.livewireComponent) {
                this.livewireComponent.call("showWarningModal", this.countdown);
            }

            // Start countdown
            this.countdownTimer = setInterval(() => {
                this.countdown--;

                if (this.countdown <= 0) {
                    // Time's up - redirect to logout
                    this.logout();
                }
            }, 1000);
        },

        resetTimer() {
            // Clear existing timers
            if (this.timer) clearTimeout(this.timer);
            if (this.countdownTimer) clearInterval(this.countdownTimer);

            // Reset state
            this.warningShown = false;
            this.countdown = this.lifetime - this.warningTime;

            // Restart the timer
            this.startTimer();
        },

        setupActivityListeners() {
            // List of events that indicate user activity
            const events = [
                "mousedown",
                "mousemove",
                "keypress",
                "scroll",
                "touchstart",
                "click",
            ];

            // Throttle activity detection to prevent excessive resets
            let lastActivity = Date.now();
            const throttleTime = 60000; // 1 minute

            events.forEach((event) => {
                document.addEventListener(
                    event,
                    () => {
                        const now = Date.now();
                        if (
                            !this.warningShown &&
                            now - lastActivity > throttleTime
                        ) {
                            lastActivity = now;
                            this.resetTimer();
                        }
                    },
                    true
                );
            });
        },

        formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;

            if (minutes > 0) {
                return `${minutes}:${remainingSeconds
                    .toString()
                    .padStart(2, "0")} minute${minutes > 1 ? "s" : ""}`;
            }
            return `${seconds} second${seconds > 1 ? "s" : ""}`;
        },

        logout() {
            // Submit logout form
            const form = document.createElement("form");
            form.method = "POST";
            form.action = config.logoutUrl;

            const csrfToken = document.createElement("input");
            csrfToken.type = "hidden";
            csrfToken.name = "_token";
            csrfToken.value = config.csrfToken;
            form.appendChild(csrfToken);

            document.body.appendChild(form);
            form.submit();
        },
    };
}
