<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
                ]" :rows="$pendingApprovals">
                    @scope('cell_request_type', $row)
                        @php
                            $typeKey = \Illuminate\Support\Str::of($row['request_type'] ?? '')->lower()->toString();
                            $typeClass = match ($typeKey) {
                                'venue booking' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                'equipment' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'logistics' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                            };
                        @endphp
                        <x-mary-badge :value="$row['request_type'] ?? 'N/A'"
                            class="{{ $typeClass }} border-none" />
                    @endscope

                    @scope('cell_priority', $row)
                        @php
                            $priorityKey = \Illuminate\Support\Str::of($row['priority'] ?? 'low')->lower()->toString();
                            $priorityClass = match ($priorityKey) {
                                'high' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                            };
                        @endphp
                        <x-mary-badge :value="\Illuminate\Support\Str::title($priorityKey)"
                            class="{{ $priorityClass }} border-none" />
                    @endscope
                </x-mary-table>
            @else
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
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
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $activity['action'] ?? 'Activity' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $activity['details'] ?? 'Details unavailable.' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $activity['time_ago'] ?? 'Just now' }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
                    <x-mary-icon name="s-document" class="w-8 h-8 mx-auto mb-2 text-gray-400" />
                    <p>Activity logs will appear here once actions are taken.</p>
                </div>
            @endif
        </x-mary-card>
    </div>
</div>
