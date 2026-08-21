{{--
    x-ui.toast — replacement for MaryUI's `x-mary-toast`.

    A single Alpine host, placed once per layout, that listens for the
    `toast-show` browser event dispatched by App\Support\Concerns\InteractsWithToasts.
    Each payload is pushed onto a stack, grouped by its requested DaisyUI position,
    rendered as DaisyUI toast/alert markup, and auto-dismissed after its timeout.

    The host initializes via Alpine `init()`, so it (re)binds correctly after a
    `wire:navigate` SPA navigation. Transitions are suppressed under
    prefers-reduced-motion via the `motion-reduce:` utilities.

    Payload contract (see design.md "Toast payload contract"):
      { type, message, description, position, icon, css, timeout, noProgress, progressClass }
--}}
@props(['position' => 'toast-top toast-end'])

<div x-cloak x-data="{
    toasts: [],
    nextId: 0,
    defaultPosition: @js($position),

    init() {
        // Reset stack on (re)initialization, including after wire:navigate.
        this.toasts = [];
    },

    add(detail) {
        const id = ++this.nextId;
        const timeout = Number(detail?.timeout ?? 3000);

        const toast = {
            id,
            type: detail?.type ?? 'info',
            message: detail?.message ?? '',
            description: detail?.description ?? null,
            position: detail?.position || this.defaultPosition,
            css: detail?.css ?? 'alert-info',
            timeout,
            noProgress: detail?.noProgress ?? false,
            visible: false,
        };

        this.toasts.push(toast);

        // Delay so the enter transition runs after DOM insertion.
        requestAnimationFrame(() => {
            const item = this.toasts.find(t => t.id === id);
            if (item) { item.visible = true; }
        });

        if (timeout > 0) {
            setTimeout(() => this.dismiss(id), timeout);
        }
    },

    dismiss(id) {
        const item = this.toasts.find(t => t.id === id);
        if (!item) { return; }

        item.visible = false;

        // Remove after the leave transition completes.
        setTimeout(() => {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }, 300);
    },

    positions() {
        return [...new Set(this.toasts.map(t => t.position))];
    },

    toastsFor(position) {
        return this.toasts.filter(t => t.position === position);
    },
}" @toast-show.window="add($event.detail)">
    <template x-for="position in positions()" :key="position">
        <div class="toast z-999 whitespace-normal" :class="position">
            <template x-for="toast in toastsFor(position)" :key="toast.id">
                <div role="alert" aria-live="assertive" aria-atomic="true" class="alert gap-2 shadow-lg"
                    :class="toast.css" x-show="toast.visible"
                    x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200 motion-reduce:transition-none"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2">
                    <span class="hidden shrink-0 sm:inline-block">
                        <x-ui.icon name="o-check-circle" class="h-6 w-6" x-show="toast.type === 'success'" />
                        <x-ui.icon name="o-x-circle" class="h-6 w-6" x-show="toast.type === 'error'" />
                        <x-ui.icon name="o-exclamation-triangle" class="h-6 w-6" x-show="toast.type === 'warning'" />
                        <x-ui.icon name="o-information-circle" class="h-6 w-6" x-show="toast.type === 'info'" />
                    </span>

                    <div class="grid">
                        <span class="font-bold" x-text="toast.message"></span>
                        <span class="text-xs" x-show="toast.description" x-text="toast.description"></span>
                    </div>

                    <button type="button" class="btn btn-circle btn-ghost btn-xs" aria-label="Dismiss notification"
                        @click="dismiss(toast.id)">
                        <x-ui.icon name="o-x-mark" class="h-4 w-4" />
                    </button>
                </div>
            </template>
        </div>
    </template>
</div>
