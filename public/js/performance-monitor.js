/**
 * Performance Monitoring Utility
 * Monitors and reports web performance metrics for the application
 * Based on MDN Web Performance APIs
 */

class PerformanceMonitor {
    constructor() {
        this.metrics = {};
        this.config = {
            enabled: true,
            debug: false, // Set to true for console logging
            slowThreshold: {
                request: 1000, // 1 second
                resource: 500, // 500ms
                livewire: 1000, // 1 second
            },
        };
    }

    /**
     * Initialize performance monitoring
     */
    init() {
        if (!this.config.enabled) return;

        // Wait for page to fully load
        if (document.readyState === 'complete') {
            this.measurePageLoad();
            this.measureResources();
        } else {
            window.addEventListener('load', () => {
                this.measurePageLoad();
                this.measureResources();
            });
        }

        // Monitor Livewire requests
        this.monitorLivewireRequests();

        // Monitor long tasks (if supported)
        this.observeLongTasks();

        // Monitor largest contentful paint
        this.observeLargestContentfulPaint();
    }

    /**
     * Measure page load performance using Navigation Timing API
     */
    measurePageLoad() {
        if (!window.performance || !window.performance.timing) return;

        const timing = performance.timing;
        const navigation = performance.navigation;

        this.metrics.navigation = {
            // Page load metrics
            pageLoadTime: timing.loadEventEnd - timing.navigationStart,
            domContentLoaded: timing.domContentLoadedEventEnd - timing.navigationStart,
            timeToFirstByte: timing.responseStart - timing.navigationStart,
            domInteractive: timing.domInteractive - timing.navigationStart,
            domComplete: timing.domComplete - timing.navigationStart,

            // Network metrics
            dnsLookup: timing.domainLookupEnd - timing.domainLookupStart,
            tcpConnection: timing.connectEnd - timing.connectStart,
            serverResponse: timing.responseEnd - timing.requestStart,
            pageRender: timing.loadEventEnd - timing.responseEnd,

            // Navigation type
            navigationType: this.getNavigationType(navigation.type),
            redirectCount: navigation.redirectCount,
        };

        if (this.config.debug) {
            console.group('📊 Page Load Performance');
            console.table(this.metrics.navigation);
            console.groupEnd();
        }

        // Send to analytics
        this.sendToAnalytics('page_load', this.metrics.navigation);
    }

    /**
     * Measure resource loading performance
     */
    measureResources() {
        if (!window.performance || !window.performance.getEntriesByType) return;

        const resources = performance.getEntriesByType('resource');
        const resourceMetrics = {
            total: resources.length,
            slow: [],
            byType: {},
        };

        resources.forEach((resource) => {
            // Track slow resources
            if (resource.duration > this.config.slowThreshold.resource) {
                resourceMetrics.slow.push({
                    name: resource.name,
                    type: resource.initiatorType,
                    duration: Math.round(resource.duration),
                });
            }

            // Group by type
            const type = resource.initiatorType || 'other';
            if (!resourceMetrics.byType[type]) {
                resourceMetrics.byType[type] = { count: 0, totalDuration: 0 };
            }
            resourceMetrics.byType[type].count++;
            resourceMetrics.byType[type].totalDuration += resource.duration;
        });

        this.metrics.resources = resourceMetrics;

        if (this.config.debug && resourceMetrics.slow.length > 0) {
            console.group('⚠️ Slow Resources (>' + this.config.slowThreshold.resource + 'ms)');
            console.table(resourceMetrics.slow);
            console.groupEnd();
        }

        // Send slow resources to analytics
        if (resourceMetrics.slow.length > 0) {
            this.sendToAnalytics('slow_resources', resourceMetrics.slow);
        }
    }

    /**
     * Monitor Livewire requests for performance
     */
    monitorLivewireRequests() {
        if (typeof Livewire === 'undefined') return;

        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ uri, options, payload }) => {
                const startTime = performance.now();

                return () => {
                    const duration = performance.now() - startTime;

                    if (this.config.debug || duration > this.config.slowThreshold.livewire) {
                        const logLevel = duration > this.config.slowThreshold.livewire ? 'warn' : 'log';
                        console[logLevel](`🔄 Livewire request: ${uri} (${Math.round(duration)}ms)`);
                    }

                    // Track slow Livewire requests
                    if (duration > this.config.slowThreshold.livewire) {
                        this.sendToAnalytics('slow_livewire', {
                            uri,
                            duration: Math.round(duration),
                            timestamp: Date.now(),
                        });
                    }
                };
            });
        });
    }

    /**
     * Observe Long Tasks (requires PerformanceObserver API)
     */
    observeLongTasks() {
        if (!window.PerformanceObserver) return;

        try {
            const observer = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (this.config.debug) {
                        console.warn(`⏱️ Long Task detected: ${Math.round(entry.duration)}ms`);
                    }

                    this.sendToAnalytics('long_task', {
                        duration: Math.round(entry.duration),
                        startTime: Math.round(entry.startTime),
                    });
                }
            });

            observer.observe({ entryTypes: ['longtask'] });
        } catch (e) {
            // Long task API not supported
            if (this.config.debug) {
                console.log('Long Task API not supported');
            }
        }
    }

    /**
     * Observe Largest Contentful Paint (LCP)
     */
    observeLargestContentfulPaint() {
        if (!window.PerformanceObserver) return;

        try {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lastEntry = entries[entries.length - 1];

                if (this.config.debug) {
                    console.log(`🎨 Largest Contentful Paint: ${Math.round(lastEntry.renderTime || lastEntry.loadTime)}ms`);
                }

                this.sendToAnalytics('lcp', {
                    lcp: Math.round(lastEntry.renderTime || lastEntry.loadTime),
                    element: lastEntry.element?.tagName || 'unknown',
                });
            });

            observer.observe({ entryTypes: ['largest-contentful-paint'] });
        } catch (e) {
            // LCP API not supported
        }
    }

    /**
     * Get navigation type as readable string
     */
    getNavigationType(type) {
        const types = {
            0: 'navigate',
            1: 'reload',
            2: 'back_forward',
            255: 'reserved',
        };
        return types[type] || 'unknown';
    }

    /**
     * Send metrics to analytics endpoint
     * You can customize this to send to your backend or third-party service
     */
    sendToAnalytics(eventType, data) {
        // Option 1: Use Navigator.sendBeacon for reliable sending
        if (navigator.sendBeacon && this.config.enabled) {
            const payload = JSON.stringify({
                event: eventType,
                data: data,
                timestamp: Date.now(),
                url: window.location.href,
                userAgent: navigator.userAgent,
            });

            // Send to your Laravel backend endpoint (you'll need to create this route)
            navigator.sendBeacon('/api/performance-metrics', payload);
        }

        // Option 2: Use Livewire to log to database (alternative)
        // if (typeof Livewire !== 'undefined') {
        //     Livewire.dispatch('log-performance', { type: eventType, metrics: data });
        // }
    }

    /**
     * Get current performance metrics
     */
    getMetrics() {
        return this.metrics;
    }

    /**
     * Enable/disable monitoring
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

// Initialize performance monitor when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.performanceMonitor = new PerformanceMonitor();
        window.performanceMonitor.init();
    });
} else {
    window.performanceMonitor = new PerformanceMonitor();
    window.performanceMonitor.init();
}

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PerformanceMonitor;
}

