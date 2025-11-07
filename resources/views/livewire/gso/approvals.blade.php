@php
    $statusOptions = [
        ['id' => 'all', 'name' => 'All Statuses'],
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
    })->values();

    $approvalsCount = $approvals->count();
@endphp

<div
    x-data="{
    showActionModal: @entangle('showConfirmationModal').live,
    actionType: @entangle('actionType').live,
    remarks: @entangle('actionRemarks').defer
    }"
    x-on:keydown.escape.window="if (showActionModal) { $wire.cancelConfirmation(); }"
    class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 sm:py-12 space-y-6">
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-base-content">Ticket Review &amp; Approvals</h1>
                    <p class="text-base-content/70 mt-1">Track, review, and finalize incoming requests for the General Services Office.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-primary">
                        <span wire:loading.remove wire:target="search,statusFilter,priorityFilter,applyFilters">
                            {{ number_format($approvalsCount) }} Requests
                        </span>
                        <span wire:loading wire:target="search,statusFilter,priorityFilter,applyFilters"
                              class="loading loading-spinner loading-xs"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div class="relative md:col-span-2 lg:col-span-2">
                <input wire:model.live.debounce.300ms="search" wire:keydown.enter.prevent="applyFilters" type="text" placeholder="Search tickets..."
                    class="input input-bordered w-full pr-10" />
                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <span wire:loading.remove wire:target="search">
                        <svg class="w-5 h-5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <span wire:loading wire:target="search" class="loading loading-spinner loading-sm"></span>
                </div>
            </div>

            <div class="relative">
                <select wire:model.live="statusFilter" wire:change="applyFilters" class="select select-bordered w-full">
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-10 flex items-center pointer-events-none">
                    <span wire:loading wire:target="statusFilter" class="loading loading-spinner loading-sm"></span>
                </div>
            </div>

            <div class="relative">
                <select wire:model.live="priorityFilter" wire:change="applyFilters" class="select select-bordered w-full">
                    @foreach ($priorityOptions as $option)
                        <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-10 flex items-center pointer-events-none">
                    <span wire:loading wire:target="priorityFilter" class="loading loading-spinner loading-sm"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 relative min-h-[400px]">
        <div wire:loading.flex wire:target="search,statusFilter,priorityFilter,applyFilters"
            class="absolute inset-0 bg-base-100/60 backdrop-blur-sm z-10 items-center justify-center rounded-box">
            <div class="text-center">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="mt-2 text-sm text-base-content/70">Loading requests...</p>
            </div>
        </div>

        @forelse ($approvals as $approval)
            @php
                $priorityKey = $approval['priority'] ?? 'low';
                $priorityLabel = $approval['priority_label'] ?? ucfirst($priorityKey);
                $statusKey = $approval['status'] ?? 'pending';
                $statusLabel = $approval['status_label'] ?? ucfirst($statusKey);
                $statusBadgeClass = $statusBadges[$statusKey] ?? 'badge-neutral';
                $priorityBadgeClass = $priorityBadges[$priorityKey] ?? 'badge-neutral';
                $requestType = $approval['request_type'] ?? 'N/A';
                $requestTypeBadgeClass = $approval['request_type_badge_class'] ?? $defaultRequestTypeBadge;
                $requirements = $approval['requirements'] ?? [];
                $remarks = $approval['remarks'] ?? null;
                $ticketKey = $approval['id'] ?? \Illuminate\Support\Str::uuid()->toString();
                $detailsUrl = $approval['ticket_id']
                    ? route('gso.ticket-details', [
                        'ticket' => $approval['ticket_id'],
                        'office' => $approval['office_id'] ?? null,
                        'approval' => $approval['id'] ?? null,
                    ])
                    : null;
            @endphp

            <div class="flex flex-col bg-base-100 rounded-box shadow-lg overflow-hidden hover:shadow-xl hover:ring-2 ring-primary/20 transition-all duration-200"
                wire:key="approval-card-{{ $ticketKey }}" x-data="{ isHovered: false }" @mouseenter="isHovered = true" @mouseleave="isHovered = false">
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="space-y-1">
                            <h3 class="font-bold text-lg text-base-content line-clamp-2">{{ $approval['event_name'] }}</h3>
                            <p class="text-sm text-base-content/70">{{ $approval['organization'] }}</p>
                        </div>
                        <span class="badge badge-sm {{ $statusBadgeClass }} text-white">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <p class="text-sm text-base-content/80 mb-4 line-clamp-3">{{ $approval['description'] }}</p>

                    <div class="space-y-3 text-sm text-base-content/80 mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-base-content/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ $approval['event_date'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-base-content/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>{{ $requestType }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge {{ $priorityBadgeClass }} badge-sm text-white">{{ $priorityLabel }}</span>
                            <span class="badge {{ $requestTypeBadgeClass }} badge-sm">{{ $requestType }}</span>
                        </div>
                    </div>

                    @if ($remarks)
                        <div class="mb-4 rounded-box border border-warning/40 bg-warning/10 px-3 py-2 text-xs text-warning/80">
                            <span class="font-semibold uppercase tracking-wide">Remarks:</span>
                            <span class="ml-1">{{ $remarks }}</span>
                        </div>
                    @endif

                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            @forelse ($requirements as $requirement)
                                <span class="badge badge-outline badge-sm">{{ $requirement }}</span>
                            @empty
                                <span class="text-xs text-base-content/50">No special requirements provided.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex-1"></div>

                    <div class="space-y-3">
                        @if ($statusKey === 'pending')
                            <div class="flex gap-2">
                                <button type="button" class="btn btn-success btn-sm flex-1"
                                    wire:click="confirmAction({{ $approval['id'] }}, 'approve')">
                                    Approve
                                </button>
                                <button type="button" class="btn btn-error btn-sm flex-1"
                                    wire:click="confirmAction({{ $approval['id'] }}, 'reject')">
                                    Reject
                                </button>
                            </div>
                        @else
                            <div class="flex justify-start">
                                <span class="badge {{ $statusBadgeClass }} text-white badge-md">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        @endif

                        @if ($detailsUrl)
                            <a href="{{ $detailsUrl }}" class="btn btn-primary btn-sm w-full group"
                                wire:navigate title="View ticket details">
                                <span>Details</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @else
                            <button type="button" class="btn btn-outline btn-sm w-full" disabled>
                                Details Unavailable
                            </button>
                        @endif
                    </div>
                </div>

                <div class="bg-base-200 px-6 py-3 border-t border-base-300 text-xs text-base-content/70 flex items-center justify-between">
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $approval['submitted_human'] ?? $approval['submitted_date'] }}</span>
                    </span>
                    <span class="font-mono">#{{ $approval['ticket_number'] ?? 'N/A' }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="flex flex-col items-center gap-4">
                    <svg class="w-16 h-16 text-base-content/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-base-content/70">No approval requests</h3>
                        <p class="text-sm text-base-content/50 mt-1">No requests match your current filters.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div x-cloak x-show="showActionModal" x-transition.opacity.scale class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-base-300/60 backdrop-blur" @click="$wire.cancelConfirmation()"></div>
        <div class="relative bg-base-100 rounded-box shadow-xl w-full max-w-xl p-6 mx-4">
            <button type="button" class="btn btn-sm btn-circle absolute right-4 top-4" @click="$wire.cancelConfirmation()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <h3 class="text-lg font-bold mb-4" x-text="actionType === 'reject' ? 'Confirm Ticket Rejection' : 'Confirm Ticket Approval'"></h3>

            <div class="space-y-4">
                <div class="alert" :class="actionType === 'reject' ? 'alert-error' : 'alert-success'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            :d="actionType === 'reject' ? 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'" />
                    </svg>
                    <div>
                        <h3 class="font-bold" x-text="actionType === 'reject' ? 'You are about to reject this request' : 'You are about to approve this request'"></h3>
                        <p class="text-sm" x-text="actionType === 'reject'
                                ? 'This action cannot be undone. No event will be created.'
                                : 'This action will create an event and schedule it on the calendar.'"></p>
                    </div>
                </div>

                <label class="text-sm font-medium" x-text="actionType === 'reject' ? 'Rejection Remarks' : 'Approval Remarks'"></label>
                <textarea x-model="remarks" class="textarea textarea-bordered w-full" rows="4"
                    :placeholder="actionType === 'reject'
                        ? 'Explain the reason for rejecting this request...'
                        : 'Enter your remarks for approving this request...'"
                    ></textarea>
                <p class="text-xs text-base-content/60"
                    x-text="actionType === 'reject'
                        ? 'Provide a detailed explanation for the rejection (minimum 10 characters)'
                        : 'Provide a brief explanation for this approval (minimum 3 characters)'"></p>
                @error('actionRemarks')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" class="btn" @click="$wire.cancelConfirmation()" wire:loading.attr="disabled" wire:target="performAction">Cancel</button>
                <button type="button" class="btn"
                    :class="actionType === 'reject' ? 'btn-error' : 'btn-success'"
                    @click="$wire.set('actionRemarks', remarks)"
                    wire:click="performAction"
                    wire:loading.attr="disabled"
                    wire:target="performAction"
                    x-bind:disabled="actionType === 'reject'
                        ? remarks.trim().length < 10
                        : remarks.trim().length < 3">
                    <span wire:loading.remove wire:target="performAction"
                        x-text="actionType === 'reject' ? 'Confirm Rejection' : 'Confirm Approval'"></span>
                    <span wire:loading wire:target="performAction" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>
</div>
