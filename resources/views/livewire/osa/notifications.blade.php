<div x-data="{ firstLoad: true }" x-init="$nextTick(() => firstLoad = false)">

    {{-- Skeleton Loading State (First Load Only) --}}
    <div x-show="firstLoad" x-cloak>
        @include('livewire.osa.placeholders.notifications')
    </div>

    {{-- Actual Content --}}
    <div x-show="!firstLoad" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">

        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">

                {{-- Header Section --}}
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-base-content">Notifications Center</h3>
                        <p class="text-sm text-base-content/60 mt-1">Stay updated on ticket submissions, approvals, and
                            system
                            updates</p>
                    </div>
                    <div class="flex gap-2">
                        <x-mary-button label="Mark All as Read" icon="s-check" class="btn-ghost btn-sm"
                            wire:click="markAllAsRead" />
                        <x-mary-button label="Settings" icon="s-cog-6-tooth" class="btn-ghost btn-sm"
                            wire:click="openNotificationSettings" />
                    </div>
                </div>

                {{-- Notification Stats --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-mary-stat title="Unread" value="{{ $unreadCount }}" icon="s-bell" color="text-error" />

                    <x-mary-stat title="Today" value="{{ $todayCount }}" icon="s-clock" color="text-info" />

                    <x-mary-stat title="This Week" value="{{ $weekCount }}" icon="s-calendar-days"
                        color="text-success" />

                    <x-mary-stat title="Total" value="{{ $totalCount }}" icon="s-archive-box"
                        color="text-secondary" />
                </div>

                {{-- Filter and Search --}}
                <x-mary-card>
                    <div class="flex flex-wrap gap-4 items-end">
                        <x-mary-input label="Search Notifications" wire:model.live="search"
                            placeholder="Search by content, event, or type..." icon="s-magnifying-glass"
                            class="flex-1 min-w-64" />

                        <x-mary-select label="Type" wire:model.live="typeFilter" :options="[
                            ['id' => '', 'name' => 'All Types'],
                            ['id' => 'ticket_status', 'name' => 'Ticket Status Updates'],
                            ['id' => 'ticket_status_approved', 'name' => 'Ticket Approved'],
                            ['id' => 'ticket_status_rejected', 'name' => 'Ticket Rejected'],
                            ['id' => 'ticket_status_needs_revision', 'name' => 'Revision Required'],
                            ['id' => 'ticket_status_gso_review', 'name' => 'GSO Review'],
                            ['id' => 'ticket_status_for_rescheduling', 'name' => 'Rescheduling'],
                        ]" class="w-48" />

                        <x-mary-select label="Status" wire:model.live="statusFilter" :options="[
                            ['id' => '', 'name' => 'All Status'],
                            ['id' => 'unread', 'name' => 'Unread'],
                            ['id' => 'read', 'name' => 'Read'],
                        ]" class="w-32" />

                        <x-mary-button icon="s-funnel" class="btn-ghost btn-sm" wire:click="clearFilters"
                            tooltip="Clear Filters" />
                    </div>
                </x-mary-card>

                {{-- Notifications List --}}
                <x-mary-card>
                    {{-- Loading Skeleton during search/filter --}}
                    <div wire:loading.delay wire:target="search,typeFilter,statusFilter,clearFilters"
                        class="space-y-4 animate-pulse">
                        @for ($i = 0; $i < 5; $i++)
                            <div class="flex items-start gap-4 p-4 bg-base-200 rounded-lg">
                                <div class="w-10 h-10 bg-base-300 rounded-full"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-4 bg-base-300 rounded w-3/4"></div>
                                    <div class="h-3 bg-base-300 rounded w-full"></div>
                                    <div class="h-3 bg-base-300 rounded w-1/2"></div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    {{-- Actual Notifications --}}
                    <div wire:loading.remove.delay wire:target="search,typeFilter,statusFilter,clearFilters"
                        class="space-y-4">
                        @forelse($notifications as $notification)
                            @php
                                $data = $notification->data;
                                $isUnread = is_null($notification->read_at);
                                $createdAt = Illuminate\Support\Carbon::parse($notification->created_at);
                                $timeAgo = $createdAt->diffForHumans();

                                // Map notification colors
                                $colorMap = [
                                    'primary' => 'primary',
                                    'success' => 'success',
                                    'error' => 'error',
                                    'warning' => 'warning',
                                    'info' => 'info',
                                    'secondary' => 'secondary',
                                ];
                                $color = $colorMap[$data['color'] ?? 'primary'] ?? 'primary';
                            @endphp

                            <div class="flex items-start gap-4 p-4 bg-{{ $color }}/5 rounded-lg border-l-4 border-{{ $color }} hover:shadow-md transition-shadow cursor-pointer {{ !$isUnread ? 'opacity-75' : '' }}"
                                wire:click="markAsRead('{{ $notification->id }}')">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-{{ $color }}/20 rounded-full flex items-center justify-center">
                                        <x-mary-icon name="s-bell" class="w-5 h-5 text-{{ $color }}" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="font-semibold text-base-content">
                                                {{ $data['title'] ?? 'Notification' }}</p>
                                            <p class="text-sm text-base-content/70 mt-1">
                                                {{ $data['message'] ?? 'No message' }}</p>

                                            @if (isset($data['ticket_number']))
                                                <div class="flex items-center gap-4 mt-2 text-xs text-base-content/60">
                                                    <span class="flex items-center gap-1">
                                                        <x-mary-icon name="s-ticket" class="w-3 h-3" />
                                                        <span>{{ $data['ticket_number'] }}</span>
                                                    </span>
                                                    <span class="flex items-center gap-1">
                                                        <x-mary-icon name="s-clock" class="w-3 h-3" />
                                                        <span>{{ $timeAgo }}</span>
                                                    </span>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-4 mt-2 text-xs text-base-content/60">
                                                    <span class="flex items-center gap-1">
                                                        <x-mary-icon name="s-clock" class="w-3 h-3" />
                                                        <span>{{ $timeAgo }}</span>
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex flex-col gap-2 ml-4">
                                            @if ($isUnread)
                                                <div class="w-3 h-3 bg-{{ $color }} rounded-full"
                                                    title="Unread">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

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

                {{-- Notification Settings --}}
                <x-mary-card title="Notification Preferences" subtitle="Customize how you receive notifications">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-semibold mb-3 text-base-content">Email Notifications</h4>
                            <div class="space-y-2">
                                <x-mary-checkbox label="New ticket submissions" checked />
                                <x-mary-checkbox label="Schedule conflicts" checked />
                                <x-mary-checkbox label="Approval requests from GSO" checked />
                                <x-mary-checkbox label="Event completion notices" checked />
                                <x-mary-checkbox label="System announcements" />
                                <x-mary-checkbox label="Weekly summary reports" />
                            </div>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-3 text-base-content">Reminder Settings</h4>
                            <div class="space-y-3">
                                <x-mary-select label="Review Reminders" :options="[
                                    ['id' => '24', 'name' => 'After 24 hours'],
                                    ['id' => '48', 'name' => 'After 48 hours'],
                                    ['id' => '72', 'name' => 'After 72 hours'],
                                    ['id' => 'none', 'name' => 'No reminders'],
                                ]" value="48" />

                                <x-mary-select label="Daily Digest" :options="[
                                    ['id' => '8', 'name' => '8:00 AM'],
                                    ['id' => '9', 'name' => '9:00 AM'],
                                    ['id' => '10', 'name' => '10:00 AM'],
                                    ['id' => 'none', 'name' => 'No daily digest'],
                                ]" value="9" />
                            </div>

                            <div class="mt-4">
                                <x-mary-button label="Save Settings" icon="s-check" class="btn-primary btn-sm" />
                            </div>
                        </div>
                    </div>
                </x-mary-card>
            </div>
        </div>
    </div>
</div>
