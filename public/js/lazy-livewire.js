/**
 * Lazy Loading for Livewire Components
 * Uses Intersection Observer API to lazy load components when they enter viewport
 */

// Prevent redeclaration if script is loaded multiple times
if (typeof LazyLivewireLoader === "undefined") {
    class LazyLivewireLoader {
        constructor() {
            this.config = {
                enabled: true,
                debug: false,
                rootMargin: "50px", // Load slightly before entering viewport
                threshold: 0.01, // Trigger when 1% of element is visible
            };
            this.observer = null;
            this.loadedComponents = new Set();
        }

        /**
         * Initialize lazy loading
         */
        init() {
            if (!this.config.enabled) return;

            // Check if Intersection Observer is supported
            if (!("IntersectionObserver" in window)) {
                if (this.config.debug) {
                    console.warn(
                        "Intersection Observer not supported, loading all components immediately"
                    );
                }
                this.loadAllComponents();
                return;
            }

            // Create intersection observer
            this.createObserver();

            // Observe all lazy components
            this.observeComponents();

            // Re-observe on Livewire navigation (for SPA behavior)
            this.setupLivewireHooks();

            if (this.config.debug) {
                console.log("🔭 Lazy Livewire Loader initialized");
            }
        }

        /**
         * Create Intersection Observer instance
         */
        createObserver() {
            this.observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            this.loadComponent(entry.target);
                        }
                    });
                },
                {
                    rootMargin: this.config.rootMargin,
                    threshold: this.config.threshold,
                }
            );
        }

        /**
         * Find and observe all lazy components
         */
        observeComponents() {
            const lazyComponents = document.querySelectorAll(
                "[data-lazy-livewire]"
            );

            lazyComponents.forEach((element) => {
                // Skip if already loaded
                if (this.loadedComponents.has(element)) return;

                this.observer.observe(element);

                if (this.config.debug) {
                    const componentName = element.dataset.lazyLivewire;
                    console.log(
                        `👁️ Observing lazy component: ${componentName}`
                    );
                }
            });
        }

        /**
         * Load a component when it enters viewport
         */
        loadComponent(element) {
            // Skip if already loaded
            if (this.loadedComponents.has(element)) return;

            const componentName = element.dataset.lazyLivewire;
            const componentParams = this.parseParams(
                element.dataset.lazyParams
            );

            if (this.config.debug) {
                console.log(`🚀 Loading lazy component: ${componentName}`);
            }

            // Mark as loaded
            this.loadedComponents.add(element);

            // Stop observing this element
            this.observer.unobserve(element);

            // Remove loading skeleton
            this.removeLoadingSkeleton(element);

            // Dispatch Livewire event to load component
            if (typeof Livewire !== "undefined") {
                // Method 1: Use Livewire wire:init (preferred)
                element.setAttribute("wire:init", "loadComponent");

                // Method 2: Dispatch custom event (alternative)
                Livewire.dispatch("load-lazy-component", {
                    component: componentName,
                    params: componentParams,
                    elementId: element.id || `lazy-${Date.now()}`,
                });
            }

            // Add loaded class for animations
            element.classList.add("lazy-loaded");

            // Trigger custom event
            element.dispatchEvent(
                new CustomEvent("lazy-loaded", {
                    detail: {
                        component: componentName,
                        params: componentParams,
                    },
                })
            );
        }

        /**
         * Parse component parameters from data attribute
         */
        parseParams(paramsString) {
            if (!paramsString) return {};

            try {
                return JSON.parse(paramsString);
            } catch (e) {
                console.error("Failed to parse lazy component params:", e);
                return {};
            }
        }

        /**
         * Remove loading skeleton and show actual component
         */
        removeLoadingSkeleton(element) {
            const skeleton = element.querySelector("[data-lazy-skeleton]");
            if (skeleton) {
                // Fade out skeleton
                skeleton.style.transition = "opacity 0.3s";
                skeleton.style.opacity = "0";

                setTimeout(() => {
                    skeleton.remove();
                }, 300);
            }
        }

        /**
         * Load all components immediately (fallback for unsupported browsers)
         */
        loadAllComponents() {
            const lazyComponents = document.querySelectorAll(
                "[data-lazy-livewire]"
            );
            lazyComponents.forEach((element) => {
                this.loadComponent(element);
            });
        }

        /**
         * Setup hooks for Livewire navigation
         */
        setupLivewireHooks() {
            if (typeof Livewire === "undefined") return;

            // Re-observe components after Livewire navigation
            document.addEventListener("livewire:navigated", () => {
                if (this.config.debug) {
                    console.log(
                        "🔄 Re-observing lazy components after navigation"
                    );
                }

                // Wait for DOM to settle
                setTimeout(() => {
                    this.observeComponents();
                }, 100);
            });
        }

        /**
         * Manually trigger loading of a specific component
         */
        loadComponentById(elementId) {
            const element = document.getElementById(elementId);
            if (element && element.hasAttribute("data-lazy-livewire")) {
                this.loadComponent(element);
            }
        }

        /**
         * Enable/disable lazy loading
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

        /**
         * Get loading status
         */
        getStatus() {
            return {
                enabled: this.config.enabled,
                loadedCount: this.loadedComponents.size,
                totalCount: document.querySelectorAll("[data-lazy-livewire]")
                    .length,
            };
        }
    }

    // Initialize lazy Livewire loader
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => {
            if (!window.lazyLivewireLoader) {
                window.lazyLivewireLoader = new LazyLivewireLoader();
                window.lazyLivewireLoader.init();
            }
        });
    } else {
        if (!window.lazyLivewireLoader) {
            window.lazyLivewireLoader = new LazyLivewireLoader();
            window.lazyLivewireLoader.init();
        }
    }

    // Export for potential module usage
    if (typeof module !== "undefined" && module.exports) {
        module.exports = LazyLivewireLoader;
    }
} // End of LazyLivewireLoader guard
