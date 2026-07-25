<div x-data="{
    firstLoad: true,
    showFilters: true
}" x-init="$nextTick(() => firstLoad = false)">

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
                <section
                    class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
                    <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
                    <div class="relative p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h1 class="text-3xl font-heading font-bold text-base-content">Notifications Center</h1>
                                <p class="text-base-content/70 mt-1">Stay updated on ticket submissions, approvals, and
                                    system
                                    updates</p>
                            </div>
                            <div class="flex flex-col md:flex-row items-center gap-2 relative z-10">
                                <x-ui.button label="Mark All as Read" icon="s-check"
                                    class="btn-ghost btn-sm cursor-pointer" wire:click="markAllAsRead" :disabled="$unreadCount === 0" />
                                <x-ui.button label="Clear All Read" icon="s-trash"
                                    class="btn-ghost btn-sm cursor-pointer {{ $readCount > 0 ? 'text-error' : '' }}"
                                    wire:click="clearAllRead" :disabled="$readCount === 0"
                                    wire:confirm="Are you sure you want to clear all read notifications? This cannot be undone." />
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Notification Stats --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-ui.metric-card label="Unread" value="{{ $unreadCount }}" icon="s-bell" color="error" />

                    <x-ui.metric-card label="Today" value="{{ $todayCount }}" icon="s-clock" color="info" />

                    <x-ui.metric-card label="This Week" value="{{ $weekCount }}" icon="s-calendar-days"
                        color="success" />

                    <x-ui.metric-card label="Total" value="{{ $totalCount }}" icon="s-archive-box"
                        color="secondary" />
                </div>

                {{-- Filters --}}
                <x-ui.card>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search notifications..."
                            icon="s-magnifying-glass" wire:loading.class="opacity-70" wire:target="search" />

                        <x-ui.select wire:model.live="typeFilter" :options="[
                            ['id' => '', 'name' => 'All Types'],
                            ['id' => 'announcement', 'name' => 'Announcements'],
                            ['id' => 'ticket_submitted', 'name' => 'New Submissions'],
                            ['id' => 'ticket_amended', 'name' => 'Amended Tickets'],
                            ['id' => 'gso_approved', 'name' => 'GSO Approved'],
                            ['id' => 'gso_for_revision', 'name' => 'GSO For Revision'],
                            ['id' => 'ticket_status_rescheduled', 'name' => 'Rescheduled'],
                            ['id' => 'ticket_status_for_rescheduling', 'name' => 'Rescheduling Requested'],
                            ['id' => 'ticket_comment', 'name' => 'Comments'],
                        ]" wire:loading.class="opacity-70" wire:target="typeFilter" />

                        <div class="flex gap-2">
                            <x-ui.select wire:model.live="statusFilter" class="flex-1" :options="[
                                ['id' => '', 'name' => 'All Status'],
                                ['id' => 'unread', 'name' => 'Unread'],
                                ['id' => 'read', 'name' => 'Read'],
                            ]" wire:loading.class="opacity-70" wire:target="statusFilter" />

                            @if ($search || $typeFilter || $statusFilter)
                                <x-ui.button icon="s-x-mark" class="btn-ghost" wire:click="clearFilters"
                                    wire:loading.attr="disabled" wire:target="clearFilters"
                                    tooltip="Clear Filters" />
                            @endif
                        </div>
                    </div>
                </x-ui.card>

                {{-- Notifications List --}}
                <x-ui.card>
                    {{-- Skeleton Loader (Filtering/Searching) --}}
                    <div wire:loading wire:target="search,typeFilter,statusFilter,clearFilters" class="w-full">
                        @include('livewire.placeholders.notification-list')
                    </div>

                    {{-- Notifications Grid --}}
                    <div class="space-y-4" wire:loading.remove
                        wire:target="search,typeFilter,statusFilter,clearFilters">
                        @forelse($notifications as $notification)
                            @php
                                $data = $notification->data;
                                $isUnread = is_null($notification->read_at);
                                $createdAt = Illuminate\Support\Carbon::parse($notification->created_at);
                                $timeAgo = $createdAt->diffForHumans();
                                $color = $data['color'] ?? 'primary';

                                $type = $data['type'] ?? '';
                                $ticketNumber = $data['ticket_number'] ?? null;
                                $url = str_starts_with($type, 'ticket_') && $ticketNumber
                                    ? route('osa.ticket-review.show', $ticketNumber)
                                    : route('admin.notifications');
                            @endphp

                            <a href="{{ $url }}" wire:navigate wire:click="markAsRead('{{ $notification->id }}')"
                                class="group flex items-start gap-4 p-4 bg-{{ $color }}/5 hover:bg-{{ $color }}/10 border border-{{ $color }}/20 rounded-lg transition-colors {{ !$isUnread ? 'opacity-70' : '' }}">
                                <div class="shrink-0 w-10 h-10 rounded-full bg-{{ $color }}/10 flex items-center justify-center">
                                    <x-ui.icon name="s-bell" class="w-5 h-5 text-{{ $color }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="font-semibold text-base-content">
                                            {{ $data['title'] ?? 'Notification' }}
                                        </p>
                                        @if ($isUnread)
                                            <div class="w-2.5 h-2.5 rounded-full bg-{{ $color }} shrink-0 mt-1"
                                                title="Unread"></div>
                                        @endif
                                    </div>
                                    <p class="text-sm text-base-content/70 mt-1">
                                        {{ $data['message'] ?? 'No message' }}
                                    </p>

                                    @if (($data['type'] ?? '') === 'announcement' && isset($data['content']) && is_string($data['content']))
                                        <div class="mt-2 p-3 bg-base-200/50 rounded-lg text-sm text-base-content/80">
                                            {{ $data['content'] }}
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-4 mt-2 text-xs text-base-content/50">
                                        @if ($ticketNumber)
                                            <span class="flex items-center gap-1">
                                                <x-ui.icon name="s-ticket" class="w-3 h-3" />
                                                {{ $ticketNumber }}
                                            </span>
                                        @endif
                                        <span class="flex items-center gap-1">
                                            <x-ui.icon name="s-clock" class="w-3 h-3" />
                                            {{ $timeAgo }}
                                        </span>
                                    </div>
                                </div>
                                <button type="button"
                                    wire:click.prevent.stop="deleteNotification('{{ $notification->id }}')"
                                    wire:confirm="Delete this notification? This action cannot be undone."
                                    class="shrink-0 btn btn-ghost btn-sm btn-circle text-error opacity-0 group-hover:opacity-100 transition-opacity"
                                    title="Delete notification">
                                    <x-ui.icon name="s-trash" class="w-4 h-4" />
                                </button>
                            </a>
                        @empty
                            <x-ui.empty-state title="No notifications found"
                                description="You're all caught up! Try adjusting your filters or check back later."
                                icon="s-bell-slash" tone="secondary" iconColor="text-secondary" />
                        @endforelse
                    </div>
                </x-ui.card>

                {{-- Pagination --}}
                @if ($notifications->hasPages())
                    <x-tickets.ticket-pagination :notifications="$notifications" />
                @endif


            </div>
        </div>
    </div>
</div>
