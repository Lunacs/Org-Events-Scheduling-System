<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Student Organization Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Welcome Card --}}
            <x-mary-card title="Welcome Back!" subtitle="Here's an overview of your organization's activities">
                <div class="flex items-center space-x-4">
                    <x-mary-icon name="s-building-office" class="w-8 h-8 text-primary" />
                    <div>
                        <h3 class="text-lg font-semibold">{{ auth()->user()->name ?? 'Student Organization' }}</h3>
                        <p class="text-sm text-gray-600">Organization Dashboard</p>
                    </div>
                </div>
            </x-mary-card>

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-mary-stat title="Total Tickets" description="Submitted requests" value="{{ $tickets->count() }}" icon="s-ticket"
                    color="text-primary" />

                <x-mary-stat title="Pending" description="Awaiting approval" value="{{ $tickets->whereNotIn('status', ['approved'])->count() }}" icon="s-clock"
                    color="text-warning" />

                <x-mary-stat title="Approved" description="Ready to proceed" value="{{ $tickets->where('status', 'approved')->count() }}" icon="s-check-circle"
                    color="text-success" />

                <x-mary-stat title="Upcoming Events" description="Next 30 days" value="{{ $upcomingEvents->count() }}" icon="s-calendar-days"
                    color="text-info" />
            </div>

            {{-- Recent Tickets --}}
            <x-mary-card title="Recent Ticket Submissions" subtitle="Your latest event requests">
                <x-slot:menu>
                    <x-mary-button label="View All" link="/student-org/my-tickets" icon="s-eye"
                        class="btn-sm btn-ghost" wire:navigate />
                </x-slot:menu>

                {{-- Desktop: Table View --}}
                <div class="hidden md:block">
                    <x-mary-table :headers="[
                        ['key' => 'id', 'label' => '#'],
                        ['key' => 'title', 'label' => 'Event Title'],
                        ['key' => 'date', 'label' => 'Event Date'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'submitted', 'label' => 'Submitted'],
                    ]" :rows="$tickets">
                        @scope('cell_id', $ticket)
                        {{ $ticket->ticket_number }}
                        @endscope

                        @scope('cell_title', $ticket)
                        {{ $ticket->event_name ?? $ticket->title }}
                        @endscope

                        @scope('cell_date', $ticket)
                        @php
                        $dateFrom = \Carbon\Carbon::parse($ticket->date_from);
                        $dateTo = \Carbon\Carbon::parse($ticket->date_to);
                        @endphp
                        @if($dateFrom->isSameDay($dateTo))
                        {{ $dateFrom->format('M j, Y') }}
                        @else
                        {{ $dateFrom->format('M j, Y') }} - {{ $dateTo->format('M j, Y') }}
                        @endif
                        @endscope

                        @scope('cell_status', $ticket)
                        <x-tickets.progress-badge :status="$ticket->status" />
                        @endscope

                        @scope('cell_submitted', $ticket)
                        {{ $ticket->created_at->format('M j, Y') }}
                        @endscope
                    </x-mary-table>
                </div>

                {{-- Mobile: Card View --}}
                <div class="md:hidden space-y-3">
                    @forelse($tickets as $ticket)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ $ticket->event_name ?? $ticket->title }}</p>
                                <p class="text-xs text-gray-500">#{{ $ticket->ticket_number }}</p>
                            </div>
                            <x-tickets.progress-badge :status="$ticket->status" />
                        </div>
                        <div class="flex items-center gap-1 text-sm text-gray-600">
                            <x-mary-icon name="s-calendar" class="w-4 h-4 text-gray-400" />
                            @php
                            $dateFrom = \Carbon\Carbon::parse($ticket->date_from);
                            $dateTo = \Carbon\Carbon::parse($ticket->date_to);
                            @endphp
                            @if($dateFrom->isSameDay($dateTo))
                            <span>{{ $dateFrom->format('M j, Y') }}</span>
                            @else
                            <span>{{ $dateFrom->format('M j, Y') }} - {{ $dateTo->format('M j, Y') }}</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <x-mary-icon name="s-ticket" class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                        <p class="text-gray-500 text-sm">No tickets submitted yet</p>
                    </div>
                    @endforelse
                </div>
            </x-mary-card>

            {{-- Upcoming Events --}}
            <x-mary-card title="Upcoming Approved Events" subtitle="Events scheduled for the next 30 days">
                <x-slot:menu>
                    <x-mary-button label="View Calendar" link="/student-org/calendar" icon="s-calendar"
                        class="btn-sm btn-ghost" wire:navigate />
                </x-slot:menu>

                <div class="space-y-4">
                    @foreach($upcomingEvents as $event)
                    <x-tickets.upc-events-card :ticket="$event" />
                    @endforeach
                </div>
            </x-mary-card>

            {{-- Quick Actions --}}
            <x-mary-card title="Quick Actions" subtitle="Frequently used actions">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-mary-button label="Submit New Ticket" icon="s-document-plus" class="btn-primary btn-lg"
                        link="/student-org/submit-ticket" wire:navigate />

                    <x-mary-button label="Check My Tickets" icon="s-ticket" class="btn-secondary btn-lg"
                        link="/student-org/my-tickets" wire:navigate />

                    <x-mary-button label="Request Reschedule" icon="s-arrow-path" class="btn-accent btn-lg"
                        link="/student-org/reschedule" wire:navigate />
                </div>
            </x-mary-card>

            {{-- Recent Notifications --}}
            <x-mary-card title="Recent Notifications" subtitle="Latest updates from OSA and GSO">
                <x-slot:menu>
                    <x-mary-button label="View All" link="/student-org/notifications" icon="s-bell"
                        class="btn-sm btn-ghost" wire:navigate />
                </x-slot:menu>

                <div class="space-y-3">
                    @forelse($recentNotifications as $notification)
                    @php
                    $data = $notification->data;
                    $createdAt = \Illuminate\Support\Carbon::parse($notification->created_at);
                    $timeAgo = $createdAt->diffForHumans();

                    $colorMap = [
                    'primary' => 'primary',
                    'success' => 'success',
                    'error' => 'error',
                    'warning' => 'warning',
                    'info' => 'info',
                    'secondary' => 'secondary',
                    ];
                    $color = $colorMap[$data['color'] ?? 'info'] ?? 'info';

                    $iconMap = [
                    'success' => 's-check-circle',
                    'warning' => 's-exclamation-triangle',
                    'error' => 's-x-circle',
                    'info' => 's-information-circle',
                    ];
                    $icon = $iconMap[$color] ?? 's-bell';
                    @endphp

                    <div class="flex items-start space-x-3 p-3 bg-{{ $color }}/10 rounded-lg border-l-4 border-{{ $color }}">
                        <x-mary-icon :name="$icon" class="w-5 h-5 text-{{ $color }} mt-0.5" />
                        <div class="flex-1">
                            <p class="font-medium">{{ $data['title'] ?? 'Notification' }}</p>
                            <p class="text-sm text-gray-600">{{ $data['message'] ?? 'No message' }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $timeAgo }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <x-mary-icon name="s-bell-slash" class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                        <p class="text-gray-500 text-sm">No recent notifications</p>
                    </div>
                    @endforelse
                </div>
            </x-mary-card>
        </div>
    </div>

</div>