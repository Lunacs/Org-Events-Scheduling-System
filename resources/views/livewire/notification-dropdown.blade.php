@php
use Illuminate\Support\Carbon;
use App\Models\User;
@endphp

<div wire:poll.60s.visible="loadNotifications">
    <!-- Notifications Dropdown -->
    <x-ui.dropdown right>
        {{-- Trigger Button --}}
        <x-slot:trigger>
            <div class="btn btn-ghost btn-sm btn-square relative">
                <x-heroicon-o-bell class="size-5" aria-hidden="true" />
                <span class="sr-only">Notifications{{ $unreadCount > 0 ? ', ' . $unreadCount . ' unread' : '' }}</span>
                @if ($unreadCount > 0)
                <span aria-hidden="true"
                    class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-error px-1 text-[0.6875rem] font-semibold tabular-nums text-error-content ring-2 ring-base-100">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
                @endif
            </div>
        </x-slot:trigger>

        {{-- Dropdown Content --}}
        <div class="flex max-h-[min(500px,80vh)] w-[min(22rem,calc(100vw-2rem))] flex-col overflow-hidden rounded-box border border-base-300 bg-base-100">
            {{-- Header Section --}}
            <div class="shrink-0 border-b border-base-300 px-4 py-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-base-content">Notifications</h2>
                    <div class="flex items-center gap-2">
                        @if ($unreadCount > 0)
                        <span class="badge badge-soft badge-error badge-sm tabular-nums">{{ $unreadCount }} unread</span>
                        @endif
                        <button type="button" wire:click="loadNotifications" class="btn btn-xs btn-ghost btn-square" aria-label="Refresh notifications">
                            <x-heroicon-o-arrow-path class="size-4" wire:loading.class="animate-spin" wire:target="loadNotifications" />
                        </button>
                    </div>
                </div>
                @if ($unreadCount > 0)
                <button type="button" wire:click="markAllAsRead" class="btn btn-xs btn-ghost mt-2 w-full">
                    Mark all as read
                </button>
                @endif
            </div>

            {{-- Scrollable Notifications List --}}
            <div class="flex-1 overflow-y-auto min-h-0">
                @forelse($notifications as $notification)
                @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
                $createdAt = Carbon::parse($notification->created_at);
                $timeAgo = $createdAt->diffForHumans();

                // Determine notification URL based on type and user role
                $type = $data['type'] ?? '';
                $ticketNumber = $data['ticket_number'] ?? null;
                // Ticket links are built for the reader's portal; older notifications may
                // have stored a link into another portal.
                $url = str_starts_with($type, 'ticket_') && $ticketNumber
                    ? Auth::user()->ticketUrl($ticketNumber)
                    : ($data['action_url'] ?? null);

                // Fallback to notifications page
                if (!$url) {
                $url = match (Auth::user()->portal()) {
                'superadmin' => route('superadmin.notifications'),
                'osa' => route('admin.notifications'),
                'gso' => route('gso.notifications'),
                'student-org' => route('student-org.notifications'),
                default => route('portal.notifications'),
                };
                }
                @endphp

                <a href="{{ $url }}" wire:navigate wire:click="markAsRead('{{ $notification->id }}')"
                    class="block px-4 py-3 transition-colors hover:bg-base-200 {{ $isUnread ? 'bg-primary/[0.04]' : '' }}">
                    <div class="flex items-start gap-3 w-full">
                        <span aria-hidden="true"
                            class="mt-1.5 size-2 shrink-0 rounded-full {{ $isUnread ? 'bg-info' : 'bg-transparent' }}"></span>
                        @if ($isUnread)<span class="sr-only">Unread:</span>@endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm {{ $isUnread ? 'font-semibold' : 'font-medium' }} text-base-content">
                                {{ $data['title'] ?? 'Notification' }}
                            </p>
                            <p class="mt-0.5 line-clamp-2 text-xs text-base-content/70">
                                {{ $data['message'] ?? 'No message' }}
                            </p>
                            <p class="text-xs text-base-content/60 mt-1">{{ $timeAgo }}</p>
                        </div>
                    </div>
                </a>

                @empty
                <div class="px-4 py-10 text-center">
                    <x-heroicon-o-bell class="mx-auto mb-2 size-6 text-base-content/40" aria-hidden="true" />
                    <p class="text-sm font-medium text-base-content">You're all caught up</p>
                    <p class="mt-0.5 text-xs text-base-content/70">Ticket updates from OSA and GSO appear here.</p>
                </div>
                @endforelse
            </div>

            {{-- Footer Section --}}
            <div class="shrink-0 border-t border-base-300 rounded-b-box">
                @php
                $route = match (Auth::user()->portal()) {
                'superadmin' => 'superadmin.notifications',
                'osa' => 'admin.notifications',
                'gso' => 'gso.notifications',
                'student-org' => 'student-org.notifications',
                default => 'portal.notifications',
                };
                @endphp
                <a href="{{ route($route) }}" wire:navigate
                    class="block px-4 py-3 text-center text-sm font-medium text-primary transition-colors hover:bg-base-200">
                    View all notifications
                </a>
            </div>
        </div>
    </x-ui.dropdown>
</div>