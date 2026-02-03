@props(['ticket'])

@php
    $userDeleted = $ticket->user?->trashed();
    $orgDeleted = $ticket->user?->studentOrganization?->trashed();
@endphp

<div class="bg-base-100 rounded-box shadow-lg p-4 md:p-6 overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
        <h2 class="text-lg md:text-xl font-bold text-base-content flex items-center gap-2">
            <x-mary-icon name="o-building-office-2" class="w-5 h-5 flex-shrink-0" />
            <span class="break-words">Organization Information</span>
            @if ($userDeleted || $orgDeleted)
                <span class="badge badge-warning badge-sm">Archived</span>
            @endif
        </h2>
        @if ($ticket->user?->studentOrganization && !$orgDeleted)
            <img src="{{ $ticket->user->studentOrganization->logo_url }}"
                alt="{{ $ticket->user->studentOrganization->org_name }} logo"
                class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-lg flex-shrink-0">
        @elseif ($orgDeleted)
            <div
                class="w-16 h-16 md:w-20 md:h-20 bg-base-200 rounded-lg flex-shrink-0 flex items-center justify-center">
                <x-mary-icon name="o-building-office" class="w-8 h-8 text-base-content/30" />
            </div>
        @endif
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 overflow-hidden">
        <div class="min-w-0">
            <label class="text-sm font-medium text-base-content/70">Organization Name</label>
            <p class="text-base-content font-medium break-words">
                @if ($orgDeleted)
                    <span class="text-base-content/50 italic">Deleted Organization</span>
                @else
                    {{ $ticket->user?->studentOrganization?->org_name ?? 'No Organization' }}
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <label class="text-sm font-medium text-base-content/70">Organization Course</label>
            <p class="text-base-content break-words">
                @if ($orgDeleted)
                    <span class="text-base-content/50 italic">N/A</span>
                @else
                    {{ $ticket->user?->studentOrganization?->course?->course_name ?? 'N/A' }}
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <label class="text-sm font-medium text-base-content/70">Name of Proponent</label>
            <p class="text-base-content break-words">
                @if ($userDeleted)
                    <span class="text-base-content/50 italic">Deleted User</span>
                @else
                    {{ $ticket->user?->name }}
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <label class="text-sm font-medium text-base-content/70">Proponent Position</label>
            <p class="text-base-content break-words">
                @if ($userDeleted)
                    <span class="text-base-content/50 italic">N/A</span>
                @else
                    {{ $ticket->user?->position?->position_name ?? 'N/A' }}
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <label class="text-sm font-medium text-base-content/70">Contact Email</label>
            <p class="text-base-content break-all">
                @if ($userDeleted)
                    <span class="text-base-content/50 italic">N/A</span>
                @else
                    {{ $ticket->user?->email }}
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <label class="text-sm font-medium text-base-content/70">Proponent Contact</label>
            <p class="text-base-content break-words">{{ $ticket->proponent_contact ?? 'N/A' }}</p>
        </div>

        <div class="min-w-0">
            <label class="text-sm font-medium text-base-content/70">Organization Adviser</label>
            <p class="text-base-content break-words">
                @if ($orgDeleted)
                    <span class="text-base-content/50 italic">N/A</span>
                @else
                    {{ $ticket->user?->studentOrganization?->adviser_name ?? 'N/A' }}
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <label class="text-sm font-medium text-base-content/70">Adviser Contact</label>
            <p class="text-base-content break-words">{{ $ticket->adviser_contact ?? 'N/A' }}</p>
        </div>
    </div>
</div>
