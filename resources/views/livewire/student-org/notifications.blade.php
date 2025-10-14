<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Header Section --}}
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">Notifications Center</h3>
                    <p class="text-sm text-gray-600">Stay updated on your event requests and university announcements</p>
                </div>
                <div class="flex space-x-3">
                    <x-mary-button label="Mark All as Read" icon="s-check" class="btn-ghost btn-sm"
                        wire:click="markAllAsRead" />
                    <x-mary-button label="Settings" icon="s-cog-6-tooth" class="btn-ghost btn-sm"
                        wire:click="openNotificationSettings" />
                </div>
            </div>

            {{-- Notification Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-mary-stat title="Unread" value="5" icon="s-bell" color="text-primary" />

                <x-mary-stat title="Today" value="3" icon="s-clock" color="text-info" />

                <x-mary-stat title="This Week" value="12" icon="s-calendar-days" color="text-success" />

                <x-mary-stat title="Total" value="47" icon="s-archive-box" color="text-secondary" />
            </div>

            {{-- Filter and Search --}}
            <x-mary-card>
                <div class="flex flex-wrap gap-4 items-end">
                    <x-mary-input label="Search Notifications" wire:model.live="search"
                        placeholder="Search by content, event, or type..." icon="s-magnifying-glass"
                        class="flex-1 min-w-64" />

                    <x-mary-select label="Type" wire:model.live="typeFilter" :options="[
                        ['id' => '', 'name' => 'All Types'],
                        ['id' => 'approval', 'name' => 'Approvals'],
                        ['id' => 'rejection', 'name' => 'Rejections'],
                        ['id' => 'revision', 'name' => 'Revision Required'],
                        ['id' => 'reminder', 'name' => 'Reminders'],
                        ['id' => 'announcement', 'name' => 'Announcements'],
                        ['id' => 'reschedule', 'name' => 'Reschedule Updates'],
                    ]" class="w-40" />

                    <x-mary-select label="Status" wire:model.live="statusFilter" :options="[
                        ['id' => '', 'name' => 'All Status'],
                        ['id' => 'unread', 'name' => 'Unread'],
                        ['id' => 'read', 'name' => 'Read'],
                        ['id' => 'archived', 'name' => 'Archived'],
                    ]" class="w-32" />

                    <x-mary-button icon="s-funnel" class="btn-ghost btn-sm" wire:click="clearFilters"
                        tooltip="Clear Filters" />
                </div>
            </x-mary-card>

            {{-- Notifications List --}}
            <x-mary-card>
                <div class="space-y-4">
                    {{-- Notification Item 1 - Approval --}}
                    <div
                        class="flex items-start space-x-4 p-4 bg-green-50 rounded-lg border-l-4 border-green-400 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <x-mary-icon name="s-check-circle" class="w-5 h-5 text-green-600" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-green-900">Event Approved: Fundraising Concert</p>
                                    <p class="text-sm text-green-700 mt-1">Your event request for "Fundraising Concert"
                                        scheduled on November 1, 2025, has been approved by both OSA and GSO. You can
                                        now proceed with your event preparations.</p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-green-600">
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-building-office" class="w-3 h-3" />
                                            <span>From: OSA Office</span>
                                        </span>
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-clock" class="w-3 h-3" />
                                            <span>2 hours ago</span>
                                        </span>
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-ticket" class="w-3 h-3" />
                                            <span>TKT-002</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col space-y-2 ml-4">
                                    <div class="w-3 h-3 bg-green-500 rounded-full" title="Unread"></div>
                                    <x-mary-dropdown>
                                        <x-slot:trigger>
                                            <x-mary-button icon="s-ellipsis-vertical" class="btn-ghost btn-xs" />
                                        </x-slot:trigger>
                                        <x-mary-menu-item title="Mark as Read" icon="s-check" />
                                        <x-mary-menu-item title="View Event" icon="s-eye" />
                                        <x-mary-menu-item title="Archive" icon="s-archive-box" />
                                    </x-mary-dropdown>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notification Item 2 - Revision Required --}}
                    <div
                        class="flex items-start space-x-4 p-4 bg-orange-50 rounded-lg border-l-4 border-orange-400 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                <x-mary-icon name="s-exclamation-triangle" class="w-5 h-5 text-orange-600" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-orange-900">Revision Required: Skills Workshop Series
                                    </p>
                                    <p class="text-sm text-orange-700 mt-1">Your event request needs additional
                                        information. Please provide more details about the workshop facilitators, their
                                        qualifications, and a detailed schedule for each day.</p>
                                    <div class="mt-3 p-2 bg-orange-100 rounded">
                                        <p class="text-xs text-orange-800 font-medium">Action Required:</p>
                                        <p class="text-xs text-orange-700">Please update your ticket with the requested
                                            information within 5 days.</p>
                                    </div>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-orange-600">
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-building-office" class="w-3 h-3" />
                                            <span>From: GSO Office</span>
                                        </span>
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-clock" class="w-3 h-3" />
                                            <span>1 day ago</span>
                                        </span>
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-ticket" class="w-3 h-3" />
                                            <span>TKT-003</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col space-y-2 ml-4">
                                    <div class="w-3 h-3 bg-orange-500 rounded-full" title="Unread"></div>
                                    <x-mary-dropdown>
                                        <x-slot:trigger>
                                            <x-mary-button icon="s-ellipsis-vertical" class="btn-ghost btn-xs" />
                                        </x-slot:trigger>
                                        <x-mary-menu-item title="Mark as Read" icon="s-check" />
                                        <x-mary-menu-item title="Edit Ticket" icon="s-pencil" />
                                        <x-mary-menu-item title="Archive" icon="s-archive-box" />
                                    </x-mary-dropdown>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notification Item 3 - Under Review --}}
                    <div
                        class="flex items-start space-x-4 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-400 hover:shadow-md transition-shadow opacity-75">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <x-mary-icon name="s-clock" class="w-5 h-5 text-blue-600" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-blue-900">Under Review: Annual Organization Meeting
                                    </p>
                                    <p class="text-sm text-blue-700 mt-1">Your event proposal looks good. Please
                                        provide the list of expected attendees with their contact information for
                                        security purposes.</p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-blue-600">
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-building-office" class="w-3 h-3" />
                                            <span>From: OSA Office</span>
                                        </span>
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-clock" class="w-3 h-3" />
                                            <span>2 days ago</span>
                                        </span>
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-ticket" class="w-3 h-3" />
                                            <span>TKT-001</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col space-y-2 ml-4">
                                    <div class="w-3 h-3 bg-gray-300 rounded-full" title="Read"></div>
                                    <x-mary-dropdown>
                                        <x-slot:trigger>
                                            <x-mary-button icon="s-ellipsis-vertical" class="btn-ghost btn-xs" />
                                        </x-slot:trigger>
                                        <x-mary-menu-item title="Mark as Unread" icon="s-envelope" />
                                        <x-mary-menu-item title="View Event" icon="s-eye" />
                                        <x-mary-menu-item title="Archive" icon="s-archive-box" />
                                    </x-mary-dropdown>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notification Item 4 - Reminder --}}
                    <div
                        class="flex items-start space-x-4 p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-400 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <x-mary-icon name="s-bell" class="w-5 h-5 text-yellow-600" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-yellow-900">Reminder: Event Approaching</p>
                                    <p class="text-sm text-yellow-700 mt-1">Your approved event "Fundraising Concert"
                                        is scheduled for November 1, 2025 (in 5 days). Don't forget to submit your final
                                        preparation checklist and post-event report.</p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-yellow-600">
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-computer-desktop" class="w-3 h-3" />
                                            <span>From: System</span>
                                        </span>
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-clock" class="w-3 h-3" />
                                            <span>3 days ago</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col space-y-2 ml-4">
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full" title="Unread"></div>
                                    <x-mary-dropdown>
                                        <x-slot:trigger>
                                            <x-mary-button icon="s-ellipsis-vertical" class="btn-ghost btn-xs" />
                                        </x-slot:trigger>
                                        <x-mary-menu-item title="Mark as Read" icon="s-check" />
                                        <x-mary-menu-item title="View Event" icon="s-eye" />
                                        <x-mary-menu-item title="Snooze" icon="s-clock" />
                                        <x-mary-menu-item title="Archive" icon="s-archive-box" />
                                    </x-mary-dropdown>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notification Item 5 - Reschedule Update --}}
                    <div
                        class="flex items-start space-x-4 p-4 bg-purple-50 rounded-lg border-l-4 border-purple-400 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <x-mary-icon name="s-arrow-path" class="w-5 h-5 text-purple-600" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-purple-900">Reschedule Request Approved</p>
                                    <p class="text-sm text-purple-700 mt-1">Your reschedule request for "Workshop
                                        Series" has been approved. The event venue has been changed from Room 301 to
                                        Library Hall due to technical issues.</p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-purple-600">
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-building-office" class="w-3 h-3" />
                                            <span>From: Facilities Office</span>
                                        </span>
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-clock" class="w-3 h-3" />
                                            <span>1 week ago</span>
                                        </span>
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-ticket" class="w-3 h-3" />
                                            <span>RSC-001</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col space-y-2 ml-4">
                                    <div class="w-3 h-3 bg-purple-500 rounded-full" title="Unread"></div>
                                    <x-mary-dropdown>
                                        <x-slot:trigger>
                                            <x-mary-button icon="s-ellipsis-vertical" class="btn-ghost btn-xs" />
                                        </x-slot:trigger>
                                        <x-mary-menu-item title="Mark as Read" icon="s-check" />
                                        <x-mary-menu-item title="View Details" icon="s-eye" />
                                        <x-mary-menu-item title="Archive" icon="s-archive-box" />
                                    </x-mary-dropdown>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notification Item 6 - General Announcement --}}
                    <div
                        class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg border-l-4 border-gray-400 hover:shadow-md transition-shadow opacity-75">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                <x-mary-icon name="s-megaphone" class="w-5 h-5 text-gray-600" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">Updated Event Guidelines</p>
                                    <p class="text-sm text-gray-700 mt-1">New guidelines for student organization
                                        events have been released. Please review the updated safety protocols and
                                        submission requirements before your next event request.</p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-600">
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-building-office" class="w-3 h-3" />
                                            <span>From: OSA Office</span>
                                        </span>
                                        <span class="flex items-center space-x-1">
                                            <x-mary-icon name="s-clock" class="w-3 h-3" />
                                            <span>1 week ago</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col space-y-2 ml-4">
                                    <div class="w-3 h-3 bg-gray-300 rounded-full" title="Read"></div>
                                    <x-mary-dropdown>
                                        <x-slot:trigger>
                                            <x-mary-button icon="s-ellipsis-vertical" class="btn-ghost btn-xs" />
                                        </x-slot:trigger>
                                        <x-mary-menu-item title="Mark as Unread" icon="s-envelope" />
                                        <x-mary-menu-item title="View Guidelines" icon="s-document-text" />
                                        <x-mary-menu-item title="Archive" icon="s-archive-box" />
                                    </x-mary-dropdown>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Load More --}}
                <div class="mt-6 text-center">
                    <x-mary-button label="Load More Notifications" icon="s-chevron-down" class="btn-ghost"
                        wire:click="loadMore" />
                </div>
            </x-mary-card>

            {{-- Notification Settings --}}
            <x-mary-card title="Notification Preferences" subtitle="Customize how you receive notifications">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold mb-3">Email Notifications</h4>
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
                        <h4 class="font-semibold mb-3">Reminder Settings</h4>
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
            </x-mary-card>
        </div>
    </div>
</div>
