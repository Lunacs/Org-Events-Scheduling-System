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

                <x-mary-table :headers="[
                    ['key' => 'id', 'label' => '#'],
                    ['key' => 'title', 'label' => 'Event Title'],
                    ['key' => 'date', 'label' => 'Requested Date'],
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
                    {{ \Carbon\Carbon::parse($ticket->date_from)->format('M j, Y') ?? 'N/A' }}
                    @endscope

                    @scope('cell_status', $ticket)
                    <x-tickets.progress-badge :status="$ticket->status" />
                    @endscope

                    @scope('cell_submitted', $ticket)
                    {{ $ticket->created_at->format('Y-m-d') }}
                    @endscope
                </x-mary-table>
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
                    <div class="flex items-start space-x-3 p-3 bg-success/10 rounded-lg border-l-4 border-success">
                        <x-mary-icon name="s-check-circle" class="w-5 h-5 text-success mt-0.5" />
                        <div>
                            <p class="font-medium">Event Approved: Fundraising Event</p>
                            <p class="text-sm text-gray-600">Your event request has been approved by OSA. You can now
                                proceed with preparations.</p>
                            <p class="text-xs text-gray-500 mt-1">2 hours ago</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3 p-3 bg-warning/10 rounded-lg border-l-4 border-warning">
                        <x-mary-icon name="s-exclamation-triangle" class="w-5 h-5 text-warning mt-0.5" />
                        <div>
                            <p class="font-medium">Additional Requirements Needed</p>
                            <p class="text-sm text-gray-600">Your Workshop Series request needs additional
                                documentation. Please check your tickets.</p>
                            <p class="text-xs text-gray-500 mt-1">1 day ago</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3 p-3 bg-info/10 rounded-lg border-l-4 border-info">
                        <x-mary-icon name="s-information-circle" class="w-5 h-5 text-info mt-0.5" />
                        <div>
                            <p class="font-medium">Reminder: Event Guidelines</p>
                            <p class="text-sm text-gray-600">Please review the updated event guidelines before your
                                next submission.</p>
                            <p class="text-xs text-gray-500 mt-1">3 days ago</p>
                        </div>
                    </div>
                </div>
            </x-mary-card>
        </div>
    </div>

</div>
