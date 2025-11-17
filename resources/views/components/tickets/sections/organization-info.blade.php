@props(['ticket'])

<div class="bg-base-100 rounded-box shadow-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
            <x-mary-icon name="o-building-office-2" class="w-5 h-5" />
            Organization Information
        </h2>
        @if ($ticket->user->studentOrganization)
            <img src="{{ $ticket->user->studentOrganization->logo_url }}"
                alt="{{ $ticket->user->studentOrganization->org_name }} logo" class="w-20 h-20 object-cover rounded-lg">
        @endif
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-base-content/70">Organization Name</label>
            <p class="text-base-content font-medium">
                {{ $ticket->user?->studentOrganization?->org_name ?? 'No Organization' }}</p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Organization Course</label>
            <p class="text-base-content">
                {{ $ticket->user?->studentOrganization?->course?->course_name ?? 'N/A' }}</p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Name of Proponent</label>
            <p class="text-base-content">{{ $ticket->user?->name }}</p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Proponent Position</label>
            <p class="text-base-content">{{ $ticket->user?->position?->position_name ?? 'N/A' }}</p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Contact Email</label>
            <p class="text-base-content">{{ $ticket->user?->email }}</p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Proponent Contact</label>
            <p class="text-base-content">{{ $ticket->proponent_contact ?? 'N/A' }}</p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Organization Adviser</label>
            <p class="text-base-content">
                {{ $ticket->user?->studentOrganization?->adviser_name ?? 'N/A' }}</p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Adviser Contact</label>
            <p class="text-base-content">{{ $ticket->adviser_contact ?? 'N/A' }}</p>
        </div>
    </div>
</div>
