@props(['ticket'])

@php
    $userDeleted = $ticket->user?->trashed();
    $orgDeleted = $ticket->user?->studentOrganization?->trashed();
@endphp

<div class="overflow-hidden rounded-box border border-base-300 bg-base-100 p-4 sm:p-5">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
        <h2 class="text-base font-semibold text-base-content flex items-center gap-2">
            <x-ui.icon name="o-building-office-2" class="size-4 shrink-0 text-base-content/60" aria-hidden="true" />
            <span class="break-words">Organization Information</span>
            @if ($userDeleted || $orgDeleted)
                <span class="badge badge-soft badge-warning badge-sm">Archived</span>
            @endif
        </h2>
        @if ($ticket->user?->studentOrganization && !$orgDeleted)
            <img src="{{ $ticket->user->studentOrganization->logo_url }}"
                alt="{{ $ticket->user->studentOrganization->org_name }} logo"
                class="size-12 shrink-0 rounded-field object-cover" width="48" height="48" loading="lazy">
        @elseif ($orgDeleted)
            <div
                class="flex size-12 shrink-0 items-center justify-center rounded-field bg-base-200">
                <x-ui.icon name="o-building-office" class="w-8 h-8 text-base-content/30" />
            </div>
        @endif
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 overflow-hidden">
        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Organization Name</p>
            <p class="text-base-content font-medium break-words">
                @if ($orgDeleted)
                    <span class="text-base-content/60 italic">Deleted Organization</span>
                @else
                    {{ $ticket->user?->studentOrganization?->org_name ?? 'No Organization' }}
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Organization Course</p>
            <p class="text-base-content break-words">
                @if ($orgDeleted)
                    <span class="text-base-content/60 italic">N/A</span>
                @else
                    {{ $ticket->user?->studentOrganization?->course?->course_name ?? 'N/A' }}
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Name of Proponent</p>
            <p class="text-base-content break-words">
                @if ($userDeleted)
                    <span class="text-base-content/60 italic">Deleted User</span>
                @else
                    {{ $ticket->user?->name }}
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Proponent Position</p>
            <p class="text-base-content break-words">
                @if ($userDeleted)
                    <span class="text-base-content/60 italic">N/A</span>
                @else
                    {{ $ticket->user?->position?->position_name ?? 'N/A' }}
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Contact Email</p>
            <p class="text-base-content break-all">
                @if ($userDeleted)
                    <span class="text-base-content/60 italic">N/A</span>
                @else
                    {{ $ticket->user?->email }}
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Proponent Contact</p>
            <p class="text-base-content break-words">{{ $ticket->proponent_contact ?? 'N/A' }}</p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Organization Adviser</p>
            <p class="text-base-content break-words">
                @if ($orgDeleted)
                    <span class="text-base-content/60 italic">N/A</span>
                @else
                    {{ $ticket->user?->studentOrganization?->adviser_name ?? 'N/A' }}
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Adviser Contact</p>
            <p class="text-base-content break-words">{{ $ticket->adviser_contact ?? 'N/A' }}</p>
        </div>
    </div>
</div>
