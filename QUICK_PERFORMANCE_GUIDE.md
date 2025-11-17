# Quick Performance Guide - Making Livewire Feel Like React

## 🚀 Quick Wins (Copy & Paste Ready)

### 1. Instant Navigation

```blade
<!-- Add to ALL internal links -->
<a href="/dashboard" wire:navigate.prefetch>Dashboard</a>

<!-- For Mary UI components -->
<x-mary-menu-item link="/dashboard" wire:navigate.prefetch />
```

### 2. Persist Static Elements

```blade
<!-- Wrap navigation/sidebar to prevent re-render -->
@persist('navigation')
    <livewire:layout.navigation />
@endpersist
```

### 3. Skeleton Loaders (Instead of opacity)

```blade
<!-- ❌ DON'T DO THIS -->
<div wire:loading.class="opacity-50">
    <table>...</table>
</div>

<!-- ✅ DO THIS -->
<div wire:loading.delay>
    <div class="animate-pulse">
        <div class="h-12 bg-gray-200 rounded"></div>
        <div class="h-12 bg-gray-200 rounded"></div>
    </div>
</div>

<div wire:loading.remove.delay>
    <table>...</table>
</div>
```

### 4. Smooth Page Transitions

```blade
<!-- Wrap your page content -->
<div
    x-data="{ show: false }"
    x-init="setTimeout(() => show = true, 50)"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
>
    <!-- Your page content -->
</div>
```

### 5. Lazy Load Heavy Components

```php
use Livewire\Attributes\Lazy;

#[Lazy]
class HeavyComponent extends Component
{
    public function placeholder()
    {
        return <<<'HTML'
        <div class="animate-pulse">
            <div class="h-96 bg-gray-200 rounded"></div>
        </div>
        HTML;
    }
}
```

### 6. Instant Search Feedback

```blade
<div x-data="{ searching: false }">
    <input
        wire:model.live.debounce.300ms="search"
        @input="searching = true"
        x-on:livewire:updated="searching = false" />

    <div x-show="searching" x-cloak>
        <span class="loading loading-spinner"></span>
        Searching...
    </div>
</div>
```

### 7. Optimize Vite Config

````javascript
### 7. Optimize Vite Config
```javascript
// vite.config.js
export default defineConfig({
    build: {
        minify: "esbuild", // Fast and built-in
        chunkSizeWarningLimit: 1000,
        target: "es2015",
    },
    server: {
        hmr: { overlay: false },
    },
});
````

**Note:** Livewire v3 includes Alpine.js - no need to install separately!

````

### 8. Cache Computed Properties

```php
#[Computed(persist: true, seconds: 600)]
public function expensiveData()
{
    return Cache::remember('key', 600, function() {
        return DB::table('users')->get();
    });
}
````

---

## 📋 Checklist for Each Page

-   [ ] Add `wire:navigate.prefetch` to all links
-   [ ] Wrap static elements with `@persist`
-   [ ] Replace opacity loading with skeleton screens
-   [ ] Add Alpine.js page transitions
-   [ ] Use `#[Lazy]` for heavy components
-   [ ] Add instant feedback for user actions
-   [ ] Cache computed properties with `#[Computed]`
-   [ ] Optimize database queries (select specific columns, eager load)

---

## 🎯 Performance Targets

| Metric              | Target            |
| ------------------- | ----------------- |
| Perceived page load | < 200ms           |
| Navigation speed    | Instant (< 100ms) |
| Search response     | < 500ms           |
| Skeleton to content | < 300ms           |

---

## 🔥 Common Mistakes to Avoid

### ❌ Don't

```blade
<!-- Opacity fades on everything -->
<div wire:loading.class="opacity-50">

<!-- No prefetching -->
<a href="/page" wire:navigate>

<!-- Re-rendering navigation on every page -->
<livewire:navigation />
```

### ✅ Do

```blade
<!-- Skeleton screens -->
<div wire:loading.delay>
    <div class="animate-pulse">...</div>
</div>

<!-- Prefetch on hover -->
<a href="/page" wire:navigate.prefetch>

<!-- Persist navigation -->
@persist('nav')
    <livewire:navigation />
@endpersist
```

---

## 💡 Pro Tips

1. **Use `wire:loading.delay`** to prevent flicker on fast connections
2. **Match skeleton structure** to actual content layout
3. **Prefetch frequently visited pages** (Dashboard, Reports, etc.)
4. **Cache organization/user lists** - they rarely change
5. **Use Alpine.js for instant client feedback** before Livewire responds
6. **Target wire:loading** to specific actions for better UX
7. **Add loading states to buttons** - users need feedback

---

## 🧪 Testing Performance

```javascript
// In browser console
performance.mark("start");
// Click a link
performance.mark("end");
performance.measure("navigation", "start", "end");
console.log(performance.getEntriesByType("measure"));
```

---

## 📚 Learn More

-   Full documentation: `OSA_SPA_OPTIMIZATIONS.md`
-   Livewire docs: https://livewire.laravel.com/docs/navigate
-   Alpine.js: https://alpinejs.dev

---

**Remember:** The goal is **perceived performance**, not just actual speed.
Users don't mind waiting if they know what's happening! 🎯
