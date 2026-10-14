{{--
    x-ui.toast — one Alpine host per layout (DESIGN.md "Feedback").

    Listens for the `toast-show` browser event dispatched by
    App\Support\Concerns\InteractsWithToasts. Toasts are paper-white cards with a
    status-colored icon, announced politely (errors assertively), auto-dismissed after
    their timeout, paused while hovered or focused, and dismissible.

    Payload contract:
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
        const type = ['success', 'error', 'warning', 'info'].includes(detail?.type) ? detail.type : 'info';
        const toast = {
            id,
            type,
            message: detail?.message ?? '',
            description: detail?.description ?? null,
            position: detail?.position || this.defaultPosition,
            remaining: Number(detail?.timeout ?? (type === 'error' ? 6000 : 4000)),
            timer: null,
            startedAt: 0,
            visible: false,
        };

        this.toasts.push(toast);
        requestAnimationFrame(() => {
            const item = this.find(id);
            if (item) { item.visible = true; this.resume(id); }
        });
    },

    find(id) {
        return this.toasts.find(t => t.id === id);
    },

    resume(id) {
        const item = this.find(id);
        if (!item || item.remaining <= 0) { return; }
        item.startedAt = Date.now();
        item.timer = setTimeout(() => this.dismiss(id), item.remaining);
    },

    pause(id) {
        const item = this.find(id);
        if (!item || !item.timer) { return; }
        clearTimeout(item.timer);
        item.timer = null;
        item.remaining -= Date.now() - item.startedAt;
    },

    dismiss(id) {
        const item = this.find(id);
        if (!item) { return; }
        clearTimeout(item.timer);
        item.visible = false;
        setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 200);
    },

    positions() {
        return [...new Set(this.toasts.map(t => t.position))];
    },

    toastsFor(position) {
        return this.toasts.filter(t => t.position === position);
    },
}" @toast-show.window="add($event.detail)">
    <template x-for="position in positions()" :key="position">
        <div class="toast z-60 w-[min(24rem,calc(100vw-1rem))] whitespace-normal p-3 sm:p-4" :class="position">
            <template x-for="toast in toastsFor(position)" :key="toast.id">
                <div :role="toast.type === 'error' ? 'alert' : 'status'"
                    :aria-live="toast.type === 'error' ? 'assertive' : 'polite'" aria-atomic="true"
                    class="flex w-full items-start gap-3 rounded-box border border-base-300 bg-base-100 p-3.5 text-base-content shadow-overlay"
                    x-show="toast.visible" @mouseenter="pause(toast.id)" @mouseleave="resume(toast.id)"
                    @focusin="pause(toast.id)" @focusout="resume(toast.id)"
                    x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
                    x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <span class="mt-0.5 shrink-0" aria-hidden="true">
                        <x-ui.icon name="s-check-circle" class="size-5 text-success" x-show="toast.type === 'success'" />
                        <x-ui.icon name="s-x-circle" class="size-5 text-error" x-show="toast.type === 'error'" />
                        <x-ui.icon name="s-exclamation-triangle" class="size-5 text-warning" x-show="toast.type === 'warning'" />
                        <x-ui.icon name="s-information-circle" class="size-5 text-info" x-show="toast.type === 'info'" />
                    </span>

                    <div class="grid min-w-0 flex-1 gap-0.5">
                        <p class="text-sm font-semibold" x-text="toast.message"></p>
                        <p class="text-sm text-base-content/70" x-show="toast.description" x-text="toast.description"></p>
                    </div>

                    <button type="button" class="btn btn-ghost btn-xs btn-square -me-1 -mt-0.5" aria-label="Dismiss notification"
                        @click="dismiss(toast.id)">
                        <x-ui.icon name="o-x-mark" class="size-4" aria-hidden="true" />
                    </button>
                </div>
            </template>
        </div>
    </template>
</div>
