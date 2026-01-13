@php
use Illuminate\Support\Carbon;
use App\Models\User;
@endphp

<div wire:poll.30s="loadNotifications">
    <!-- Notifications Dropdown -->
    <x-mary-dropdown right>
        {{-- Trigger Button --}}
        <x-slot:trigger>
            <div class="btn btn-ghost btn-sm btn-circle relative">
                <x-heroicon-s-bell class="h-5 w-5" />
                @if ($unreadCount > 0)
                <span
                    class="absolute -top-1 -right-1 flex items-center justify-center min-w-5 h-5 px-1 bg-error text-neutral-content text-xs font-bold rounded-full">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
                @endif
            </div>
        </x-slot:trigger>

        {{-- Dropdown Content --}}
        <div class="w-80 max-h-[500px] flex flex-col overflow-hidden bg-base-100 rounded-box border border-base-300">
            {{-- Header Section --}}
            <div class="px-4 py-3 bg-base-200 rounded-t-box border-b border-base-300 shrink-0">
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
                <button wire:click="markAllAsRead" class="btn btn-xs btn-ghost mt-2 w-full">
                    Mark all as read
                </button>
                @endif
            </div>

            {{-- Scrollable Notifications List --}}
            <div class="flex-1 overflow-y-auto min-h-0 ">
                @forelse($notifications as $notification)
                @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
                $createdAt = Carbon::parse($notification->created_at);
                $timeAgo = $createdAt->diffForHumans();

                // Determine notification URL based on type and user role
                $type = $data['type'] ?? '';
                $ticketNumber = $data['ticket_number'] ?? null;
                $url = $data['action_url'] ?? null;

                if (!$url && str_starts_with($type, 'ticket_') && $ticketNumber) {
                if (Auth::user()->isStudentOrg()) {
                $url = route('student-org.my-tickets');
                } elseif (Auth::user()->isOSA() || Auth::user()->isSuperAdmin()) {
                $url = route('osa.ticket-review.show', $ticketNumber);
                } elseif (Auth::user()->isGSO()) {
                $url = route('gso.ticket-details', ['ticketNumber' => $ticketNumber]);
                }
                }

                // Fallback to notifications page
                if (!$url) {
                $url = match (true) {
                Auth::user()->isOSA() || Auth::user()->isSuperAdmin() => route('admin.notifications'),
                Auth::user()->isGSO() => route('gso.notifications'),
                Auth::user()->isStudentOrg() => route('student-org.notifications'),
                default => '#',
                };
                }
                @endphp

                <a href="{{ $url }}" wire:navigate wire:click="markAsRead('{{ $notification->id }}')"
                    class="block py-3 px-4 hover:bg-base-200 transition-colors {{ !$isUnread ? 'opacity-75' : '' }}">
                    <div class="flex items-start gap-3 w-full">
                        <div
                            class="w-2 h-2 {{ $isUnread ? 'bg-' . ($data['color'] ?? 'primary') : 'bg-base-300' }} rounded-full mt-2 shrink-0">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-base-content">
                                {{ $data['title'] ?? 'Notification' }}
                            </p>
                            <p class="text-xs text-base-content/60 mt-1">
                                {{ $data['message'] ?? 'No message' }}
                            </p>
                            <p class="text-xs text-base-content/40 mt-1">{{ $timeAgo }}</p>
                        </div>
                    </div>
                </a>

                @if (!$loop->last)
                <div class="divider my-0"></div>
                @endif
                @empty
                <div class="px-4 py-8 text-center">
                    <p class="text-sm text-base-content/60">No notifications</p>
                </div>
                @endforelse
            </div>

            {{-- Footer Section --}}
            <div class="shrink-0 border-t border-base-300 rounded-b-box">
                @php
                $route = match (Auth::user()->role_id) {
                User::getRoleId('superadmin') => 'superadmin.notifications',
                User::getRoleId('osa') => 'admin.notifications',
                User::getRoleId('gso') => 'gso.notifications',
                User::getRoleId('student-org') => 'student-org.notifications',
                default => 'admin.notifications',
                };
                @endphp
                <a href="{{ route($route) }}" wire:navigate
                    class="py-3 px-4 text-center text-primary hover:bg-base-200 transition-colors font-medium block rounded-b-box">
                    View All Notifications
                </a>
            </div>
        </div>
    </x-mary-dropdown>
</div>