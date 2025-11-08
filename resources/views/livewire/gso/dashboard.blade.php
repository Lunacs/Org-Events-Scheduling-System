<div>
    <div class="p-6">
        {{-- Header --}}
        <div class="mb-8">
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-base-content">GSO Dashboard</h1>
                        <p class="text-base-content/70 mt-1">General Services Office - Event Management System</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-mary-stat title="Pending Approvals"
                :value="number_format(data_get($stats, 'pending', 0))"
                description="Awaiting your approval"
                icon="s-clock" color="text-warning" />

            <x-mary-stat title="Approved Today"
                :value="number_format(data_get($stats, 'approvedToday', 0))"
                description="Decisions made today"
                icon="s-check-circle" color="text-success" />

            <x-mary-stat title="Rejected Today"
                :value="number_format(data_get($stats, 'rejectedToday', 0))"
                description="Requires follow-up"
                icon="s-x-circle" color="text-error" />

            <x-mary-stat title="Upcoming Events"
                :value="number_format(data_get($stats, 'upcomingEvents', 0))"
                description="Scheduled in calendar"
                icon="s-calendar-days" color="text-info" />
        </div>

        @php
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

            foreach (
                collect($pendingApprovals)
                    ->map(fn ($row) => is_array($row) ? $row : (array) $row)
                    ->pluck('request_type')
                    ->filter()
                    ->unique()
                    ->values() as $typeLabel
            ) {
                $lookupKey = \Illuminate\Support\Str::of($typeLabel)->lower()->toString();

                if (! array_key_exists($lookupKey, $requestTypeBadgeMap)) {
                    $requestTypeBadgeMap[$lookupKey] = $requestTypeBadgePalette[$paletteIndex % count($requestTypeBadgePalette)];
                    $paletteIndex++;
                }
            }

            $pendingApprovalRows = collect($pendingApprovals)
                ->map(fn ($row) => is_array($row) ? $row : (array) $row)
                ->map(function ($row) use ($requestTypeBadgeMap, $defaultRequestTypeBadge) {
                    $typeKey = \Illuminate\Support\Str::of($row['request_type'] ?? '')->lower()->toString();
                    $row['request_type_badge_class'] = $requestTypeBadgeMap[$typeKey] ?? $defaultRequestTypeBadge;

                    return $row;
                })
                ->values()
                ->all();
        @endphp

        <x-mary-card title="Pending Approvals" subtitle="Tickets awaiting action">
            <x-slot:menu>
                <x-mary-button label="View All" icon="s-arrow-right" class="btn-sm btn-ghost"
                    link="{{ route('gso.approvals') }}" wire:navigate />
            </x-slot:menu>

            @if (count($pendingApprovals) > 0)
                <x-mary-table :headers="[
                    ['key' => 'ticket_number', 'label' => 'Ticket ID'],
                    ['key' => 'event_title', 'label' => 'Event'],
                    ['key' => 'organization', 'label' => 'Organization'],
                    ['key' => 'request_type', 'label' => 'Request Type'],
                    ['key' => 'event_date', 'label' => 'Date'],
                    ['key' => 'priority', 'label' => 'Priority'],
                ]" :rows="$pendingApprovalRows">
                    @scope('cell_request_type', $row)
                        <x-mary-badge :value="$row['request_type'] ?? 'N/A'"
                            class="{{ $row['request_type_badge_class'] ?? 'badge-ghost text-base-content' }} border-none badge-lg h-auto flex-wrap whitespace-normal leading-tight px-3 py-1" />
                    @endscope

                    @scope('cell_priority', $row)
                        @php
                            $priorityKey = \Illuminate\Support\Str::of($row['priority'] ?? 'low')->lower()->toString();
                            $priorityClass = match ($priorityKey) {
                                'high' => 'badge-error text-white',
                                'medium' => 'badge-warning text-white',
                                'low' => 'badge-success text-white',
                                default => 'badge-ghost text-base-content',
                            };
                        @endphp
                        <x-mary-badge :value="\Illuminate\Support\Str::title($priorityKey)"
                            class="{{ $priorityClass }} border-none badge-lg h-auto flex-wrap whitespace-normal leading-tight px-3 py-1" />
                    @endscope
                </x-mary-table>
            @else
                <div class="text-center py-8 text-base-content/70">
                    <x-mary-icon name="s-check-circle" class="w-10 h-10 mx-auto mb-2 text-success" />
                    <p>You're all caught up. No pending approvals right now.</p>
                </div>
            @endif
        </x-mary-card>

        <x-mary-card title="Recent Activity" subtitle="Latest actions from your team">
            @if (count($recentActivities) > 0)
                <ul class="space-y-3">
                    @foreach ($recentActivities as $activity)
                        @php
                            $actionText = \Illuminate\Support\Str::of($activity['action'] ?? '')->lower()->toString();
                            $icon = 's-chat-bubble-left-right';
                            $iconColor = 'text-info';

                            if (str_contains($actionText, 'approve')) {
                                $icon = 's-check-circle';
                                $iconColor = 'text-success';
                            } elseif (str_contains($actionText, 'reject')) {
                                $icon = 's-x-circle';
                                $iconColor = 'text-error';
                            }
                        @endphp

                        <li class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <x-mary-icon :name="$icon" class="w-6 h-6 {{ $iconColor }}" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-base-content">
                                    {{ $activity['action'] ?? 'Activity' }}
                                </p>
                                <p class="text-xs text-base-content/70">
                                    {{ $activity['details'] ?? 'Details unavailable.' }}
                                </p>
                                <p class="text-xs text-base-content/70">
                                    {{ $activity['time_ago'] ?? 'Just now' }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-center py-6 text-sm text-base-content/70">
                    <x-mary-icon name="s-document" class="w-8 h-8 mx-auto mb-2 text-base-content/40" />
                    <p>Activity logs will appear here once actions are taken.</p>
                </div>
            @endif
        </x-mary-card>
        </div>
    </div>
</div>
