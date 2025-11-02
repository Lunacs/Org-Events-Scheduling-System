<div>
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- Header Section --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="text-2xl font-bold text-base-content">Notifications Center</h3>
                    <p class="text-sm text-base-content/60 mt-1">Stay updated on ticket submissions, approvals, and
                        system
                        updates</p>
                </div>
                <div class="flex gap-2">
                    <x-mary-button label="Mark All as Read" icon="s-check" class="btn-ghost btn-sm"
                        wire:click="markAllAsRead" />
                    <x-mary-button label="Settings" icon="s-cog-6-tooth" class="btn-ghost btn-sm"
                        wire:click="openNotificationSettings" />
                </div>
            </div>

            {{-- Notifications Component --}}
            <x-notifications.list
                :notifications="$notifications"
                :unread-count="$unreadCount"
                :today-count="$todayCount"
                :week-count="$weekCount"
                :total-count="$totalCount"
                :search="$search"
                :type-filter="$typeFilter"
                :status-filter="$statusFilter"
                :type-options="[
                    ['id' => '', 'name' => 'All Types'],
                    ['id' => 'ticket_submitted', 'name' => 'Ticket Submissions'],
                    ['id' => 'approval_request', 'name' => 'Approval Requests'],
                    ['id' => 'conflict', 'name' => 'Schedule Conflicts'],
                    ['id' => 'reminder', 'name' => 'Reminders'],
                    ['id' => 'announcement', 'name' => 'Announcements'],
                    ['id' => 'system', 'name' => 'System Updates'],
                ]"
                :show-ticket-number="true" />

            {{-- Notification Settings --}}
            <x-mary-card title="Notification Preferences" subtitle="Customize how you receive notifications">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold mb-3 text-base-content">Email Notifications</h4>
                        <div class="space-y-2">
                            <x-mary-checkbox label="New ticket submissions" checked />
                            <x-mary-checkbox label="Schedule conflicts" checked />
                            <x-mary-checkbox label="Approval requests from GSO" checked />
                            <x-mary-checkbox label="Event completion notices" checked />
                            <x-mary-checkbox label="System announcements" />
                            <x-mary-checkbox label="Weekly summary reports" />
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold mb-3 text-base-content">Reminder Settings</h4>
                        <div class="space-y-3">
                            <x-mary-select label="Review Reminders" :options="[
                                ['id' => '24', 'name' => 'After 24 hours'],
                                ['id' => '48', 'name' => 'After 48 hours'],
                                ['id' => '72', 'name' => 'After 72 hours'],
                                ['id' => 'none', 'name' => 'No reminders'],
                            ]" value="48" />

                            <x-mary-select label="Daily Digest" :options="[
                                ['id' => '8', 'name' => '8:00 AM'],
                                ['id' => '9', 'name' => '9:00 AM'],
                                ['id' => '10', 'name' => '10:00 AM'],
                                ['id' => 'none', 'name' => 'No daily digest'],
                            ]" value="9" />
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
