@php
    $statusOptions = [
        ['id' => 'all', 'name' => 'All Status'],
        ['id' => 'pending', 'name' => 'Pending'],
        ['id' => 'approved', 'name' => 'Approved'],
        ['id' => 'rejected', 'name' => 'Rejected'],
    ];

    $priorityOptions = [
        ['id' => 'all', 'name' => 'All Priorities'],
        ['id' => 'high', 'name' => 'High'],
        ['id' => 'medium', 'name' => 'Medium'],
        ['id' => 'low', 'name' => 'Low'],
    ];

    $statusBadges = [
        'pending' => 'badge-warning',
        'approved' => 'badge-success',
        'rejected' => 'badge-error',
    ];

    $priorityBadges = [
        'high' => 'badge-error',
        'medium' => 'badge-warning',
        'low' => 'badge-success',
    ];

    $defaultRequestTypeBadge = 'badge-ghost';

    $requestTypeBadgeDefaults = [
        'venue booking' => 'badge-primary',
        'venue' => 'badge-primary',
        'equipment' => 'badge-info',
        'logistics' => 'badge-secondary',
        'catering' => 'badge-accent',
    ];

    $requestTypeBadgePalette = [
        'badge-success',
        'badge-warning',
        'badge-error',
        'badge-neutral',
        'badge-info',
        'badge-secondary',
        'badge-accent',
        'badge-primary',
    ];

    $approvalCollection = collect($approvals)
        ->map(fn ($approval) => is_array($approval) ? $approval : (array) $approval);

    $requestTypeBadgeMap = $requestTypeBadgeDefaults;
    $paletteIndex = 0;

    foreach ($approvalCollection->pluck('request_type')->filter()->unique()->values() as $typeLabel) {
        $lookupKey = \Illuminate\Support\Str::of($typeLabel)->lower()->toString();

        if (! array_key_exists($lookupKey, $requestTypeBadgeMap)) {
            $requestTypeBadgeMap[$lookupKey] = $requestTypeBadgePalette[$paletteIndex % count($requestTypeBadgePalette)];
            $paletteIndex++;
        }
    }

    $approvals = $approvalCollection
        ->map(function ($approval) use ($requestTypeBadgeMap, $defaultRequestTypeBadge) {
            $typeKey = \Illuminate\Support\Str::of($approval['request_type'] ?? '')->lower()->toString();
            $approval['request_type_badge_class'] = $requestTypeBadgeMap[$typeKey] ?? $defaultRequestTypeBadge;

            return $approval;
        })
        ->values()
        ->all();
@endphp

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 sm:py-12 space-y-6">
    <div class="mb-6">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Approvals Management') }}
        </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stats shadow bg-emerald-50 dark:bg-emerald-900/20">
            <div class="stat">
                <div class="stat-figure text-emerald-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="stat-title text-emerald-700 dark:text-emerald-300">Pending Review</div>
                <div class="stat-value text-emerald-600">{{ number_format($stats['pending'] ?? 0) }}</div>
                <div class="stat-desc text-emerald-600">Awaiting your approval</div>
            </div>
        </div>

        <div class="stats shadow bg-blue-50 dark:bg-blue-900/20">
            <div class="stat">
                <div class="stat-figure text-blue-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="stat-title text-blue-700 dark:text-blue-300">Today's Approvals</div>
                <div class="stat-value text-blue-600">{{ number_format($stats['todayApproved'] ?? 0) }}</div>
                <div class="stat-desc text-blue-600">Approved today</div>
            </div>
        </div>

        <div class="stats shadow bg-orange-50 dark:bg-orange-900/20">
            <div class="stat">
                <div class="stat-figure text-orange-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="stat-title text-orange-700 dark:text-orange-300">Urgent</div>
                <div class="stat-value text-orange-600">{{ number_format($stats['urgent'] ?? 0) }}</div>
                <div class="stat-desc text-orange-600">High priority items</div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-64">
                    <x-mary-input wire:model.debounce.300ms="search" label="Search Requests"
                        placeholder="Search by event name, organization..." class="input-emerald">
                        <x-slot:prepend>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </x-slot:prepend>
                    </x-mary-input>
                </div>

                <div>
                    <x-mary-select wire:model.live="statusFilter" label="Status Filter" :options="$statusOptions"
                        option-value="id" option-label="name" class="select-emerald" />
                </div>

                <div>
                    <x-mary-select wire:model.live="priorityFilter" label="Priority Filter" :options="$priorityOptions"
                        option-value="id" option-label="name" class="select-emerald" />
                </div>

                <x-mary-button type="button" label="Bulk Approve ({{ count($selectedRequests) }})"
                    icon="s-check" class="btn-emerald" disabled />
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Approval Requests</h3>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="checkbox" class="checkbox checkbox-emerald" disabled>
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Select All</span>
                    </label>
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($approvals as $approval)
                    @php
                        $priorityKey = $approval['priority'] ?? 'low';
                        $priorityLabel = $approval['priority_label'] ?? ucfirst($priorityKey);
                        $statusKey = $approval['status'] ?? 'pending';
                        $statusLabel = $approval['status_label'] ?? ucfirst($statusKey);
                        $cardTone = 'border-gray-200 bg-white dark:bg-gray-800';
                    @endphp
                    <div class="border rounded-lg p-4 hover:shadow-md transition-shadow {{ $cardTone }}"
                        wire:key="approval-{{ $approval['id'] ?? uniqid() }}">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="flex items-start space-x-4 flex-1">
                                <input type="checkbox" class="checkbox checkbox-emerald mt-1" disabled>

                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center space-x-3 mb-2">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                            {{ $approval['event_name'] }}
                                        </h4>
                                        <x-mary-badge :value="$statusLabel"
                                            class="badge {{ $statusBadges[$statusKey] ?? 'badge-ghost' }}" />
                                        <x-mary-badge :value="\Illuminate\Support\Str::lower($priorityLabel)"
                                            class="badge {{ $priorityBadges[$priorityKey] ?? 'badge-ghost' }}" />
                                        <x-mary-badge :value="$approval['request_type'] ?? 'N/A'"
                                            class="badge {{ $approval['request_type_badge_class'] ?? $defaultRequestTypeBadge }}" />
                                    </div>

                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        <div>
                                            <strong>Organization:</strong> {{ $approval['organization'] }}
                                        </div>
                                        <div>
                                            <strong>Event Date:</strong> {{ $approval['event_date'] }}
                                        </div>
                                        <div>
                                            <strong>Requested:</strong> {{ $approval['request_type'] }}
                                        </div>
                                    </div>

                                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                                        {{ $approval['description'] }}
                                    </p>

                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @forelse ($approval['requirements'] as $requirement)
                                            <x-mary-badge :value="$requirement" class="badge badge-outline badge-sm" />
                                        @empty
                                            <span class="text-xs text-gray-500 dark:text-gray-400">No special
                                                requirements provided.</span>
                                        @endforelse
                                    </div>

                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Submitted: {{ $approval['submitted_date'] }} |
                                        Due: {{ $approval['due_date'] }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col space-y-2 ml-4 md:self-center md:items-center w-full sm:w-56">
                                @if ($statusKey === 'pending')
                                    <div class="flex flex-col gap-2 w-full">
                                        <div class="flex gap-2">
                                            <x-mary-button type="button" label="Approve" icon="s-check"
                                                class="btn-sm btn-success flex-1" />
                                            <x-mary-button type="button" label="Reject" icon="s-x-mark"
                                                class="btn-sm btn-error flex-1" />
                                        </div>
                                        @if ($approval['ticket_id'])
                                            <x-mary-button label="Details" icon="s-eye"
                                                class="btn-sm btn-outline btn-emerald w-full" link="{{ route('gso.ticket-details', ['ticket' => $approval['ticket_id']]) }}"
                                                wire:navigate />
                                        @else
                                            <x-mary-button label="Details" icon="s-eye"
                                                class="btn-sm btn-outline w-full"
                                                disabled />
                                        @endif
                                    </div>
                                @else
                                    @php
                                        $statusBadgeText = match ($statusKey) {
                                            'approved' => $statusLabel . ' ✓',
                                            'rejected' => $statusLabel . ' ✗',
                                            default => $statusLabel,
                                        };
                                    @endphp
                                    <div class="flex flex-col gap-2 w-full">
                                        <div class="flex">
                                            <x-mary-badge :value="$statusBadgeText"
                                                class="badge {{ $statusBadges[$statusKey] ?? 'badge-ghost' }} flex-1 justify-center" />
                                        </div>
                                        @if ($approval['ticket_id'])
                                            <x-mary-button label="Details" icon="s-eye"
                                                class="btn-sm btn-outline btn-emerald w-full" link="{{ route('gso.ticket-details', ['ticket' => $approval['ticket_id']]) }}"
                                                wire:navigate />
                                        @else
                                            <x-mary-button label="Details" icon="s-eye"
                                                class="btn-sm btn-outline w-full"
                                                disabled />
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No approval requests</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No requests match your current filters.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
