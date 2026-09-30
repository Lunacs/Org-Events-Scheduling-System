<x-layouts.guest>
    <div class="w-full max-w-md space-y-6 text-center">
        <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-warning/15 text-warning">
            <i class="fa-solid fa-lock text-xl" aria-hidden="true"></i>
        </div>

        <div class="space-y-2">
            <h1 class="text-2xl font-semibold font-heading text-base-content">No pages assigned yet</h1>
            <p class="text-sm text-base-content/70">
                Your account's role does not have access to any pages. Please contact a system administrator to
                have permissions assigned to your role.
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-full">Sign out</button>
        </form>
    </div>
</x-layouts.guest>
