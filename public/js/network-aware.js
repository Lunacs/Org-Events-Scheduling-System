/**
 * Network-Aware Loading Utility
 * Adapts content loading based on user's network connection speed
 * Uses Network Information API
 */

// Prevent redeclaration if script is loaded multiple times
if (typeof NetworkAwareLoader === "undefined") {
    class NetworkAwareLoader {
        constructor() {
            this.connection =
                navigator.connection ||
                navigator.mozConnection ||
                navigator.webkitConnection;
            this.config = {
                enabled: true,
                debug: false,
            };
        }

        /**
         * Initialize network-aware loading
         */
        init() {
            if (!this.config.enabled) return;

            // Apply initial optimizations
            this.loadConditionally();

            // Monitor connection changes
            this.monitorConnection();

            if (this.config.debug) {
                this.logConnectionInfo();
            }
        }

        /**
         * Check if user is on a slow connection
         */
        isSlowConnection() {
            if (!this.connection) return false;

            const slowConnections = ["slow-2g", "2g", "3g"];
            return slowConnections.includes(this.connection.effectiveType);
        }

        /**
         * Check if user is on a metered connection (e.g., mobile data)
         */
        isSaveDataEnabled() {
            return this.connection && this.connection.saveData === true;
        }

        /**
         * Get connection speed category
         */
        getConnectionSpeed() {
            if (!this.connection) return "unknown";

            const type = this.connection.effectiveType;
            const speeds = {
                "slow-2g": "very-slow",
                "2g": "slow",
                "3g": "medium",
                "4g": "fast",
            };

            return speeds[type] || "unknown";
        }

        /**
         * Conditionally load content based on connection
         */
        loadConditionally() {
            const isSlowOrSaveData =
                this.isSlowConnection() || this.isSaveDataEnabled();

            if (isSlowOrSaveData) {
                this.optimizeForSlowConnection();
            } else {
                this.optimizeForFastConnection();
            }
        }

        /**
         * Optimize content for slow connections
         */
        optimizeForSlowConnection() {
            // Disable autoplay for videos
            document.querySelectorAll("video[autoplay]").forEach((video) => {
                video.removeAttribute("autoplay");
                video.setAttribute("data-autoplay-disabled", "true");
            });

            // Use low-quality images if available
            document.querySelectorAll("img[data-lq-src]").forEach((img) => {
                if (!img.hasAttribute("data-network-optimized")) {
                    img.src = img.getAttribute("data-lq-src");
                    img.setAttribute("data-network-optimized", "true");
                }
            });

            // Lazy load images more aggressively
            document.querySelectorAll('img[loading="eager"]').forEach((img) => {
                // Skip critical images (like logos)
                if (!img.hasAttribute("fetchpriority")) {
                    img.setAttribute("loading", "lazy");
                }
            });

            // Defer non-critical scripts
            document
                .querySelectorAll("script[data-defer-on-slow]")
                .forEach((script) => {
                    script.setAttribute("defer", "true");
                });

            // Add visual indicator for users
            this.showConnectionNotice("slow");

            if (this.config.debug) {
                console.log("🐌 Optimized for slow connection");
            }
        }

        /**
         * Optimize content for fast connections
         */
        optimizeForFastConnection() {
            // Preload critical resources
            const criticalResources = document.querySelectorAll(
                "[data-preload-on-fast]"
            );
            criticalResources.forEach((element) => {
                const href = element.getAttribute("data-preload-href");
                const as = element.getAttribute("data-preload-as") || "fetch";

                if (href) {
                    const link = document.createElement("link");
                    link.rel = "prefetch";
                    link.href = href;
                    link.as = as;
                    document.head.appendChild(link);
                }
            });

            if (this.config.debug) {
                console.log("🚀 Optimized for fast connection");
            }
        }

        /**
         * Show connection notice to users
         */
        showConnectionNotice(type) {
            // Check if notice already exists
            if (document.getElementById("network-notice")) return;

            const notices = {
                slow: {
                    message:
                        "We detected a slow connection. Content has been optimized for faster loading.",
                    class: "bg-yellow-100 border-yellow-400 text-yellow-700",
                },
                "save-data": {
                    message:
                        "Data saver mode is enabled. We've reduced data usage.",
                    class: "bg-blue-100 border-blue-400 text-blue-700",
                },
            };

            const notice = notices[type];
            if (!notice) return;

            // Create notice element
            const noticeEl = document.createElement("div");
            noticeEl.id = "network-notice";
            noticeEl.className = `fixed bottom-4 right-4 max-w-sm p-4 border-l-4 rounded shadow-lg z-50 ${notice.class}`;
            noticeEl.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium">${notice.message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-3 flex-shrink-0">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        `;

            document.body.appendChild(noticeEl);

            // Auto-hide after 10 seconds
            setTimeout(() => {
                noticeEl.remove();
            }, 10000);
        }

        /**
         * Monitor connection changes
         */
        monitorConnection() {
            if (!this.connection) return;

            this.connection.addEventListener("change", () => {
                if (this.config.debug) {
                    console.log(
                        `📶 Connection changed to: ${this.connection.effectiveType}`
                    );
                }

                this.loadConditionally();
            });
        }

        /**
         * Log connection information (debug)
         */
        logConnectionInfo() {
            if (!this.connection) {
                console.log("📶 Network Information API not supported");
                return;
            }

            console.group("📶 Network Connection Info");
            console.log("Effective Type:", this.connection.effectiveType);
            console.log("Downlink:", this.connection.downlink, "Mbps");
            console.log("RTT:", this.connection.rtt, "ms");
            console.log("Save Data:", this.connection.saveData);
            console.groupEnd();
        }

        /**
         * Get current connection info
         */
        getConnectionInfo() {
            if (!this.connection) return null;

            return {
                effectiveType: this.connection.effectiveType,
                downlink: this.connection.downlink,
                rtt: this.connection.rtt,
                saveData: this.connection.saveData,
                isSlowConnection: this.isSlowConnection(),
            };
        }

        /**
         * Enable/disable network-aware loading
         */
        setEnabled(enabled) {
            this.config.enabled = enabled;
        }

        /**
         * Enable/disable debug logging
         */
        setDebug(debug) {
            this.config.debug = debug;
        }
    }

    // Initialize network-aware loader
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => {
            if (!window.networkAwareLoader) {
                window.networkAwareLoader = new NetworkAwareLoader();
                window.networkAwareLoader.init();
            }
        });
    } else {
        if (!window.networkAwareLoader) {
            window.networkAwareLoader = new NetworkAwareLoader();
            window.networkAwareLoader.init();
        }
    }

    // Export for potential module usage
    if (typeof module !== "undefined" && module.exports) {
        module.exports = NetworkAwareLoader;
    }
} // End of NetworkAwareLoader guard
