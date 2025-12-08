@php
    $statusDefinitions = $statusDefinitions ?? [
        'pending' => ['key' => 'pending', 'label' => 'Pending'],
        'approved' => ['key' => 'approved', 'label' => 'Approved'],
        'for_revision' => ['key' => 'for_revision', 'label' => 'For Revision'],
    ];

    $statusOptions = collect($statusDefinitions)
        ->map(fn($definition) => ['id' => $definition['key'], 'name' => $definition['label']])
        ->prepend(['id' => '', 'name' => 'All Statuses'])
        ->values()
        ->all();

    $typeOptions = [
        ['id' => '', 'name' => 'All Request Types'],
        ['id' => 'venue', 'name' => 'Venue Booking'],
        ['id' => 'equipment', 'name' => 'Equipment'],
        ['id' => 'logistics', 'name' => 'Logistics'],
        ['id' => 'catering', 'name' => 'Catering'],
    ];

    $priorityOptions = [
        ['id' => '', 'name' => 'All Priorities'],
        ['id' => 'high', 'name' => 'High'],
        ['id' => 'medium', 'name' => 'Medium'],
        ['id' => 'low', 'name' => 'Low'],
    ];

    $statusBadges = [
        'pending' => 'badge-outline border-info/60 text-info',
        'approved' => 'badge-outline border-success/60 text-success',
        'for_revision' => 'badge-outline border-warning/60 text-warning',
        'completed' => 'badge-outline border-neutral/60 text-neutral',
    ];

    $priorityBadges = [
        'high' => 'badge-outline border-error/50 text-error',
        'medium' => 'badge-outline border-warning/50 text-warning',
        'low' => 'badge-outline border-success/50 text-success',
    ];

    // Get the paginated items
    $approvalCollection = collect($approvals->items())->map(fn($a) => is_array($a) ? $a : (array) $a)->values();
    $approvalsCount = $approvals->total();
@endphp

<div>
    <x-mary-toast />

    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-base-content font-heading">Ticket Review</h1>
                    <p class="text-base-content/70 mt-1">Track, prioritize, and review requests assigned to the
                        General
                        Services Office.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-outline border-primary/60 text-primary">
                        <span wire:loading.remove wire:target="search,filterStatus,filterType,filterPriority">
                            {{ number_format($approvalsCount) }} Tickets
                        </span>
                        <span wire:loading wire:target="search,filterStatus,filterType,filterPriority"
                            class="loading loading-spinner loading-xs"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-mary-input wire:model.live.debounce.300ms="search" placeholder="Search tickets..."
                icon="o-magnifying-glass" clearable />

            <x-mary-select wire:model.live="filterStatus" placeholder="Filter by Status" :options="$statusOptions"
                option-value="id" option-label="name" />

            <x-mary-select wire:model.live="filterOrganization" placeholder="Filter by Organization" :options="$organizations"
                option-value="org_id" option-label="org_name" />

            <x-mary-select wire:model.live="filterPriority" placeholder="Filter by Priority" :options="$priorityOptions"
                option-value="id" option-label="name" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 relative min-h-[400px]">
        <div wire:loading.flex wire:target="search,filterStatus,filterOrganization,filterPriority"
            class="absolute inset-0 bg-base-100/60 backdrop-blur-sm z-10 items-center justify-center rounded-box">
            <div class="text-center">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="mt-2 text-sm text-base-content/70">Loading tickets...</p>
            </div>
        </div>

        @forelse ($approvalCollection as $approval)
            @php
                $priorityKey = $approval['priority'] ?? 'low';
                $priorityLabel = $approval['priority_label'] ?? ucfirst($priorityKey);
                $statusKey = $approval['status'] ?? 'pending';
                $statusLabel = $approval['status_label'] ?? ucfirst($statusKey);
                $statusBadgeClass = $statusBadges[$statusKey] ?? 'badge-neutral';
                $priorityBadgeClass = $priorityBadges[$priorityKey] ?? 'badge-neutral';
                $requestType = $approval['request_type'] ?? 'N/A';
                $requirements = $approval['requirements'] ?? [];
                $ticketKey = $approval['approval_id'] ?? \Illuminate\Support\Str::uuid()->toString();
                $detailUrl = !empty($approval['ticket_number'])
                    ? route('gso.ticket-details', ['ticketNumber' => $approval['ticket_number']])
                    : null;
            @endphp

            <div class="flex flex-col bg-base-100 rounded-box shadow-lg overflow-hidden hover:shadow-xl hover:ring-2 ring-primary/20 transition-all duration-200"
                wire:key="approval-card-{{ $ticketKey }}">
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="space-y-1">
                            <h3 class="font-bold text-lg text-base-content line-clamp-2">
                                {{ $approval['event_name'] }}
                            </h3>
                            <p class="text-sm text-base-content/70">{{ $approval['organization'] }}</p>
                        </div>
                        <span class="badge badge-sm {{ $statusBadgeClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <p class="text-sm text-base-content/80 mb-4 line-clamp-3">{{ $approval['description'] }}</p>

                    <div class="space-y-3 text-sm text-base-content/80 mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-base-content/60" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span>{{ $approval['event_date'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-base-content/60" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>{{ $requestType }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge {{ $priorityBadgeClass }} badge-sm">{{ $priorityLabel }}</span>
                        </div>
                    </div>

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

                    <div class="pt-2">
                        @if ($detailUrl)
                            <a href="{{ $detailUrl }}" class="btn btn-primary btn-sm w-full group" wire:navigate
                                title="Review ticket details">
                                <span>Review Details</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @else
                            <button type="button" class="btn btn-outline btn-sm w-full" disabled>
                                Details Unavailable
                            </button>
                        @endif
                    </div>
                </div>

                <div
                    class="bg-base-200 px-6 py-3 border-t border-base-300 text-xs text-base-content/70 flex items-center justify-between">
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $approval['submitted_date'] }}</span>
                    </span>
                    <span class="font-mono">#{{ $approval['ticket_number'] ?? 'N/A' }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="flex flex-col items-center gap-4">
                    <svg class="w-16 h-16 text-base-content/20" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                        </path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-base-content/70">No tickets found</h3>
                        <p class="text-sm text-base-content/50 mt-1">No tickets match your current filters.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($approvals->total() > 0)
        <x-tickets.ticket-pagination :tickets="$approvals" />
    @endif
</div>
