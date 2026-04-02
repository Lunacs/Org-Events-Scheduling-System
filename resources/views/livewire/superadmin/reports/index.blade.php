<div>
    <div class="p-6 space-y-6" wire:loading.class="opacity-50 pointer-events-none"
        wire:target="dateFrom, dateTo, selectedOffices, selectedEventTypes, searchTerm">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-heading text-base-content">Reports & Analytics</h1>
                <p class="text-sm text-base-content/60 mt-1">Generate and export system reports</p>
            </div>
            <div class="flex gap-2">
                {{-- Filter Toggle Button --}}
                <button class="btn btn-outline gap-2 border-base-300" wire:click="$toggle('showFilterDrawer')">
                    <x-mary-icon name="o-adjustments-horizontal" class="w-4 h-4" />
                    Filter
                    @if (count($selectedOffices) > 0 || count($selectedEventTypes) > 0)
                        <span
                            class="badge badge-primary badge-sm">{{ count($selectedOffices) + count($selectedEventTypes) }}</span>
                    @endif
                </button>

                <button class="btn btn-primary gap-2" onclick="document.getElementById('exportModal').showModal()">
                    <x-mary-icon name="o-arrow-down-tray" class="w-4 h-4" />
                    Export Report
                </button>
            </div>
        </div>

        {{-- Filter Drawer (Right Side) --}}
        <x-mary-drawer wire:model="showFilterDrawer" title="Filters" subtitle="Refine your report data" separator
            with-close-button class="w-11/12 lg:w-1/3" right>
            <div class="space-y-6">
                {{-- Date Range Section --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-base font-medium">Date range</span>
                        <button class="text-teal-600 font-medium text-sm hover:underline"
                            wire:click="resetDateRange">Reset</button>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <label class="block text-sm text-base-content/60 mb-1">From:</label>
                            <input type="date" wire:model.live="dateFrom"
                                class="input input-bordered w-full bg-base-100" />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm text-base-content/60 mb-1">To:</label>
                            <input type="date" wire:model.live="dateTo"
                                class="input input-bordered w-full bg-base-100" />
                        </div>
                    </div>
                </div>

                <div class="divider my-2"></div>

                {{-- Organizations Section --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-base font-medium">Organizations</span>
                        <button class="text-teal-600 font-medium text-sm hover:underline"
                            wire:click="resetOffices">Reset</button>
                    </div>
                    <div class="max-h-48 overflow-y-auto space-y-1 border border-base-200 rounded-lg p-2">
                        @foreach ($offices as $office)
                            <label
                                class="flex items-center gap-3 px-3 py-2.5 hover:bg-base-200 rounded-md cursor-pointer transition-colors group">
                                <input type="checkbox" value="{{ $office->org_id }}" wire:model.live="selectedOffices"
                                    class="checkbox checkbox-primary checkbox-sm border-base-300" />
                                <span
                                    class="text-sm group-hover:text-primary transition-colors">{{ $office->org_name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if (count($selectedOffices) > 0)
                        <div class="text-sm text-base-content/60">{{ count($selectedOffices) }} selected</div>
                    @endif
                </div>

                <div class="divider my-2"></div>

                {{-- Event Types Section --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-base font-medium">Event types</span>
                        <button class="text-teal-600 font-medium text-sm hover:underline"
                            wire:click="resetEventTypes">Reset</button>
                    </div>
                    <div class="max-h-48 overflow-y-auto space-y-1 border border-base-200 rounded-lg p-2">
                        @foreach ($eventTypes as $type)
                            <label
                                class="flex items-center gap-3 px-3 py-2.5 hover:bg-base-200 rounded-md cursor-pointer transition-colors group">
                                <input type="checkbox" value="{{ $type->event_type_id }}"
                                    wire:model.live="selectedEventTypes"
                                    class="checkbox checkbox-primary checkbox-sm border-base-300" />
                                <span
                                    class="text-sm group-hover:text-primary transition-colors">{{ $type->type_name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if (count($selectedEventTypes) > 0)
                        <div class="text-sm text-base-content/60">{{ count($selectedEventTypes) }} selected</div>
                    @endif
                </div>
            </div>

            <x-slot:actions>
                <x-mary-button label="Reset all" wire:click="clearFilters" class="btn-ghost" />
                <x-mary-button label="Apply" @click="$wire.showFilterDrawer = false" class="btn-primary"
                    icon="o-check" />
            </x-slot:actions>
        </x-mary-drawer>

        {{-- Active Filters Display Bar --}}
        @if (count($selectedOffices) > 0 || count($selectedEventTypes) > 0 || $searchTerm)
            <div
                class="flex flex-wrap items-center gap-3 p-4 bg-base-100 rounded-xl border border-base-200 shadow-sm animate-in fade-in slide-in-from-top-4 duration-300">
                <span class="text-sm text-base-content/50 font-medium flex items-center gap-2">
                    <x-mary-icon name="o-funnel" class="w-4 h-4" />
                    Active Filters:
                </span>

                @if ($searchTerm)
                    <div class="badge badge-neutral gap-2 py-3.5 px-4 font-medium text-xs">
                        Search: "{{ $searchTerm }}"
                        <button wire:click="resetSearch"
                            class="btn btn-ghost btn-xs btn-circle hover:bg-neutral-content/20 transition-colors">
                            <x-mary-icon name="o-x-mark" class="w-3.5 h-3.5" />
                        </button>
                    </div>
                @endif

                @foreach ($selectedOffices as $orgId)
                    @php $org = $offices->firstWhere('org_id', $orgId); @endphp
                    @if ($org)
                        <div class="badge badge-primary gap-2 py-3.5 px-4 font-medium text-xs">
                            <x-mary-icon name="o-building-office" class="w-3.5 h-3.5" />
                            {{ $org->org_name }}
                            <button
                                wire:click="$set('selectedOffices', {{ json_encode(array_values(array_diff($selectedOffices, [$orgId]))) }})"
                                class="btn btn-ghost btn-xs btn-circle hover:bg-primary-focus transition-colors">
                                <x-mary-icon name="o-x-mark" class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    @endif
                @endforeach

                @foreach ($selectedEventTypes as $typeId)
                    @php $type = $eventTypes->firstWhere('event_type_id', $typeId); @endphp
                    @if ($type)
                        <div class="badge badge-secondary gap-2 py-3.5 px-4 font-medium text-xs">
                            <x-mary-icon name="o-calendar" class="w-3.5 h-3.5" />
                            {{ $type->type_name }}
                            <button
                                wire:click="$set('selectedEventTypes', {{ json_encode(array_values(array_diff($selectedEventTypes, [$typeId]))) }})"
                                class="btn btn-ghost btn-xs btn-circle hover:bg-secondary-focus transition-colors">
                                <x-mary-icon name="o-x-mark" class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    @endif
                @endforeach

                <button wire:click="clearFilters"
                    class="btn btn-ghost btn-xs normal-case text-error hover:bg-error/10 ml-auto">
                    Clear all
                </button>
            </div>
        @endif

        {{-- Overview Stats --}}
        @island(name: 'charts')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stats shadow-lg border-primary border-2 bg-primary/10 text-primary-content">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <x-mary-icon name="o-calendar" class="w-8 h-8" />
                    </div>
                    <div class="stat-title text-primary-content opacity-90 dark:text-base-content">Total Events</div>
                    <div class="stat-value text-primary-content dark:text-white">
                        {{ $chartData['overview']['total_events'] ?? 0 }}
                    </div>
                    <div class="stat-desc text-primary-content opacity-50 dark:text-base-content">In selected period
                    </div>
                </div>
            </div>

            <div class="stats shadow-lg border-primary border-2 bg-success/10">
                <div class="stat">
                    <div class="stat-figure text-success">
                        <x-mary-icon name="o-check-circle" class="w-8 h-8" />
                    </div>
                    <div class="stat-title text-success-content opacity-90 dark:text-base-content">Approved Events
                    </div>
                    <div class="stat-value text-success-content dark:text-white">
                        {{ $chartData['overview']['approved_events'] ?? 0 }}
                    </div>
                    <div class="stat-desc text-success-content opacity-50 dark:text-base-content">Successfully approved
                    </div>
                </div>
            </div>

            <div class="stats shadow-lg border-info border-2 bg-info/10 text-info-content">
                <div class="stat">
                    <div class="stat-figure text-info">
                        <x-mary-icon name="o-ticket" class="w-8 h-8" />
                    </div>
                    <div class="stat-title text-info-content opacity-90 dark:text-base-content">Total Tickets</div>
                    <div class="stat-value text-info-content dark:text-white">
                        {{ $chartData['overview']['total_tickets'] ?? 0 }}</div>
                    <div class="stat-desc text-info-content opacity-50 dark:text-base-content">All submissions</div>
                </div>
            </div>

            <div class="stats shadow-lg border-accent border-2 bg-accent/10 text-accent-content">
                <div class="stat">
                    <div class="stat-figure text-accent">
                        <x-mary-icon name="o-user-group" class="w-8 h-8" />
                    </div>
                    <div class="stat-title text-accent-content opacity-90 dark:text-base-content">Active Organizations
                    </div>
                    <div class="stat-value text-accent-content dark:text-white">
                        {{ $chartData['overview']['active_orgs'] ?? 0 }}</div>
                    <div class="stat-desc text-accent-content opacity-50 dark:text-base-content">With events</div>
                </div>
            </div>
        </div>

        {{-- Charts Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Events by Month Chart --}}
            <x-mary-card title="Events by Month" class="shadow-lg">
                @if (!empty($eventsByMonthChart))
                    <x-mary-chart wire:model="eventsByMonthChart" class="h-80" />
                @else
                    <div class="flex items-center justify-center h-80 text-base-content/50">
                        <span>No data available</span>
                    </div>
                @endif
            </x-mary-card>

            {{-- Events by Type Chart --}}
            <x-mary-card title="Events by Type" class="shadow-lg">
                @if (!empty($eventsByTypeChart))
                    <x-mary-chart wire:model="eventsByTypeChart" class="h-80" />
                @else
                    <div class="flex items-center justify-center h-80 text-base-content/50">
                        <span>No data available</span>
                    </div>
                @endif
            </x-mary-card>

            {{-- Events by Office Chart --}}
            <x-mary-card title="Events by Organization" class="shadow-lg">
                @if (!empty($eventsByOfficeChart))
                    <x-mary-chart wire:model="eventsByOfficeChart" class="h-80" />
                @else
                    <div class="flex items-center justify-center h-80 text-base-content/50">
                        <span>No data available</span>
                    </div>
                @endif
            </x-mary-card>

            {{-- Ticket Status Distribution --}}
            <x-mary-card title="Ticket Status Distribution" class="shadow-lg">
                @if (!empty($ticketStatusChart))
                    <x-mary-chart wire:model="ticketStatusChart" class="h-80" />
                @else
                    <div class="flex items-center justify-center h-80 text-base-content/50">
                        <span>No data available</span>
                    </div>
                @endif
            </x-mary-card>
        </div>

        {{-- Users by Role Chart --}}
        <x-mary-card title="Users by Role" class="shadow-lg">
            @if (!empty($usersByRoleChart))
                <x-mary-chart wire:model="usersByRoleChart" class="h-72" />
            @else
                <div class="flex items-center justify-center h-72 text-base-content/50">
                    <span>No data available</span>
                </div>
            @endif
        </x-mary-card>
    </div>
    @endisland

    {{-- Export Modal --}}
    <dialog id="exportModal" class="modal">
        <div class="modal-box max-w-sm">
            <h3 class="font-bold text-lg mb-4">Export Report</h3>
            <p class="text-sm text-base-content/70 mb-6">
                Choose a format to export the current report data.
            </p>
            <div class="flex flex-col gap-3">
                <button class="btn btn-outline gap-3 justify-start h-14" wire:click.async="exportReport('csv')">
                    <x-mary-icon name="o-document-text" class="w-5 h-5 text-primary" />
                    <div class="text-left">
                        <div class="font-bold">Export as CSV</div>
                        <div class="text-xs opacity-50 text-base-content">Spreadsheet compatible format</div>
                    </div>
                </button>
                <button class="btn btn-outline gap-3 justify-start h-14" wire:click.async="exportReport('pdf')">
                    <x-mary-icon name="o-document" class="w-5 h-5 text-secondary" />
                    <div class="text-left">
                        <div class="font-bold">Export as PDF</div>
                        <div class="text-xs opacity-50 text-base-content">Print-ready document</div>
                    </div>
                </button>
            </div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-ghost">Cancel</button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</div>
