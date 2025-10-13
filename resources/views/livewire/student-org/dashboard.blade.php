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
                <x-mary-stat title="Total Tickets" description="Submitted requests" value="12" icon="s-ticket"
                    color="text-primary" />

                <x-mary-stat title="Pending" description="Awaiting approval" value="3" icon="s-clock"
                    color="text-warning" />

                <x-mary-stat title="Approved" description="Ready to proceed" value="7" icon="s-check-circle"
                    color="text-success" />

                <x-mary-stat title="Upcoming Events" description="Next 30 days" value="2" icon="s-calendar-days"
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
                ]" :rows="[
                    [
                        'id' => 'TKT-001',
                        'title' => 'Annual Org Meeting',
                        'date' => '2025-10-15',
                        'status' => 'Pending',
                        'submitted' => '2025-09-28',
                    ],
                    [
                        'id' => 'TKT-002',
                        'title' => 'Fundraising Event',
                        'date' => '2025-11-01',
                        'status' => 'Approved',
                        'submitted' => '2025-09-25',
                    ],
                    [
                        'id' => 'TKT-003',
                        'title' => 'Workshop Series',
                        'date' => '2025-10-20',
                        'status' => 'Under Review',
                        'submitted' => '2025-09-27',
                    ],
                ]">
                    @scope('cell_status', $row)
                        @if ($row['status'] === 'Approved')
                            <x-mary-badge value="{{ $row['status'] }}" class="badge-success" />
                        @elseif($row['status'] === 'Pending')
                            <x-mary-badge value="{{ $row['status'] }}" class="badge-warning" />
                        @else
                            <x-mary-badge value="{{ $row['status'] }}" class="badge-info" />
                        @endif
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
                    <div class="flex items-center space-x-4 p-4 bg-base-200 rounded-lg">
                        <div class="flex-shrink-0">
                            <x-mary-icon name="s-calendar-days" class="w-6 h-6 text-success" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold">Fundraising Event</h4>
                            <p class="text-sm text-gray-600">November 1, 2025 • 2:00 PM - 6:00 PM</p>
                            <p class="text-sm text-gray-500">Student Center Auditorium</p>
                        </div>
                        <div>
                            <x-mary-badge value="Confirmed" class="badge-success" />
                        </div>
                    </div>

                    <div class="flex items-center space-x-4 p-4 bg-base-200 rounded-lg">
                        <div class="flex-shrink-0">
                            <x-mary-icon name="s-calendar-days" class="w-6 h-6 text-info" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold">Workshop Series</h4>
                            <p class="text-sm text-gray-600">October 20, 2025 • 10:00 AM - 12:00 PM</p>
                            <p class="text-sm text-gray-500">Room 301, Building A</p>
                        </div>
                        <div>
                            <x-mary-badge value="Pending Final Approval" class="badge-warning" />
                        </div>
                    </div>
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
