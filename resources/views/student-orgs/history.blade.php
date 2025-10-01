<x-student-org-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Event History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Header Section --}}
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">Event History & Analytics</h3>
                    <p class="text-sm text-gray-600">View your organization's complete event history and performance metrics</p>
                </div>
                <div class="flex space-x-3">
                    <x-mary-button
                        label="Export Report"
                        icon="s-document-arrow-down"
                        class="btn-secondary btn-sm"
                        wire:click="exportReport" />
                    <x-mary-button
                        label="Submit New Event"
                        icon="s-document-plus"
                        class="btn-primary btn-sm"
                        link="/student-org/submit-ticket"
                        wire:navigate />
                </div>
            </div>

            {{-- Analytics Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-mary-stat
                    title="Total Events"
                    description="All time"
                    value="24"
                    icon="s-calendar-days"
                    color="text-primary" />

                <x-mary-stat
                    title="Approved"
                    description="Success rate: 83%"
                    value="20"
                    icon="s-check-circle"
                    color="text-success" />

                <x-mary-stat
                    title="Rejected"
                    description="17% of submissions"
                    value="4"
                    icon="s-x-circle"
                    color="text-error" />

                <x-mary-stat
                    title="Avg. Processing"
                    description="Days to approval"
                    value="5.2"
                    icon="s-clock"
                    color="text-info" />
            </div>

            {{-- Performance Analytics --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-mary-card title="Event Types Distribution" subtitle="Your most common event categories">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 bg-blue-500 rounded"></div>
                                <span class="text-sm font-medium">Academic/Workshop</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: 40%"></div>
                                </div>
                                <span class="text-sm text-gray-600">40%</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 bg-green-500 rounded"></div>
                                <span class="text-sm font-medium">Cultural Events</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: 30%"></div>
                                </div>
                                <span class="text-sm text-gray-600">30%</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                                <span class="text-sm font-medium">Fundraising</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full" style="width: 20%"></div>
                                </div>
                                <span class="text-sm text-gray-600">20%</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 bg-purple-500 rounded"></div>
                                <span class="text-sm font-medium">Meetings</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-500 h-2 rounded-full" style="width: 10%"></div>
                                </div>
                                <span class="text-sm text-gray-600">10%</span>
                            </div>
                        </div>
                    </div>
                </x-mary-card>

                <x-mary-card title="Monthly Activity" subtitle="Events submitted per month (Last 6 months)">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">October 2025</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full" style="width: 100%"></div>
                                </div>
                                <span class="text-sm text-gray-600">5 events</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">September 2025</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full" style="width: 80%"></div>
                                </div>
                                <span class="text-sm text-gray-600">4 events</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">August 2025</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full" style="width: 60%"></div>
                                </div>
                                <span class="text-sm text-gray-600">3 events</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">July 2025</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full" style="width: 40%"></div>
                                </div>
                                <span class="text-sm text-gray-600">2 events</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">June 2025</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full" style="width: 60%"></div>
                                </div>
                                <span class="text-sm text-gray-600">3 events</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">May 2025</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full" style="width: 80%"></div>
                                </div>
                                <span class="text-sm text-gray-600">4 events</span>
                            </div>
                        </div>
                    </div>
                </x-mary-card>
            </div>

            {{-- Filter and Search --}}
            <x-mary-card>
                <div class="flex flex-wrap gap-4 items-end">
                    <x-mary-input
                        label="Search Events"
                        wire:model.live="search"
                        placeholder="Search by title, description, or venue..."
                        icon="s-magnifying-glass"
                        class="flex-1 min-w-64" />

                    <x-mary-select
                        label="Status"
                        wire:model.live="statusFilter"
                        :options="[
                            ['id' => '', 'name' => 'All Status'],
                            ['id' => 'approved', 'name' => 'Approved'],
                            ['id' => 'rejected', 'name' => 'Rejected'],
                            ['id' => 'cancelled', 'name' => 'Cancelled']
                        ]"
                        class="w-32" />

                    <x-mary-select
                        label="Event Type"
                        wire:model.live="typeFilter"
                        :options="[
                            ['id' => '', 'name' => 'All Types'],
                            ['id' => 'academic', 'name' => 'Academic'],
                            ['id' => 'cultural', 'name' => 'Cultural'],
                            ['id' => 'fundraising', 'name' => 'Fundraising'],
                            ['id' => 'meeting', 'name' => 'Meeting'],
                            ['id' => 'workshop', 'name' => 'Workshop']
                        ]"
                        class="w-32" />

                    <x-mary-select
                        label="Year"
                        wire:model.live="yearFilter"
                        :options="[
                            ['id' => '', 'name' => 'All Years'],
                            ['id' => '2025', 'name' => '2025'],
                            ['id' => '2024', 'name' => '2024'],
                            ['id' => '2023', 'name' => '2023']
                        ]"
                        class="w-24" />

                    <x-mary-button
                        icon="s-arrow-path"
                        class="btn-ghost btn-sm"
                        wire:click="resetFilters"
                        tooltip="Reset Filters" />
                </div>
            </x-mary-card>

            {{-- Event History List --}}
            <x-mary-card title="Event History" subtitle="Complete record of your organization's events">
                <div class="space-y-6">
                    {{-- Event Item 1 - Approved --}}
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-lg font-semibold">Summer Leadership Workshop</h4>
                                    <x-mary-badge value="Approved" class="badge-success" />
                                    <x-mary-badge value="Completed" class="badge-info" />
                                </div>
                                <p class="text-gray-600 mb-3">Three-day intensive workshop on leadership skills and organizational management for student officers.</p>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Event Date</p>
                                        <p class="text-sm font-medium">Aug 15-17, 2025</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Venue</p>
                                        <p class="text-sm font-medium">Conference Room A</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Attendance</p>
                                        <p class="text-sm font-medium">45 / 50 attendees</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Budget</p>
                                        <p class="text-sm font-medium">₱15,000</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-500">Submitted:</p>
                                        <p class="font-medium">July 20, 2025</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Approved:</p>
                                        <p class="font-medium">July 25, 2025</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Processing Time:</p>
                                        <p class="font-medium">5 days</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col space-y-2 ml-6">
                                <x-mary-button icon="s-eye" class="btn-sm btn-ghost" tooltip="View Details" />
                                <x-mary-button icon="s-document-arrow-down" class="btn-sm btn-ghost" tooltip="Download Report" />
                                <x-mary-button icon="s-photograph" class="btn-sm btn-ghost" tooltip="View Photos" />
                            </div>
                        </div>

                        {{-- Event Feedback/Results --}}
                        <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-400">
                            <div class="flex items-start space-x-3">
                                <x-mary-icon name="s-chart-bar" class="w-5 h-5 text-green-600 mt-0.5" />
                                <div class="flex-1">
                                    <h5 class="font-medium text-green-900">Event Results & Feedback</h5>
                                    <p class="text-sm text-green-700 mt-1">Excellent turnout with 90% attendance rate. Participants rated the workshop 4.8/5. Generated ₱2,000 in additional membership fees.</p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-green-600">
                                        <span>✓ Post-event report submitted</span>
                                        <span>✓ Financial report completed</span>
                                        <span>✓ Venue restored</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Event Item 2 - Rejected --}}
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-lg font-semibold">Outdoor Music Festival</h4>
                                    <x-mary-badge value="Rejected" class="badge-error" />
                                </div>
                                <p class="text-gray-600 mb-3">Large-scale outdoor music festival featuring local bands and food vendors to raise funds for community projects.</p>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Proposed Date</p>
                                        <p class="text-sm font-medium">July 10, 2025</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Venue</p>
                                        <p class="text-sm font-medium">University Plaza</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Expected</p>
                                        <p class="text-sm font-medium">500 attendees</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Budget</p>
                                        <p class="text-sm font-medium">₱85,000</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-500">Submitted:</p>
                                        <p class="font-medium">June 15, 2025</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Rejected:</p>
                                        <p class="font-medium">June 22, 2025</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Processing Time:</p>
                                        <p class="font-medium">7 days</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col space-y-2 ml-6">
                                <x-mary-button icon="s-eye" class="btn-sm btn-ghost" tooltip="View Details" />
                                <x-mary-button icon="s-document-text" class="btn-sm btn-ghost" tooltip="View Feedback" />
                                <x-mary-button icon="s-arrow-path" class="btn-sm btn-ghost" tooltip="Resubmit Modified" />
                            </div>
                        </div>

                        {{-- Rejection Reasons --}}
                        <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-400">
                            <div class="flex items-start space-x-3">
                                <x-mary-icon name="s-x-circle" class="w-5 h-5 text-red-600 mt-0.5" />
                                <div class="flex-1">
                                    <h5 class="font-medium text-red-900">Rejection Reasons</h5>
                                    <div class="text-sm text-red-700 mt-1 space-y-1">
                                        <p>• Event scale exceeds organization's capacity and experience level</p>
                                        <p>• Insufficient security and safety planning for large outdoor gathering</p>
                                        <p>• Budget exceeds recommended limits for student organizations</p>
                                        <p>• Noise ordinance conflicts with nearby residential areas</p>
                                    </div>
                                    <p class="text-xs text-red-600 mt-2">Suggestion: Consider a smaller, indoor alternative or partner with experienced organizations</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Event Item 3 - Approved & Completed --}}
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-lg font-semibold">Annual General Assembly</h4>
                                    <x-mary-badge value="Approved" class="badge-success" />
                                    <x-mary-badge value="Completed" class="badge-info" />
                                </div>
                                <p class="text-gray-600 mb-3">Annual meeting for all organization members to review yearly accomplishments and elect new officers.</p>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Event Date</p>
                                        <p class="text-sm font-medium">May 20, 2025</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Venue</p>
                                        <p class="text-sm font-medium">Student Center Hall</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Attendance</p>
                                        <p class="text-sm font-medium">78 / 80 members</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Budget</p>
                                        <p class="text-sm font-medium">₱8,500</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-500">Submitted:</p>
                                        <p class="font-medium">April 25, 2025</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Approved:</p>
                                        <p class="font-medium">April 28, 2025</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Processing Time:</p>
                                        <p class="font-medium">3 days</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col space-y-2 ml-6">
                                <x-mary-button icon="s-eye" class="btn-sm btn-ghost" tooltip="View Details" />
                                <x-mary-button icon="s-document-arrow-down" class="btn-sm btn-ghost" tooltip="Download Minutes" />
                                <x-mary-button icon="s-users" class="btn-sm btn-ghost" tooltip="Attendance Record" />
                            </div>
                        </div>

                        {{-- Event Highlights --}}
                        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-400">
                            <div class="flex items-start space-x-3">
                                <x-mary-icon name="s-star" class="w-5 h-5 text-blue-600 mt-0.5" />
                                <div class="flex-1">
                                    <h5 class="font-medium text-blue-900">Event Highlights</h5>
                                    <p class="text-sm text-blue-700 mt-1">Record high attendance (97.5%). Successfully elected new officers. Approved budget increase for next year. Received commendation from university administration.</p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-blue-600">
                                        <span>97.5% attendance rate</span>
                                        <span>5 new committee chairs elected</span>
                                        <span>₱50k budget approved</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Event Item 4 - Cancelled --}}
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow opacity-75">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-lg font-semibold">Career Fair</h4>
                                    <x-mary-badge value="Approved" class="badge-success" />
                                    <x-mary-badge value="Cancelled" class="badge-warning" />
                                </div>
                                <p class="text-gray-600 mb-3">Career fair connecting students with potential employers and internship opportunities.</p>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Planned Date</p>
                                        <p class="text-sm font-medium">March 15, 2025</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Venue</p>
                                        <p class="text-sm font-medium">Gymnasium</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Expected</p>
                                        <p class="text-sm font-medium">200 students</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Budget</p>
                                        <p class="text-sm font-medium">₱25,000</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-500">Submitted:</p>
                                        <p class="font-medium">Feb 10, 2025</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Approved:</p>
                                        <p class="font-medium">Feb 15, 2025</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Cancelled:</p>
                                        <p class="font-medium">March 12, 2025</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col space-y-2 ml-6">
                                <x-mary-button icon="s-eye" class="btn-sm btn-ghost" tooltip="View Details" />
                                <x-mary-button icon="s-document-text" class="btn-sm btn-ghost" tooltip="Cancellation Report" />
                            </div>
                        </div>

                        {{-- Cancellation Reason --}}
                        <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-400">
                            <div class="flex items-start space-x-3">
                                <x-mary-icon name="s-exclamation-triangle" class="w-5 h-5 text-yellow-600 mt-0.5" />
                                <div class="flex-1">
                                    <h5 class="font-medium text-yellow-900">Event Cancelled</h5>
                                    <p class="text-sm text-yellow-700 mt-1">Event cancelled due to insufficient employer participation (only 3 out of 15 expected companies confirmed). Refunds processed for all advance payments.</p>
                                    <p class="text-xs text-yellow-600 mt-2">Learning: Start employer recruitment earlier and have contingency plans</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pagination --}}
                <div class="mt-6 flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        Showing 4 of 24 events
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

            {{-- Insights and Recommendations --}}
            <x-mary-card title="Insights & Recommendations" subtitle="Data-driven suggestions for future events">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h4 class="font-semibold text-green-600">✓ What's Working Well</h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-start space-x-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <p>Academic workshops have 95% approval rate and high attendance</p>
                            </div>
                            <div class="flex items-start space-x-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <p>Events submitted 2+ weeks in advance process 40% faster</p>
                            </div>
                            <div class="flex items-start space-x-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <p>Venue bookings in Student Center have lowest rejection rate</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="font-semibold text-orange-600">⚠ Areas for Improvement</h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-start space-x-2">
                                <div class="w-2 h-2 bg-orange-500 rounded-full mt-2"></div>
                                <p>Large outdoor events (>200 people) have 60% rejection rate</p>
                            </div>
                            <div class="flex items-start space-x-2">
                                <div class="w-2 h-2 bg-orange-500 rounded-full mt-2"></div>
                                <p>Budget planning needs improvement - 30% over initial estimates</p>
                            </div>
                            <div class="flex items-start space-x-2">
                                <div class="w-2 h-2 bg-orange-500 rounded-full mt-2"></div>
                                <p>Post-event reports submitted late in 25% of cases</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t">
                    <h4 class="font-semibold text-primary mb-3">💡 Recommendations</h4>
                    <div class="bg-primary/5 p-4 rounded-lg">
                        <ul class="text-sm space-y-2">
                            <li>• Focus on academic and workshop-style events for higher success rates</li>
                            <li>• Submit event requests 3+ weeks in advance for optimal processing</li>
                            <li>• Consider partnering with other organizations for large-scale events</li>
                            <li>• Set up automated reminders for post-event report deadlines</li>
                            <li>• Create template budgets based on successful past events</li>
                        </ul>
                    </div>
                </div>
            </x-mary-card>
        </div>
    </div>
</x-student-org-layout>
