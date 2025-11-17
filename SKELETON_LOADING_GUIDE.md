# Skeleton Loading States Guide

This project implements **smart skeleton loading states** that show loading UI only when needed, not on every page refresh.

## 📋 Three Loading Strategies

### 1️⃣ **First-Time Navigation (Initial Load)**

Shows skeleton **only on first page visit**, not on subsequent refreshes.

```blade
<div x-data="{ firstLoad: true }" x-init="$nextTick(() => firstLoad = false)">
    {{-- Skeleton: Shows briefly on first load --}}
    <div x-show="firstLoad" x-cloak>
        @include('livewire.osa.placeholders.dashboard')
    </div>

    {{-- Real content: Fades in after skeleton --}}
    <div x-show="!firstLoad" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100">
        <!-- Your actual page content -->
    </div>
</div>
```

**Used in:**

-   Dashboard
-   Ticket Management
-   Archive
-   Notifications
-   Communication
-   Event Calendar
-   Ticket Review

---

### 2️⃣ **User Interactions (Filters, Search, etc.)**

Shows skeleton during **specific user actions** like filtering or searching.

```blade
{{-- Skeleton shown during filter operations --}}
<div wire:loading.delay wire:target="search,statusFilter,organizationFilter">
    <div class="animate-pulse">
        @for ($i = 0; $i < 5; $i++)
            <div class="h-16 bg-base-200 rounded mb-2"></div>
        @endfor
    </div>
</div>

{{-- Actual content hidden during loading --}}
<div wire:loading.remove.delay wire:target="search,statusFilter,organizationFilter">
    @forelse($items as $item)
        <!-- Your item content -->
    @empty
        <p>No items found</p>
    @endforelse
</div>
```

**Used in:**

-   Ticket Management (filter operations)
-   Archive (filter operations)
-   Ticket Review (search/filter with overlay)
-   Notifications (search/filter)
-   Reports (report generation)

---

### 3️⃣ **Network Issues / Offline State**

Shows skeleton when **connection is lost** (future implementation).

```blade
<div x-data="{
    isOnline: navigator.onLine,
    init() {
        window.addEventListener('online', () => this.isOnline = true);
        window.addEventListener('offline', () => this.isOnline = false);
    }
}">
    {{-- Offline skeleton --}}
    <div x-show="!isOnline" x-cloak>
        @include('livewire.osa.placeholders.offline')
        <div class="alert alert-warning">
            <span>No internet connection. Showing cached data.</span>
        </div>
    </div>

    {{-- Online content --}}
    <div x-show="isOnline" x-cloak>
        <!-- Your content -->
    </div>
</div>
```

**Status:** Not yet implemented (future enhancement)

---

## 🎨 Available Skeleton Placeholders

Located in `resources/views/livewire/osa/placeholders/`:

| File                          | Description                     | Used For             |
| ----------------------------- | ------------------------------- | -------------------- |
| `stats-grid.blade.php`        | 4-column stats cards            | Dashboard statistics |
| `table.blade.php`             | Table with filters & pagination | Data tables          |
| `dashboard.blade.php`         | Stats + charts + activities     | Full dashboard       |
| `ticket-management.blade.php` | Header + table skeleton         | Ticket pages         |
| `notifications.blade.php`     | Notification card list          | Notifications        |
| `communication.blade.php`     | Form skeleton                   | Communication form   |
| `event-calendar.blade.php`    | Calendar grid skeleton          | Event calendar       |

---

## 🔧 Implementation Tips

### Using `wire:loading.delay`

The `.delay` modifier prevents flashing skeletons on fast operations:

```blade
{{-- Only shows skeleton if loading takes >200ms --}}
<div wire:loading.delay wire:target="search">
    <!-- Skeleton -->
</div>
```

### Multiple Loading Targets

Target multiple actions with comma-separated list:

```blade
<div wire:loading wire:target="search,statusFilter,organizationFilter,clearFilters">
    <!-- Shows when ANY of these are loading -->
</div>
```

### Alpine.js `x-cloak`

Prevents flash of unstyled content before Alpine initializes:

```blade
<div x-show="someCondition" x-cloak>
    <!-- Won't show until Alpine is ready -->
</div>
```

Add to your CSS:

```css
[x-cloak] {
    display: none !important;
}
```

---

## ✅ Benefits

-   ✅ **No skeleton spam** - Only shows when truly needed
-   ✅ **Smooth UX** - Elegant transitions instead of jarring loads
-   ✅ **Fast refreshes** - No unnecessary loading states
-   ✅ **Smart loading** - Context-aware based on user action
-   ✅ **Mobile-friendly** - Works great on slow connections
-   ✅ **SPA-like** - Feels instant with Livewire's wire:navigate

---

## 🚀 Future Enhancements

-   [ ] Add offline state detection
-   [ ] Progressive loading for large datasets
-   [ ] Skeleton shimmer animations
-   [ ] Custom skeletons for specific components
-   [ ] Loading state persistence across page navigation
