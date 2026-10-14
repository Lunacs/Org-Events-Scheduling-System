<div>
    <div class="space-y-6">
        @php
            $normalizeRows = fn($rows) => collect($rows)
                ->map(fn($row) => is_array($row) ? $row : (array) $row)
                ->map(function ($row) {
                    $row['submitted_at'] = $row['event_date'] ?? 'N/A';
                    return $row;
                });

            $pendingApprovalCollection = $normalizeRows($pendingApprovals);
            $approvalSnapshotCollection = $normalizeRows($approvalSnapshot ?? []);

            $defaultRequestTypeBadge = 'badge-ghost text-base-content';

            $requestTypeBadgeDefaults = [
                'venue booking' => 'badge-primary text-white',
                'venue' => 'badge-primary text-white',
                'equipment' => 'badge-info text-white',
                'logistics' => 'badge-secondary text-white',
                'catering' => 'badge-accent text-white',
            ];

            $requestTypeBadgePalette = [
                'badge-success text-white',
                'badge-warning text-white',
                'badge-error text-white',
                'badge-neutral text-white',
                'badge-info text-white',
                'badge-secondary text-white',
                'badge-accent text-white',
                'badge-primary text-white',
            ];

            $requestTypeBadgeMap = $requestTypeBadgeDefaults;
            $paletteIndex = 0;

            $combinedApprovalCollection = $pendingApprovalCollection->concat($approvalSnapshotCollection);

            foreach ($combinedApprovalCollection->pluck('request_type')->filter()->unique()->values() as $typeLabel) {
                $lookupKey = \Illuminate\Support\Str::of($typeLabel)->lower()->toString();

                if (!array_key_exists($lookupKey, $requestTypeBadgeMap)) {
                    $requestTypeBadgeMap[$lookupKey] =
                        $requestTypeBadgePalette[$paletteIndex % count($requestTypeBadgePalette)];
                    $paletteIndex++;
                }
            }

            $assignBadgeClass = fn($collection) => $collection
                ->map(function ($row) use ($requestTypeBadgeMap, $defaultRequestTypeBadge) {
                    $typeKey = \Illuminate\Support\Str::of($row['request_type'] ?? '')
                        ->lower()
                        ->toString();
                    $row['request_type_badge_class'] = $requestTypeBadgeMap[$typeKey] ?? $defaultRequestTypeBadge;

                    return $row;
                })
                ->values();

            $pendingApprovalRows = $assignBadgeClass($pendingApprovalCollection);
            $approvalSnapshotRows = $assignBadgeClass($approvalSnapshotCollection);

            $recentActivityItems = collect($recentActivities)->map(fn($row) => is_array($row) ? $row : (array) $row);
        @endphp

        <x-ui.page-heading title="GSO Dashboard"
            subtitle="Venue and logistics requests that OSA forwarded to the General Services Office.">
            @can('tickets.view')
                <x-slot:actions>
                    <x-ui.button label="Open review queue" icon-right="o-arrow-right" class="btn-primary"
                        link="{{ route('gso.ticket-review') }}" />
                </x-slot:actions>
            @endcan
        </x-ui.page-heading>

        {{-- Status strip --}}
        <div id="overview-metrics" class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <x-ui.metric-card label="Needs your review" value="{{ number_format(data_get($stats, 'pending', 0)) }}"
                meta="Forwarded by OSA" icon="o-inbox-arrow-down" color="info"
                link="{{ route('gso.ticket-review') }}" link-label="Open the GSO review queue" />
            <x-ui.metric-card label="Approved today" value="{{ number_format(data_get($stats, 'approvedToday', 0)) }}"
                meta="Sent back to OSA" icon="o-check-circle" color="success" />
            <x-ui.metric-card label="Returned today" value="{{ number_format(data_get($stats, 'for_revisionToday', 0)) }}"
                meta="Not approved by GSO" icon="o-arrow-uturn-left" color="warning" />
            <x-ui.metric-card label="Upcoming events" value="{{ number_format(data_get($stats, 'upcomingEvents', 0)) }}"
                meta="Approved and scheduled" icon="o-calendar-days" link="{{ route('gso.calendar') }}"
                link-label="Open the event calendar" />
        </div>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
            {{-- Queue --}}
            <x-ui.card id="ticket-review-section" title="Needs your review"
                subtitle="Soonest events first. Approve or return each request so OSA can decide."
                class="lg:col-span-2">
                <x-slot:menu>
                    <x-ui.button label="View all" icon-right="o-arrow-right" link="{{ route('gso.ticket-review') }}"
                        class="btn-sm btn-ghost" />
                </x-slot:menu>

                @if ($pendingApprovalRows->count() > 0)
                    <ul class="-mx-4 -mb-4 divide-y divide-base-300 border-t border-base-300 sm:-mx-5 sm:-mb-5">
                        @foreach ($pendingApprovalRows->sortBy('days_until_event') as $approval)
                            @php
                                $days = $approval['days_until_event'] ?? null;
                                $urgent = ($approval['priority_key'] ?? null) === 'high';
                            @endphp
                            <li wire:key="pending-{{ $approval['approval_id'] }}">
                                <a href="{{ route('gso.ticket-details', ['ticketNumber' => $approval['ticket_number']]) }}"
                                    wire:navigate class="block px-4 transition-colors hover:bg-base-200/60 sm:px-5">
                                    <div class="flex items-center justify-between gap-4 py-3.5">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                <h3 class="truncate text-sm font-semibold text-base-content">{{ $approval['event_title'] }}</h3>
                                                @if ($days !== null)
                                                    <span @class([
                                                        'badge badge-sm badge-soft',
                                                        'badge-warning' => $urgent,
                                                    ])>
                                                        @if ($urgent)
                                                            <x-ui.icon name="o-exclamation-triangle" class="size-3.5" aria-hidden="true" />
                                                        @endif
                                                        {{ $days <= 0 ? 'Event today' : 'Event in ' . $days . ' ' . Str::plural('day', $days) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-base-content/70">
                                                <span class="font-mono">{{ $approval['ticket_number'] }}</span>
                                                <span>{{ $approval['organization'] }}</span>
                                                <span>{{ $approval['request_type'] }}</span>
                                                <span>{{ $approval['event_date'] }}</span>
                                            </p>
                                        </div>
                                        <x-ui.icon name="o-chevron-right" class="size-4 shrink-0 text-base-content/40" aria-hidden="true" />
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-ui.empty-state icon="o-check-circle" title="Nothing waiting for GSO"
                        description="When OSA forwards a request for venue or logistics approval, it appears here." />
                @endif
            </x-ui.card>

            {{-- Activity --}}
            <x-ui.card id="recent-activity-section" title="Recent activity" subtitle="Latest actions by your office.">
                @if ($recentActivityItems->count() > 0)
                    <ul class="-my-2 divide-y divide-base-300">
                        @foreach ($recentActivityItems as $activity)
                            @php
                                $actionText = \Illuminate\Support\Str::of($activity['action'] ?? '')->lower()->toString();
                                [$icon, $iconClasses] = match (true) {
                                    str_contains($actionText, 'approve') => ['o-check-circle', 'text-success'],
                                    str_contains($actionText, 'reject') => ['o-x-circle', 'text-error'],
                                    default => ['o-chat-bubble-bottom-center-text', 'text-base-content/60'],
                                };
                            @endphp
                            <li class="flex items-start gap-3 py-3"
                                wire:key="activity-{{ $activity['id'] ?? \Illuminate\Support\Str::uuid()->toString() }}">
                                <x-ui.icon :name="$icon" class="mt-0.5 size-4 shrink-0 {{ $iconClasses }}" aria-hidden="true" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-base-content">
                                        {{ \Illuminate\Support\Str::headline(strtolower($activity['action'] ?? 'Activity')) }}</p>
                                    <p class="line-clamp-2 text-xs text-base-content/70">{{ $activity['details'] ?? '' }}</p>
                                    <p class="mt-0.5 text-xs text-base-content/70">{{ $activity['time_ago'] ?? 'Just now' }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-ui.empty-state compact icon="o-clock" title="No activity yet"
                        description="Approvals and returns by your office appear here." />
                @endif
            </x-ui.card>
        </div>

        {{-- Recently forwarded tickets --}}
        <x-ui.card id="approval-snapshot-section" title="Recently forwarded"
            subtitle="The latest tickets OSA sent to GSO, with how soon each event is.">
            @if ($approvalSnapshotRows->count() > 0)
                <div class="-mx-4 -mb-4 sm:-mx-5 sm:-mb-5">
                    <x-ui.table :headers="[
                        ['key' => 'event_title', 'label' => 'Event'],
                        ['key' => 'organization', 'label' => 'Organization', 'class' => 'hidden md:table-cell'],
                        ['key' => 'event_date', 'label' => 'Event date'],
                        ['key' => 'priority', 'label' => 'Timing'],
                    ]">
                        @foreach ($approvalSnapshotRows as $row)
                            <tr wire:key="snapshot-row-{{ $row['ticket_number'] }}" class="hover:bg-base-200/50">
                                <x-ui.table-column>
                                    <a href="{{ route('gso.ticket-details', ['ticketNumber' => $row['ticket_number']]) }}"
                                        wire:navigate class="font-medium text-base-content hover:underline">{{ $row['event_title'] }}</a>
                                    <p class="font-mono text-xs text-base-content/70">{{ $row['ticket_number'] }}</p>
                                </x-ui.table-column>
                                <x-ui.table-column class="hidden md:table-cell">{{ $row['organization'] }}</x-ui.table-column>
                                <x-ui.table-column class="whitespace-nowrap">{{ $row['event_date'] }}</x-ui.table-column>
                                <x-ui.table-column>
                                    <span @class([
                                        'badge badge-sm badge-soft whitespace-nowrap',
                                        'badge-warning' => ($row['priority_key'] ?? null) === 'high',
                                    ])>{{ $row['priority'] ?? 'Low' }}</span>
                                </x-ui.table-column>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </div>
            @else
                <x-ui.empty-state icon="o-inbox" title="No forwarded tickets yet"
                    description="Tickets OSA forwards for venue or logistics approval appear here." />
            @endif
        </x-ui.card>
    </div>
</div>
