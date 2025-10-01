<x-student-org-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Event Calendar') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Calendar Header --}}
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">University Event Calendar</h3>
                    <p class="text-sm text-gray-600">View all approved events to avoid scheduling conflicts</p>
                </div>
                <div class="flex space-x-3">
                    <x-mary-button label="Submit New Ticket" icon="s-document-plus" class="btn-primary"
                        link="/student-org/submit-ticket" wire:navigate />
                    <x-mary-button label="My Tickets" icon="s-ticket" class="btn-secondary"
                        link="/student-org/my-tickets" wire:navigate />
                </div>
            </div>

            {{-- Info Banner --}}
            <x-mary-card>
                <div class="bg-info/10 p-4 rounded-lg border-l-4 border-info">
                    <div class="flex items-start space-x-3">
                        <x-mary-icon name="s-information-circle" class="w-5 h-5 text-info mt-0.5" />
                        <div class="text-sm">
                            <p class="font-medium mb-1 text-info-content">Calendar Information:</p>
                            <ul class="list-disc list-inside space-y-1 text-gray-600">
                                <li>This calendar shows only approved events from all student organizations</li>
                                <li>Use this to check for potential conflicts before submitting your event request</li>
                                <li>Events are color-coded by category (Academic, Cultural, Sports, etc.)</li>
                                <li>Click on any event to view more details</li>
                                <li>Your organization's events are highlighted with a special border</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </x-mary-card>

            {{-- Calendar Navigation and Filters --}}
            <x-mary-card>
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <x-mary-button icon="s-chevron-left" class="btn-ghost btn-sm" wire:click="previousMonth" />
                        <h3 class="text-xl font-semibold min-w-48 text-center">October 2025</h3>
                        <x-mary-button icon="s-chevron-right" class="btn-ghost btn-sm" wire:click="nextMonth" />
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-mary-select label="Event Type" wire:model.live="eventTypeFilter" :options="[
                            ['id' => '', 'name' => 'All Types'],
                            ['id' => 'academic', 'name' => 'Academic'],
                            ['id' => 'cultural', 'name' => 'Cultural'],
                            ['id' => 'sports', 'name' => 'Sports'],
                            ['id' => 'meeting', 'name' => 'Meeting'],
                            ['id' => 'workshop', 'name' => 'Workshop'],
                        ]"
                            class="w-40" />

                        <x-mary-select label="Venue" wire:model.live="venueFilter" :options="[
                            ['id' => '', 'name' => 'All Venues'],
                            ['id' => 'auditorium', 'name' => 'University Auditorium'],
                            ['id' => 'student_center', 'name' => 'Student Center'],
                            ['id' => 'gymnasium', 'name' => 'Gymnasium'],
                            ['id' => 'library', 'name' => 'Library Hall'],
                        ]" class="w-40" />

                        <x-mary-button icon="s-arrow-path" class="btn-ghost btn-sm" wire:click="resetFilters"
                            tooltip="Reset Filters" />
                    </div>
                </div>
            </x-mary-card>

            {{-- Calendar Grid --}}
            <x-mary-card>
                <div class="calendar-container">
                    {{-- Calendar Header Days --}}
                    <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-t-lg overflow-hidden">
                        <div class="bg-gray-100 p-3 text-center font-semibold text-sm">Sunday</div>
                        <div class="bg-gray-100 p-3 text-center font-semibold text-sm">Monday</div>
                        <div class="bg-gray-100 p-3 text-center font-semibold text-sm">Tuesday</div>
                        <div class="bg-gray-100 p-3 text-center font-semibold text-sm">Wednesday</div>
                        <div class="bg-gray-100 p-3 text-center font-semibold text-sm">Thursday</div>
                        <div class="bg-gray-100 p-3 text-center font-semibold text-sm">Friday</div>
                        <div class="bg-gray-100 p-3 text-center font-semibold text-sm">Saturday</div>
                    </div>

                    {{-- Calendar Body --}}
                    <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-b-lg overflow-hidden">
                        {{-- Week 1 --}}
                        <div class="bg-white p-2 h-32 text-gray-400">
                            <div class="text-sm">29</div>
                        </div>
                        <div class="bg-white p-2 h-32 text-gray-400">
                            <div class="text-sm">30</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">1</div>
                            <div class="mt-1 space-y-1">
                                <div class="bg-blue-100 text-blue-800 text-xs px-1 py-0.5 rounded truncate cursor-pointer hover:bg-blue-200"
                                    onclick="showEventDetails('evt-001')">
                                    Fundraising Concert
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">2</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">3</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">4</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">5</div>
                        </div>

                        {{-- Week 2 --}}
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">6</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">7</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">8</div>
                            <div class="mt-1 space-y-1">
                                <div class="bg-green-100 text-green-800 text-xs px-1 py-0.5 rounded truncate cursor-pointer hover:bg-green-200"
                                    onclick="showEventDetails('evt-002')">
                                    Sports Festival
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">9</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">10</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">11</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">12</div>
                        </div>

                        {{-- Week 3 --}}
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">13</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">14</div>
                        </div>
                        <div class="bg-white p-2 h-32 border-2 border-primary border-dashed">
                            <div class="text-sm font-medium">15</div>
                            <div class="mt-1 space-y-1">
                                <div class="bg-primary-100 text-primary-800 text-xs px-1 py-0.5 rounded truncate cursor-pointer hover:bg-primary-200 border border-primary-300"
                                    onclick="showEventDetails('evt-003')">
                                    Your Org Meeting
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">16</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">17</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">18</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">19</div>
                        </div>

                        {{-- Week 4 --}}
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">20</div>
                            <div class="mt-1 space-y-1">
                                <div class="bg-purple-100 text-purple-800 text-xs px-1 py-0.5 rounded truncate cursor-pointer hover:bg-purple-200"
                                    onclick="showEventDetails('evt-004')">
                                    Workshop Series
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">21</div>
                            <div class="mt-1 space-y-1">
                                <div class="bg-purple-100 text-purple-800 text-xs px-1 py-0.5 rounded truncate cursor-pointer hover:bg-purple-200"
                                    onclick="showEventDetails('evt-004')">
                                    Workshop Day 2
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">22</div>
                            <div class="mt-1 space-y-1">
                                <div class="bg-purple-100 text-purple-800 text-xs px-1 py-0.5 rounded truncate cursor-pointer hover:bg-purple-200"
                                    onclick="showEventDetails('evt-004')">
                                    Workshop Day 3
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">23</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">24</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">25</div>
                            <div class="mt-1 space-y-1">
                                <div class="bg-yellow-100 text-yellow-800 text-xs px-1 py-0.5 rounded truncate cursor-pointer hover:bg-yellow-200"
                                    onclick="showEventDetails('evt-005')">
                                    Cultural Night
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">26</div>
                        </div>

                        {{-- Week 5 --}}
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">27</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">28</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">29</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">30</div>
                        </div>
                        <div class="bg-white p-2 h-32">
                            <div class="text-sm font-medium">31</div>
                        </div>
                        <div class="bg-white p-2 h-32 text-gray-400">
                            <div class="text-sm">1</div>
                        </div>
                        <div class="bg-white p-2 h-32 text-gray-400">
                            <div class="text-sm">2</div>
                        </div>
                    </div>
                </div>
            </x-mary-card>

            {{-- Legend --}}
            <x-mary-card title="Legend" subtitle="Event categories and colors">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-blue-100 border border-blue-300 rounded"></div>
                        <span class="text-sm">Cultural Events</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-green-100 border border-green-300 rounded"></div>
                        <span class="text-sm">Sports Events</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-purple-100 border border-purple-300 rounded"></div>
                        <span class="text-sm">Academic/Workshop</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded"></div>
                        <span class="text-sm">Social Events</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-primary-100 border-2 border-primary-300 border-dashed rounded"></div>
                        <span class="text-sm font-medium">Your Organization</span>
                    </div>
                </div>
            </x-mary-card>

            {{-- Upcoming Events List --}}
            <x-mary-card title="Upcoming Events This Month" subtitle="Detailed list of scheduled events">
                <div class="space-y-4">
                    <div class="flex items-start space-x-4 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="s-musical-note" class="w-6 h-6 text-blue-600" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-blue-900">Fundraising Concert</h4>
                            <p class="text-sm text-blue-700 mt-1">Music Club Annual Concert for Scholarship Fund</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-blue-600">
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-calendar" class="w-4 h-4" />
                                    <span>Oct 1, 2025 • 6:00 PM - 10:00 PM</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-map-pin" class="w-4 h-4" />
                                    <span>University Auditorium</span>
                                </span>
                            </div>
                            <x-mary-badge value="Cultural" class="badge-info mt-2" />
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 p-4 bg-green-50 rounded-lg border-l-4 border-green-400">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="s-trophy" class="w-6 h-6 text-green-600" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-green-900">Sports Festival</h4>
                            <p class="text-sm text-green-700 mt-1">Inter-Organization Sports Competition</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-green-600">
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-calendar" class="w-4 h-4" />
                                    <span>Oct 8, 2025 • 8:00 AM - 5:00 PM</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-map-pin" class="w-4 h-4" />
                                    <span>University Gymnasium</span>
                                </span>
                            </div>
                            <x-mary-badge value="Sports" class="badge-success mt-2" />
                        </div>
                    </div>

                    <div
                        class="flex items-start space-x-4 p-4 bg-primary-50 rounded-lg border-l-4 border-primary-400 border-dashed">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="s-building-office" class="w-6 h-6 text-primary-600" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-primary-900">Your Organization Meeting</h4>
                            <p class="text-sm text-primary-700 mt-1">Annual General Assembly • Status: Under Review</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-primary-600">
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-calendar" class="w-4 h-4" />
                                    <span>Oct 15, 2025 • 2:00 PM - 5:00 PM</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-map-pin" class="w-4 h-4" />
                                    <span>Student Center Room 201</span>
                                </span>
                            </div>
                            <div class="flex items-center space-x-2 mt-2">
                                <x-mary-badge value="Meeting" class="badge-primary" />
                                <x-mary-badge value="Pending Approval" class="badge-warning" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 p-4 bg-purple-50 rounded-lg border-l-4 border-purple-400">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="s-academic-cap" class="w-6 h-6 text-purple-600" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-purple-900">Leadership Workshop Series</h4>
                            <p class="text-sm text-purple-700 mt-1">3-Day Skills Development Workshop</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-purple-600">
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-calendar" class="w-4 h-4" />
                                    <span>Oct 20-22, 2025 • 9:00 AM - 4:00 PM</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-map-pin" class="w-4 h-4" />
                                    <span>Building A, Room 301</span>
                                </span>
                            </div>
                            <x-mary-badge value="Workshop" class="badge-secondary mt-2" />
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-400">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="s-sparkles" class="w-6 h-6 text-yellow-600" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-yellow-900">Cultural Night</h4>
                            <p class="text-sm text-yellow-700 mt-1">Diversity and Culture Celebration</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-yellow-600">
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-calendar" class="w-4 h-4" />
                                    <span>Oct 25, 2025 • 7:00 PM - 11:00 PM</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-map-pin" class="w-4 h-4" />
                                    <span>Student Center Plaza</span>
                                </span>
                            </div>
                            <x-mary-badge value="Cultural" class="badge-warning mt-2" />
                        </div>
                    </div>
                </div>
            </x-mary-card>
        </div>
    </div>

    <script>
        function showEventDetails(eventId) {
            // This would typically open a modal or show event details
            console.log('Show event details for:', eventId);
            // You can implement a modal or redirect to event details page
        }
    </script>
</x-student-org-layout>
