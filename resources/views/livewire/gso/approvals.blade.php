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
        'badge-success', 'badge-warning', 'badge-error', 'badge-neutral',
        'badge-info', 'badge-secondary', 'badge-accent', 'badge-primary',
    ];

    $approvalCollection = collect($approvals)->map(fn($a) => is_array($a) ? $a : (array) $a);

    $requestTypeBadgeMap = $requestTypeBadgeDefaults;
    $paletteIndex = 0;

    foreach ($approvalCollection->pluck('request_type')->filter()->unique()->values() as $typeLabel) {
        $lookupKey = \Illuminate\Support\Str::of($typeLabel)->lower()->toString();
        if (!array_key_exists($lookupKey, $requestTypeBadgeMap)) {
            $requestTypeBadgeMap[$lookupKey] = $requestTypeBadgePalette[$paletteIndex % count($requestTypeBadgePalette)];
            $paletteIndex++;
        }
    }

    $approvals = $approvalCollection->map(function($approval) use ($requestTypeBadgeMap, $defaultRequestTypeBadge) {
        $typeKey = \Illuminate\Support\Str::of($approval['request_type'] ?? '')->lower()->toString();
        $approval['request_type_badge_class'] = $requestTypeBadgeMap[$typeKey] ?? $defaultRequestTypeBadge;
        return $approval;
    })->values()->all();
@endphp

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 sm:py-12 space-y-6">

    <!-- Success Message -->
    @if (session('message'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-emerald-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-emerald-700 dark:text-emerald-300">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    <!-- Page Header -->
    <div class="mb-6">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Approvals Management') }}
        </h2>
    </div>

    <!-- Stats Cards -->
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

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-96">
                    <x-mary-input 
                        wire:model.live.debounce.300ms="search" 
                        label="Search Requests"
                        placeholder="Search by event name, organization, ticket number..." 
                        class="input-emerald"
                    >
                        <x-slot:prepend>
                            <div class="flex items-center justify-center h-full px-3">
                                <svg class="w-7 h-7 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </x-slot:prepend>
                    </x-mary-input>
                </div>

                <div>
                    <x-mary-select wire:model.live="priorityFilter" label="Priority Filter" :options="$priorityOptions"
                                    option-value="id" option-label="name" class="select-emerald" />
                </div>

                <x-mary-button 
                    type="button" 
                    label="Bulk Approve" 
                    icon="o-check-circle" 
                    class="btn-success"
                    wire:click="bulkApprove"
                    :disabled="empty($selectedRequests)"
                    x-bind:class="{ 'opacity-50 cursor-not-allowed': @js(empty($selectedRequests)) }" />
            </div>
        </div>
    </div>

    <!-- Approval Requests -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Approval Requests</h3>
                <div class="flex items-center space-x-4">
                    @php
                        $pendingApprovals = collect($approvals)->filter(fn($a) => ($a['status'] ?? '') === 'pending');
                        $allPendingIds = $pendingApprovals->pluck('id')->all();
                        $hasPending = !empty($allPendingIds);
                    @endphp
                    @if($hasPending)
                        <label class="flex items-center cursor-pointer" 
                               x-data="{ 
                                   selectedIds: @entangle('selectedRequests').live,
                                   allPendingIds: @js($allPendingIds),
                                   get isAllChecked() {
                                       return Array.isArray(this.selectedIds) && 
                                              this.selectedIds.length === this.allPendingIds.length && 
                                              this.allPendingIds.length > 0;
                                   }
                               }">
                            <div class="relative inline-flex items-center justify-center">
                                <input 
                                    type="checkbox" 
                                    class="checkbox checkbox-emerald"
                                    x-bind:checked="isAllChecked"
                                    x-on:change="
                                        if ($event.target.checked) {
                                            $wire.set('selectedRequests', @js($allPendingIds));
                                        } else {
                                            $wire.set('selectedRequests', []);
                                        }
                                    "
                                >
                                <!-- White checkmark overlay when all selected -->
                                <div 
                                    x-show="isAllChecked"
                                    x-transition.opacity.duration.200ms
                                    class="absolute inset-0 flex items-center justify-center pointer-events-none"
                                >
                                    <svg 
                                        class="w-3.5 h-3.5 text-white"
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                        stroke-width="4"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                Select All Pending ({{ count($allPendingIds) }})
                            </span>
                        </label>
                        @if(count($selectedRequests) > 0)
                            <span class="text-sm font-medium text-emerald-600">
                                {{ count($selectedRequests) }} selected
                            </span>
                        @endif
                    @else
                        <label class="flex items-center opacity-50">
                            <input type="checkbox" class="checkbox checkbox-emerald" disabled>
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Select All</span>
                        </label>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($approvals as $approval)
                    @php
                        $priorityKey = $approval['priority'] ?? 'low';
                        $priorityLabel = $approval['priority_label'] ?? ucfirst($priorityKey);
                        $statusKey = $approval['status'] ?? 'pending';
                        $statusLabel = $approval['status_label'] ?? ucfirst($statusKey);
                        $cardTone = 'border border-gray-200 bg-white dark:bg-gray-800';
                    @endphp
                    <div class="rounded-lg p-4 hover:shadow-md transition-shadow {{ $cardTone }}"
                        wire:key="approval-{{ $approval['id'] ?? uniqid() }}">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">

                            <!-- Approval Info -->
                            <div class="flex items-start space-x-4 flex-1">
                                @if($statusKey === 'pending')
                                    <input 
                                        type="checkbox" 
                                        class="checkbox checkbox-emerald mt-1" 
                                        wire:model.live="selectedRequests"
                                        value="{{ $approval['id'] }}"
                                    >
                                @else
                                    <input type="checkbox" class="checkbox checkbox-emerald mt-1 opacity-30" disabled>
                                @endif

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

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        <div><strong>Organization:</strong> {{ $approval['organization'] }}</div>
                                        <div><strong>Event Date:</strong> {{ $approval['event_date'] }}</div>
                                        <div><strong>Requested:</strong> {{ $approval['request_type'] }}</div>
                                    </div>

                                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                                        {{ $approval['description'] }}
                                    </p>

                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @forelse ($approval['requirements'] as $requirement)
                                            <x-mary-badge :value="$requirement" class="badge badge-outline badge-sm" />
                                        @empty
                                            <span class="text-xs text-gray-500 dark:text-gray-400">No special requirements provided.</span>
                                        @endforelse
                                    </div>

                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Submitted: {{ $approval['submitted_date'] }} | Due: {{ $approval['due_date'] }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col space-y-2 ml-4 md:self-center md:items-center w-full sm:w-56">
                                @if ($statusKey === 'pending')
                                    <div class="flex flex-col gap-2 w-full">
                                        <div class="flex gap-2">
                                            <x-mary-button type="button" label="Approve" icon="s-check"
                                                class="btn-sm btn-success flex-1"
                                                wire:click="confirmAction({{ $approval['id'] }}, 'approve')" />
                                            <x-mary-button type="button" label="Reject" icon="s-x-mark"
                                                class="btn-sm btn-error flex-1"
                                                wire:click="confirmAction({{ $approval['id'] }}, 'reject')" />
                                        </div>
                                        @if ($approval['ticket_id'])
                                            <x-mary-button label="Details" icon="s-eye"
                                                class="btn-sm btn-outline btn-emerald w-full" link="{{ route('gso.ticket-details', ['ticket' => $approval['ticket_id'], 'office' => $approval['office_id'] ?? null]) }}"
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
                                        <div class="flex w-full justify-center">
                                            <x-mary-badge :value="$statusBadgeText"
                                                class="badge {{ $statusBadges[$statusKey] ?? 'badge-ghost' }} w-full justify-center" />
                                        </div>
                                        @if ($approval['ticket_id'])
                                            <x-mary-button label="Details" icon="s-eye"
                                                class="btn-sm btn-outline btn-emerald w-full" link="{{ route('gso.ticket-details', ['ticket' => $approval['ticket_id'], 'office' => $approval['office_id'] ?? null]) }}"
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
                    <div class="flex flex-col items-center justify-center py-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">No approval requests</h3>
                        <p class="mt-1 text-sm">No requests match your current filters.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

        <!-- Modal Confirmation -->
    @php
        $modalActionLabel = $actionType === 'approve' ? 'Approve' : ($actionType === 'reject' ? 'Reject' : ucfirst($actionType));
        $modalActionVerb = $actionType === 'approve' ? 'approve' : ($actionType === 'reject' ? 'reject' : $actionType);
        $modalColor = $actionType === 'approve' ? 'success' : ($actionType === 'reject' ? 'danger' : 'secondary');
        $requiredWord = $actionType === 'approve' ? 'approve' : ($actionType === 'reject' ? 'reject' : null);
    @endphp

    <x-mary-modal wire:model="showConfirmationModal" title="Confirm {{ $modalActionLabel }}">
        <p class="mb-2 text-sm">Are you sure you want to {{ $modalActionVerb }} this request?</p>

       @if($requiredWord)
                <div x-data="{ local: @entangle('confirmationInput'), action: @entangle('actionType'), required: null }"
                    x-init="required = action === 'approve' ? 'approve' : (action === 'reject' ? 'reject' : null); $watch('action', value => required = value === 'approve' ? 'approve' : (value === 'reject' ? 'reject' : null))"
                    class="mt-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type '{{ $requiredWord }}' to confirm</label>
                    <div class="mt-1 flex items-center space-x-2">
                        <x-mary-input x-model="local" placeholder="{{ $requiredWord }}" class="flex-1 h-9" />

                        {{-- Live match indicator (client-side) --}}
                        <div class="w-5 h-5 flex items-center justify-center">
                            <svg x-show="local && local.trim().toLowerCase() === required" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <svg x-show="!(local && local.trim().toLowerCase() === required)" class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></circle>
                            </svg>
                        </div>
                    </div>

                    @error('confirmationInput')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
        @endif

        <x-slot:actions>
            <x-mary-button wire:click="cancelConfirmation" color="secondary">
                Cancel
            </x-mary-button>

                <x-mary-button wire:click="performAction"
                               wire:loading.attr="disabled"
                               wire:target="performAction"
                               color="{{ $modalColor }}"
                               x-bind:disabled="required ? !(local && local.trim().toLowerCase() === required) : false">
                    Yes, {{ $modalActionLabel }}
                </x-mary-button>
        </x-slot:actions>
    </x-mary-modal>

    <!-- Bulk Approval Modal -->
    <x-mary-modal wire:model="showBulkApprovalModal" title="Confirm Bulk Approval">
        <p class="mb-2 text-sm">
            You are about to approve <strong class="text-emerald-600">{{ count($selectedRequests) }} request(s)</strong>. 
            This action will approve all selected requests at once.
        </p>

        <div x-data="{ local: @entangle('bulkConfirmationInput') }" class="mt-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type 'approve' to confirm</label>
            <div class="mt-1 flex items-center space-x-2">
                <x-mary-input x-model="local" placeholder="approve" class="flex-1 h-9" />

                {{-- Live match indicator (client-side) --}}
                <div class="w-5 h-5 flex items-center justify-center">
                    <svg x-show="local && local.trim().toLowerCase() === 'approve'" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg x-show="!(local && local.trim().toLowerCase() === 'approve')" class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></circle>
                    </svg>
                </div>
            </div>

            @error('bulkConfirmationInput')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <x-slot:actions>
            <x-mary-button wire:click="cancelBulkApproval" color="secondary">
                Cancel
            </x-mary-button>

            <x-mary-button 
                wire:click="performBulkApproval"
                wire:loading.attr="disabled"
                wire:target="performBulkApproval"
                color="success"
                x-bind:disabled="!(local && local.trim().toLowerCase() === 'approve')">
                Yes, Approve All
            </x-mary-button>
        </x-slot:actions>
    </x-mary-modal>
</div>
