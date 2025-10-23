<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Tickets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Header Actions --}}
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">All Event Requests</h3>
                    <p class="text-sm text-gray-600">Track the progress of your submitted tickets</p>
                </div>
                <x-mary-button label="Submit New Ticket" icon="s-document-plus" class="btn-primary"
                    link="/student-org/submit-ticket" wire:navigate />
            </div>

            {{-- Filter and Search --}}
            <x-mary-card>
                <div class="flex flex-wrap gap-4 items-end">
                    <x-mary-input label="Search Tickets" wire:model.live="search"
                        placeholder="Search by title, ID, or description..." icon="s-magnifying-glass"
                        class="flex-1 min-w-64" />

                    <x-mary-select label="Status Filter" wire:model.live="statusFilter" :options="[
                        ['id' => '', 'name' => 'All Status'],
                        ['id' => 'draft', 'name' => 'Draft'],
                        ['id' => 'submitted', 'name' => 'Submitted'],
                        ['id' => 'under_review', 'name' => 'Under Review'],
                        ['id' => 'pending_osa', 'name' => 'Pending OSA Approval'],
                        ['id' => 'pending_gso', 'name' => 'Pending GSO Approval'],
                        ['id' => 'approved', 'name' => 'Approved'],
                        ['id' => 'rejected', 'name' => 'Rejected'],
                        ['id' => 'requires_revision', 'name' => 'Requires Revision'],
                    ]"
                        placeholder="Filter by status" />

                    <x-mary-select label="Date Range" wire:model.live="dateFilter" :options="[
                        ['id' => '', 'name' => 'All Time'],
                        ['id' => 'last_week', 'name' => 'Last Week'],
                        ['id' => 'last_month', 'name' => 'Last Month'],
                        ['id' => 'last_3_months', 'name' => 'Last 3 Months'],
                        ['id' => 'this_year', 'name' => 'This Year'],
                    ]"
                        placeholder="Filter by date" />

                    <x-mary-button icon="s-funnel" class="btn-ghost" wire:click="clearFilters" />
                </div>
            </x-mary-card>

            {{-- Tickets List --}}
            <x-mary-card>
                <div class="space-y-4">
                    {{-- Ticket Item 1 --}}
                    @foreach($tickets as $ticket)
                    <x-tickets.ticketinfo :tickets="$ticket" />

                    @endforeach

                    {{-- Ticket Item 2 --}}
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-lg font-semibold">Fundraising Concert</h4>
                                    <x-mary-badge value="Approved" class="badge-success" />
                                    <span class="text-sm text-gray-500">#TKT-002</span>
                                </div>
                                <p class="text-gray-600 mb-3">Musical concert event to raise funds for our community
                                    outreach programs and scholarship fund.</p>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="flex items-center space-x-2">
                                        <x-mary-icon name="s-calendar" class="w-4 h-4 text-gray-400" />
                                        <span class="text-sm">Nov 1, 2025 • 6:00 PM</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <x-mary-icon name="s-map-pin" class="w-4 h-4 text-gray-400" />
                                        <span class="text-sm">University Auditorium</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <x-mary-icon name="s-users" class="w-4 h-4 text-gray-400" />
                                        <span class="text-sm">300 attendees expected</span>
                                    </div>
                                </div>

                                {{-- Progress Steps - Completed --}}
                                <div class="mb-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center space-x-2">
                                            <div
                                                class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                                                <x-mary-icon name="s-check" class="w-4 h-4 text-white" />
                                            </div>
                                            <span class="text-sm font-medium">Submitted</span>
                                        </div>
                                        <div class="flex-1 h-0.5 bg-success"></div>
                                        <div class="flex items-center space-x-2">
                                            <div
                                                class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                                                <x-mary-icon name="s-check" class="w-4 h-4 text-white" />
                                            </div>
                                            <span class="text-sm font-medium">OSA Review</span>
                                        </div>
                                        <div class="flex-1 h-0.5 bg-success"></div>
                                        <div class="flex items-center space-x-2">
                                            <div
                                                class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                                                <x-mary-icon name="s-check" class="w-4 h-4 text-white" />
                                            </div>
                                            <span class="text-sm font-medium">GSO Review</span>
                                        </div>
                                        <div class="flex-1 h-0.5 bg-success"></div>
                                        <div class="flex items-center space-x-2">
                                            <div
                                                class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                                                <x-mary-icon name="s-check-circle" class="w-4 h-4 text-white" />
                                            </div>
                                            <span class="text-sm font-medium">Approved</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col space-y-2">
                                <x-mary-button icon="s-eye" class="btn-sm btn-ghost" tooltip="View Details" />
                                <x-mary-button icon="s-document-arrow-down" class="btn-sm btn-ghost"
                                    tooltip="Download Approval" />
                                <x-mary-button icon="s-chat-bubble-left-right" class="btn-sm btn-ghost"
                                    tooltip="Comments" />
                            </div>
                        </div>

                        {{-- Approval Notice --}}
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="bg-green-50 p-3 rounded-lg">
                                <div class="flex items-start space-x-3">
                                    <x-mary-icon name="s-check-circle" class="w-5 h-5 text-green-500 mt-0.5" />
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-green-700">Event Approved!</p>
                                        <p class="text-sm text-green-600 mt-1">Congratulations! Your event has been
                                            approved by both OSA and GSO. You may now proceed with your preparations.
                                            Please ensure to follow all safety guidelines and submit a post-event
                                            report.</p>
                                        <p class="text-xs text-green-500 mt-2">1 week ago</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-sm text-gray-500">
                            Submitted on September 25, 2025 • Approved on September 29, 2025
                        </div>
                    </div>

                    {{-- Ticket Item 3 --}}
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-lg font-semibold">Skills Workshop Series</h4>
                                    <x-mary-badge value="Requires Revision" class="badge-warning" />
                                    <span class="text-sm text-gray-500">#TKT-003</span>
                                </div>
                                <p class="text-gray-600 mb-3">Three-day workshop series focusing on leadership,
                                    communication, and project management skills for students.</p>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="flex items-center space-x-2">
                                        <x-mary-icon name="s-calendar" class="w-4 h-4 text-gray-400" />
                                        <span class="text-sm">Oct 20-22, 2025 • 9:00 AM</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <x-mary-icon name="s-map-pin" class="w-4 h-4 text-gray-400" />
                                        <span class="text-sm">Building A, Room 301</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <x-mary-icon name="s-users" class="w-4 h-4 text-gray-400" />
                                        <span class="text-sm">40 attendees expected</span>
                                    </div>
                                </div>

                                {{-- Progress Steps - Requires Action --}}
                                <div class="mb-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center space-x-2">
                                            <div
                                                class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                                                <x-mary-icon name="s-check" class="w-4 h-4 text-white" />
                                            </div>
                                            <span class="text-sm font-medium">Submitted</span>
                                        </div>
                                        <div class="flex-1 h-0.5 bg-warning"></div>
                                        <div class="flex items-center space-x-2">
                                            <div
                                                class="w-8 h-8 bg-warning rounded-full flex items-center justify-center">
                                                <x-mary-icon name="s-exclamation-triangle"
                                                    class="w-4 h-4 text-white" />
                                            </div>
                                            <span class="text-sm font-medium">Needs Revision</span>
                                        </div>
                                        <div class="flex-1 h-0.5 bg-gray-200"></div>
                                        <div class="flex items-center space-x-2">
                                            <div
                                                class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                <x-mary-icon name="s-clock" class="w-4 h-4 text-gray-400" />
                                            </div>
                                            <span class="text-sm text-gray-400">GSO Review</span>
                                        </div>
                                        <div class="flex-1 h-0.5 bg-gray-200"></div>
                                        <div class="flex items-center space-x-2">
                                            <div
                                                class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                <x-mary-icon name="s-check-circle" class="w-4 h-4 text-gray-400" />
                                            </div>
                                            <span class="text-sm text-gray-400">Approved</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col space-y-2">
                                <x-mary-button icon="s-eye" class="btn-sm btn-ghost" tooltip="View Details" />
                                <x-mary-button icon="s-pencil" class="btn-sm btn-primary" tooltip="Revise" />
                                <x-mary-button icon="s-chat-bubble-left-right" class="btn-sm btn-ghost"
                                    tooltip="Comments" />
                            </div>
                        </div>

                        {{-- Revision Request --}}
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="bg-orange-50 p-3 rounded-lg">
                                <div class="flex items-start space-x-3">
                                    <x-mary-icon name="s-exclamation-triangle"
                                        class="w-5 h-5 text-orange-500 mt-0.5" />
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-orange-700">Revision Required</p>
                                        <p class="text-sm text-orange-600 mt-1">Please provide more details about the
                                            workshop facilitators, their qualifications, and a detailed schedule for
                                            each day. Also, include the registration process for participants and
                                            maximum capacity per session.</p>
                                        <p class="text-xs text-orange-500 mt-2">3 days ago</p>
                                        <x-mary-button label="Submit Revision" icon="s-arrow-up"
                                            class="btn-sm btn-primary mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-sm text-gray-500">
                            Submitted on September 27, 2025 • Revision requested October 2, 2025
                        </div>
                    </div>
                </div>

                {{-- Pagination --}}
                <div class="mt-6 flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        Showing 3 of 12 tickets
                    </div>
                    <div class="flex space-x-2">
                        <x-mary-button icon="s-chevron-left" class="btn-sm btn-ghost" disabled />
                        <x-mary-button label="1" class="btn-sm btn-primary" />
                        <x-mary-button label="2" class="btn-sm btn-ghost" />
                        <x-mary-button label="3" class="btn-sm btn-ghost" />
                        <x-mary-button icon="s-chevron-right" class="btn-sm btn-ghost" />
                    </div>
                </div>
            </x-mary-card>

            {{-- Quick Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-mary-stat title="Total Submitted" value="12" icon="s-document-text" color="text-primary" />

                <x-mary-stat title="Under Review" value="4" icon="s-clock" color="text-warning" />

                <x-mary-stat title="Approved" value="7" icon="s-check-circle" color="text-success" />

                <x-mary-stat title="Need Action" value="1" icon="s-exclamation-triangle" color="text-error" />
            </div>
        </div>
    </div>

</div>
