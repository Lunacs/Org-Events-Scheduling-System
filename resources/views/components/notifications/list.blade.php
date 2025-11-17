@props([
    'notifications' => [],
    'unreadCount' => 0,
    'todayCount' => 0,
    'weekCount' => 0,
    'totalCount' => 0,
    'search' => '',
    'typeFilter' => '',
    'statusFilter' => '',
    'typeOptions' => [],
    'showTicketNumber' => true,
])

{{-- Notification Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-mary-stat title="Unread" value="{{ $unreadCount }}" icon="s-bell" color="text-error" />
    <x-mary-stat title="Today" value="{{ $todayCount }}" icon="s-clock" color="text-info" />
    <x-mary-stat title="This Week" value="{{ $weekCount }}" icon="s-calendar-days" color="text-success" />
    <x-mary-stat title="Total" value="{{ $totalCount }}" icon="s-archive-box" color="text-secondary" />
</div>

{{-- Filter and Search --}}
<x-mary-card>
    <div class="flex flex-wrap gap-4 items-end">
        <x-mary-input
            label="Search Notifications"
            wire:model.live="search"
            placeholder="Search by content, event, or type..."
            icon="s-magnifying-glass"
            class="flex-1 min-w-64" />

        <x-mary-select
            label="Type"
            wire:model.live="typeFilter"
            :options="$typeOptions"
            class="w-48" />

        <x-mary-select
            label="Status"
            wire:model.live="statusFilter"
            :options="[
                ['id' => '', 'name' => 'All Status'],
                ['id' => 'unread', 'name' => 'Unread'],
                ['id' => 'read', 'name' => 'Read'],
                ['id' => 'archived', 'name' => 'Archived'],
            ]"
            class="w-32" />

        <x-mary-button
            icon="s-funnel"
            class="btn-ghost btn-sm"
            wire:click="clearFilters"
            tooltip="Clear Filters" />
    </div>
</x-mary-card>

{{-- Notifications List --}}
<x-mary-card>
    <div class="space-y-4">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
                $createdAt = Illuminate\Support\Carbon::parse($notification->created_at);
                $timeAgo = $createdAt->diffForHumans();

                $colorMap = [
                    'primary' => 'primary',
                    'success' => 'success',
                    'error' => 'error',
                    'warning' => 'warning',
                    'info' => 'info',
                    'secondary' => 'secondary',
                ];
                $color = $colorMap[$data['color'] ?? 'primary'] ?? 'primary';
                
                // Determine notification URL based on type and user role
                $type = $data['type'] ?? '';
                $ticketNumber = $data['ticket_number'] ?? null;
                $url = null;
                
                if (str_starts_with($type, 'ticket_') && $ticketNumber) {
                    $url = route('student-org.my-tickets');
                }
                
                // Fallback to notifications page
                if (!$url) {
                    $url = route('student-org.notifications');
                }
            @endphp

            <a href="{{ $url }}" wire:navigate wire:click="markAsRead('{{ $notification->id }}')" 
                class="flex items-start gap-4 p-4 bg-{{ $color }}/5 rounded-lg border-l-4 border-{{ $color }} hover:shadow-md transition-shadow {{ !$isUnread ? 'opacity-75' : '' }} block">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-{{ $color }}/20 rounded-full flex items-center justify-center">
                        <x-mary-icon name="s-bell" class="w-5 h-5 text-{{ $color }}" />
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="font-semibold text-base-content">
                                {{ $data['title'] ?? 'Notification' }}
                            </p>
                            <p class="text-sm text-base-content/70 mt-1">
                                {{ $data['message'] ?? 'No message' }}
                            </p>

                            <div class="flex items-center gap-4 mt-2 text-xs text-base-content/60">
                                @if ($showTicketNumber && isset($data['ticket_number']))
                                    <span class="flex items-center gap-1">
                                        <x-mary-icon name="s-ticket" class="w-3 h-3" />
                                        <span>{{ $data['ticket_number'] }}</span>
                                    </span>
                                @endif
                                <span class="flex items-center gap-1">
                                    <x-mary-icon name="s-clock" class="w-3 h-3" />
                                    <span>{{ $timeAgo }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 ml-4">
                            @if ($isUnread)
                                <div class="w-3 h-3 bg-{{ $color }} rounded-full" title="Unread"></div>
                            @endif
                        </div>
                    </div>
                </div>
            </a>

            @if (!$loop->last)
                <div class="divider my-0"></div>
            @endif
        @empty
            <div class="text-center py-12">
                <x-mary-icon name="s-bell" class="w-16 h-16 text-base-content/20 mx-auto mb-4" />
                <p class="text-base-content/70 text-lg font-medium">No notifications found</p>
                <p class="text-base-content/50 text-sm mt-2">You're all caught up!</p>
            </div>
        @endforelse
    </div>
</x-mary-card>
