import { createAvatar } from "@dicebear/core";
import { bigEars } from "@dicebear/collection";

// Available avatar styles (optimized selection)
// - big-ears: 1.2 trillion unique avatars
const avatarStyles = {
    "big-ears": bigEars,
};

/**
 * Generate a DiceBear avatar as SVG data URI
 * @param {string} style - The avatar style (only 'big-ears' supported)
 * @param {string} seed - The seed for consistent avatar generation
 * @param {object} options - Additional options for avatar generation
 * @returns {string} - SVG data URI
 */
export function generateAvatar(
    style = "big-ears",
    seed = "default",
    options = {}
) {
    const collection = avatarStyles[style] || avatarStyles["big-ears"];

    const avatar = createAvatar(collection, {
        seed,
        ...options,
    });

    return avatar.toDataUri();
}

/**
 * Get list of available avatar styles
 * @returns {Array} - Array of style names
 */
export function getAvailableStyles() {
    return Object.keys(avatarStyles);
}

// Cache for generated avatars to improve performance
const avatarCache = new Map();

/**
 * Initialize avatar rendering for elements with data-avatar attribute
 * Format: data-avatar="style:seed"
 * Uses caching for better performance
 */
export function initAvatars() {
    const elements = document.querySelectorAll("[data-avatar]");
    console.log(
        `[Avatar Init] Found ${elements.length} avatar elements to initialize`
    );

    // Process immediately without requestAnimationFrame for faster response
    elements.forEach((element) => {
        // Skip if already initialized (only when it has the same data-avatar value)
        const avatarData = element.getAttribute("data-avatar");

        if (!avatarData) {
            console.warn("[Avatar Init] Element missing data-avatar:", element);
            return;
        }

        // Check if this specific avatar is already initialized with the same data
        // AND the image is already loaded (has src or background-image)
        const isImgTag = element.tagName === "IMG";
        const hasLoadedContent = isImgTag
            ? element.src &&
              element.src !==
                  "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'%3E%3C/svg%3E"
            : element.style.backgroundImage &&
              element.style.backgroundImage !== "";

        if (
            element.dataset.initialized === "true" &&
            element.dataset.currentAvatar === avatarData &&
            hasLoadedContent
        ) {
            console.log(`[Avatar Init] Skipping already loaded: ${avatarData}`);
            return;
        }

        // Parse format: "dicebear:style:seed" or "style:seed" or direct URL
        // If it's a direct URL (uploaded photo), use it directly
        const isDirectUrl = avatarData.startsWith('https') || avatarData.startsWith('http') || avatarData.startsWith('/storage');
        
        if (isDirectUrl) {
            // Direct URL - use it as-is
            console.log(`[Avatar Init] Using uploaded photo:`, avatarData);
            
            if (element.tagName === "IMG") {
                element.src = avatarData;
                element.style.opacity = "1";
            } else {
                element.style.backgroundImage = `url(${avatarData})`;
                element.style.backgroundSize = "cover";
                element.style.backgroundPosition = "center";
            }
            
            // Mark as initialized
            element.dataset.initialized = "true";
            element.dataset.currentAvatar = avatarData;
            return;
        }
        
        // DiceBear format - parse and generate
        const parts = avatarData.replace("dicebear:", "").split(":");
        const style = parts[0] || "big-ears";
        const seed = parts[1] || "default";
        const cacheKey = `${style}:${seed}`;

        try {
            // Check cache first
            let avatarUrl = avatarCache.get(cacheKey);

            if (!avatarUrl) {
                // Generate and cache the avatar
                avatarUrl = generateAvatar(style, seed);
                avatarCache.set(cacheKey, avatarUrl);
                console.log(`[Avatar Init] Generated & cached: ${cacheKey}`);
            } else {
                console.log(`[Avatar Init] Using cached: ${cacheKey}`);
            }

            // Set the avatar immediately
            if (element.tagName === "IMG") {
                element.src = avatarUrl;
                element.style.opacity = "1";
            } else {
                element.style.backgroundImage = `url(${avatarUrl})`;
                element.style.backgroundSize = "cover";
                element.style.backgroundPosition = "center";
            }

            // Mark as initialized with current avatar data
            element.dataset.initialized = "true";
            element.dataset.currentAvatar = avatarData;
        } catch (error) {
            console.error(
                `[Avatar Init] Error generating avatar for ${cacheKey}:`,
                error
            );
        }
    });
}

/**
 * Clear avatar cache (useful when user changes avatar)
 */
export function clearAvatarCache() {
    avatarCache.clear();
    console.log("[Avatar Cache] Cleared");
}

// Auto-initialize on DOM ready
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAvatars);
} else {
    initAvatars();
}

// Re-initialize when Livewire updates the DOM
document.addEventListener("livewire:navigated", () => {
    console.log("[Avatar] Page navigated, checking for new avatars...");
    // Don't reinitialize all - only new ones
    initAvatars();
});

document.addEventListener("livewire:update", () => {
    console.log("[Avatar] Livewire updated, checking for new avatars...");
    initAvatars();
});

// Only clear cache and full reinit when user explicitly changes avatar
window.addEventListener("avatar-changed", () => {
    console.log("[Avatar] User changed avatar, full refresh...");
    clearAvatarCache();
    document.querySelectorAll("[data-avatar]").forEach((el) => {
        el.dataset.initialized = "false";
    });
    setTimeout(() => initAvatars(), 100);
});

// Listen for navigation refresh
window.addEventListener("navigation-refresh", () => {
    console.log("[Avatar] Navigation refresh triggered");
    // Don't clear cache - only reinitialize new/changed avatars
    document.querySelectorAll("[data-avatar]").forEach((el) => {
        const currentData = el.dataset.currentAvatar;
        const newData = el.getAttribute("data-avatar");
        // Only reset if avatar data changed
        if (currentData !== newData) {
            el.dataset.initialized = "false";
        }
    });
    setTimeout(() => initAvatars(), 50);
});

// Export for global use
window.AvatarHelper = {
    generateAvatar,
    getAvailableStyles,
    initAvatars,
    clearAvatarCache,
};
