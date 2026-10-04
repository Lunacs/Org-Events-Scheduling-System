<x-layouts.guest>
    <div class="space-y-6">
        <div>
            <x-ui.icon name="o-lock-closed" class="mb-3 size-6 text-base-content/60" aria-hidden="true" />
            <h1 class="text-2xl font-semibold tracking-[-0.015em] text-base-content">No pages assigned yet</h1>
            <p class="mt-1.5 text-sm text-base-content/70">
                Your account's role doesn't include access to any pages yet. Ask a system administrator to assign
                permissions to your role, then sign in again.
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-block">Sign out</button>
        </form>
    </div>
</x-layouts.guest>
