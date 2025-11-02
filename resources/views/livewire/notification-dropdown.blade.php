@php
    use Illuminate\Support\Carbon;
@endphp

<div>
    <!-- Notifications Dropdown -->
    <div class="dropdown dropdown-end" x-data="{ open: false }" x-on:click="open = !open">
        <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle relative tooltip tooltip-bottom"
            data-tip="Notifications">
            <x-heroicon-s-bell class="h-5 w-5" />
            @if ($unreadCount > 0)
                <span class="absolute top-1 right-1 w-2 h-2 bg-error rounded-full"></span>
            @endif
        </div>
        <ul tabindex="0" wire:poll.visible.30s="loadNotifications"
            class="dropdown-content z-1 menu p-0 shadow-lg bg-base-100 rounded-box w-80 border border-base-300 mt-2 max-h-[400px] overflow-y-auto">
            {{-- Notifications Header --}}
            <li class="px-4 py-3 bg-base-200 rounded-t-box sticky top-0 z-10 pointer-events-none">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-base text-base-content">Notifications</h4>
                    <div class="flex items-center gap-2">
                        @if ($unreadCount > 0)
                            <span class="badge badge-error badge-sm">{{ $unreadCount }}</span>
                        @endif
                        <button wire:click="loadNotifications" class="btn btn-xs btn-ghost" title="Refresh">
                            <x-heroicon-s-arrow-path class="h-3 w-3" />
                        </button>
                    </div>
                </div>
                @if ($unreadCount > 0)
                    <button wire:click="markAllAsRead" class="btn btn-xs btn-ghost mt-2 pointer-events-auto">
                        Mark all as read
                    </button>
                @endif
            </li>

            {{-- Notification Items --}}
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $createdAt = Carbon::parse($notification->created_at);
                    $timeAgo = $createdAt->diffForHumans();
                @endphp

                <li>
                    <div wire:click="markAsRead('{{ $notification->id }}')"
                        class="py-3 px-4 hover:bg-base-200 transition-colors cursor-pointer {{ !$isUnread ? 'opacity-75' : '' }}">
                        <div class="flex items-start gap-3 w-full">
                            <div
                                class="w-2 h-2 {{ $isUnread ? 'bg-' . ($data['color'] ?? 'primary') : 'bg-base-300' }} rounded-full mt-2 shrink-0">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-base-content">{{ $data['title'] ?? 'Notification' }}
                                </p>
                                <p class="text-xs text-base-content/60 mt-1">{{ $data['message'] ?? 'No message' }}</p>
                                <p class="text-xs text-base-content/40 mt-1">{{ $timeAgo }}</p>
                            </div>
                        </div>
                    </div>
                </li>

                @if (!$loop->last)
                    <div class="divider my-0"></div>
                @endif
            @empty
                <li class="px-4 py-8 text-center">
                    <p class="text-sm text-base-content/60">No notifications</p>
                </li>
            @endforelse

            <div class="divider my-0"></div>

            {{-- View All Link --}}
            <li>
                <a href="{{ route('admin.notifications') }}" wire:navigate
                    class="py-3 px-4 text-center text-primary hover:bg-base-200 transition-colors font-medium">
                    View All Notifications
                </a>
            </li>
        </ul>
    </div>
</div>
