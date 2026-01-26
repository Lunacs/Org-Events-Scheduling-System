<div x-data="{
    firstLoad: true,
    showFilters: true
}" x-init="$nextTick(() => firstLoad = false)">

    {{-- Skeleton Loading State (First Load Only) --}}
    <div x-show="firstLoad" x-cloak>
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">
                {{-- Header Skeleton --}}
                <div class="bg-base-100 rounded-box shadow-lg p-6 animate-pulse">
                    <div class="h-8 bg-base-200 rounded w-1/4 mb-4"></div>
                    <div class="h-4 bg-base-200 rounded w-1/2"></div>
                </div>
                {{-- Stats Skeleton --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="bg-base-100 rounded-box shadow-lg p-4 animate-pulse">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-base-200 rounded-full"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-6 bg-base-200 rounded w-1/2"></div>
                                    <div class="h-3 bg-base-200 rounded w-1/3"></div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
                {{-- Filters Skeleton --}}
                <div class="bg-base-100 rounded-box shadow-lg p-6 animate-pulse">
                    <div class="h-12 bg-base-200 rounded w-full"></div>
                </div>
                {{-- List Skeleton --}}
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    @include('livewire.placeholders.notification-list')
                </div>
            </div>
        </div>
    </div>

    {{-- Actual Content --}}
    <div x-show="!firstLoad" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100">

        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">

                {{-- Header Section --}}
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-base-content">Notifications Center</h1>
                            <p class="text-base-content/70 mt-1">Stay updated on your event requests and university
                                announcements</p>
                        </div>
                        <div class="flex flex-col md:flex-row items-center gap-2">
                            <x-mary-button label="Mark All as Read" icon="s-check"
                                class="btn-ghost btn-sm cursor-pointer" wire:click="markAllAsRead" :disabled="$unreadCount === 0" />
                            <x-mary-button label="Clear All Read" icon="s-trash"
                                class="btn-ghost btn-sm cursor-pointer {{ $readCount > 0 ? 'text-error' : '' }}"
                                wire:click="clearAllRead" :disabled="$readCount === 0"
                                wire:confirm="Are you sure you want to clear all read notifications? This cannot be undone." />
                        </div>
                    </div>
                </div>

                {{-- Notification Stats --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-base-100 rounded-box shadow-lg p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-error/20 rounded-full flex items-center justify-center">
                                <x-mary-icon name="s-bell" class="w-6 h-6 text-error" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-base-content">{{ $unreadCount }}</p>
                                <p class="text-sm text-base-content/60">Unread</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-100 rounded-box shadow-lg p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-info/20 rounded-full flex items-center justify-center">
                                <x-mary-icon name="s-clock" class="w-6 h-6 text-info" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-base-content">{{ $todayCount }}</p>
                                <p class="text-sm text-base-content/60">Today</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-100 rounded-box shadow-lg p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-success/20 rounded-full flex items-center justify-center">
                                <x-mary-icon name="s-calendar-days" class="w-6 h-6 text-success" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-base-content">{{ $weekCount }}</p>
                                <p class="text-sm text-base-content/60">This Week</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-100 rounded-box shadow-lg p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-secondary/20 rounded-full flex items-center justify-center">
                                <x-mary-icon name="s-archive-box" class="w-6 h-6 text-secondary" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-base-content">{{ $totalCount }}</p>
                                <p class="text-sm text-base-content/60">Total</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative">
                            <input wire:model.live.debounce.300ms="search" type="text"
                                placeholder="Search notifications..." class="input input-bordered w-full pr-10" />
                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                <span wire:loading.remove wire:target="search">
                                    <svg class="w-5 h-5 text-base-content/40" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </span>
                                <span wire:loading wire:target="search"
                                    class="loading loading-spinner loading-sm"></span>
                            </div>
                        </div>

                        <div class="relative">
                            <select wire:model.live="typeFilter" class="select select-bordered w-full">
                                <option value="">All Types</option>
                                <option value="ticket_status_approved">Ticket Approved</option>
                                <option value="ticket_status_for_revision">Ticket For Revision</option>
                                <option value="ticket_status_gso_review">Under GSO Review</option>
                                <option value="ticket_status_pending_osa_approval">Pending OSA Approval</option>
                                <option value="ticket_status_amended">Ticket Amended</option>
                                <option value="ticket_comment">Comments</option>
                            </select>
                            <div class="absolute inset-y-0 right-10 flex items-center pointer-events-none">
                                <span wire:loading wire:target="typeFilter"
                                    class="loading loading-spinner loading-sm"></span>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <select wire:model.live="statusFilter" class="select select-bordered flex-1">
                                <option value="">All Status</option>
                                <option value="unread">Unread</option>
                                <option value="read">Read</option>
                            </select>
                            <button wire:click="clearFilters" type="button" class="btn btn-ghost"
                                x-show="$wire.search || $wire.typeFilter || $wire.statusFilter" x-transition
                                wire:loading.attr="disabled" wire:target="clearFilters">
                                <svg wire:loading.remove wire:target="clearFilters" class="w-4 h-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span wire:loading wire:target="clearFilters"
                                    class="loading loading-spinner loading-sm"></span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Notifications List --}}
                <div class="bg-base-100 rounded-box shadow-lg p-6 relative min-h-[400px]">
                    {{-- Skeleton Loader (Filtering/Searching) --}}
                    <div wire:loading wire:target="search,typeFilter,statusFilter,clearFilters" class="mb-4 w-full">
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

                                // Determine notification URL based on type
                                $type = $data['type'] ?? '';
                                $ticketNumber = $data['ticket_number'] ?? null;
                                $url = $ticketNumber
                                    ? route('student-org.my-tickets')
                                    : route('student-org.notifications');

                                // Map colors to Tailwind classes
                                $bgClass = match ($color) {
                                    'success' => 'bg-success/5 border-success hover:bg-success/10',
                                    'error' => 'bg-error/5 border-error hover:bg-error/10',
                                    'warning' => 'bg-warning/5 border-warning hover:bg-warning/10',
                                    'info' => 'bg-info/5 border-info hover:bg-info/10',
                                    'secondary' => 'bg-secondary/5 border-secondary hover:bg-secondary/10',
                                    default => 'bg-primary/5 border-primary hover:bg-primary/10',
                                };

                                $iconBgClass = match ($color) {
                                    'success' => 'bg-success/20',
                                    'error' => 'bg-error/20',
                                    'warning' => 'bg-warning/20',
                                    'info' => 'bg-info/20',
                                    'secondary' => 'bg-secondary/20',
                                    default => 'bg-primary/20',
                                };

                                $iconTextClass = match ($color) {
                                    'success' => 'text-success',
                                    'error' => 'text-error',
                                    'warning' => 'text-warning',
                                    'info' => 'text-info',
                                    'secondary' => 'text-secondary',
                                    default => 'text-primary',
                                };

                                $dotClass = match ($color) {
                                    'success' => 'bg-success',
                                    'error' => 'bg-error',
                                    'warning' => 'bg-warning',
                                    'info' => 'bg-info',
                                    'secondary' => 'bg-secondary',
                                    default => 'bg-primary',
                                };
                            @endphp

                            <div
                                class="flex items-start gap-4 p-4 {{ $bgClass }} rounded-lg border-l-4 hover:shadow-md transition-all {{ !$isUnread ? 'opacity-75' : '' }} group">
                                <div class="shrink-0">
                                    <div
                                        class="w-10 h-10 {{ $iconBgClass }} rounded-full flex items-center justify-center">
                                        <x-mary-icon name="s-bell" class="w-5 h-5 {{ $iconTextClass }}" />
                                    </div>
                                </div>
                                <a href="{{ $url }}" wire:navigate
                                    wire:click="markAsRead('{{ $notification->id }}')" class="flex-1 min-w-0 block">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="font-semibold text-base-content">
                                                {{ $data['title'] ?? 'Notification' }}
                                            </p>
                                            <p class="text-sm text-base-content/70 mt-1">
                                                {{ $data['message'] ?? 'No message' }}
                                            </p>

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
                                                <div class="w-3 h-3 {{ $dotClass }} rounded-full" title="Unread">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                                <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click.prevent="deleteNotification('{{ $notification->id }}')"
                                        wire:confirm="Delete this notification? This action cannot be undone."
                                        class="btn btn-ghost btn-sm btn-circle text-error hover:bg-error/10"
                                        title="Delete notification">
                                        <x-mary-icon name="s-trash" class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            @if (!$loop->last)
                                <div class="divider my-0"></div>
                            @endif
                        @empty
                            <div class="text-center py-12">
                                <x-mary-icon name="s-bell-slash"
                                    class="w-16 h-16 text-base-content/20 mx-auto mb-4" />
                                <p class="text-base-content/70 text-lg font-medium">No notifications found</p>
                                <p class="text-base-content/50 text-sm mt-2">
                                    <span x-show="$wire.search || $wire.typeFilter || $wire.statusFilter">Try adjusting
                                        your
                                        filters</span>
                                    <span x-show="!$wire.search && !$wire.typeFilter && !$wire.statusFilter">You're all
                                        caught
                                        up!</span>
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Pagination --}}
                @if ($notifications->hasPages())
                    <x-tickets.ticket-pagination :notifications="$notifications" />
                @endif

                {{-- Notification Settings --}}
                <div class="bg-base-100 rounded-box shadow-lg p-6">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-base-content">Notification Preferences</h3>
                        <p class="text-sm text-base-content/60 mt-1">Customize how you receive notifications</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-semibold mb-3 text-base-content">Email Notifications</h4>
                            <div class="space-y-2">
                                <x-mary-checkbox label="Event approvals and rejections" checked />
                                <x-mary-checkbox label="Revision requests" checked />
                                <x-mary-checkbox label="Event reminders" checked />
                                <x-mary-checkbox label="Reschedule updates" checked />
                                <x-mary-checkbox label="General announcements" />
                                <x-mary-checkbox label="System maintenance" />
                            </div>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-3 text-base-content">Reminder Settings</h4>
                            <div class="space-y-3">
                                <x-mary-select label="Event Reminders" :options="[
                                    ['id' => '1', 'name' => '1 day before'],
                                    ['id' => '3', 'name' => '3 days before'],
                                    ['id' => '5', 'name' => '5 days before'],
                                    ['id' => '7', 'name' => '1 week before'],
                                    ['id' => 'none', 'name' => 'No reminders'],
                                ]" value="5" />

                                <x-mary-select label="Deadline Reminders" :options="[
                                    ['id' => '1', 'name' => '1 day before deadline'],
                                    ['id' => '2', 'name' => '2 days before deadline'],
                                    ['id' => '3', 'name' => '3 days before deadline'],
                                    ['id' => 'none', 'name' => 'No deadline reminders'],
                                ]" value="2" />
                            </div>

                            <div class="mt-4">
                                <x-mary-button label="Save Settings" icon="s-check" class="btn-primary btn-sm" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
