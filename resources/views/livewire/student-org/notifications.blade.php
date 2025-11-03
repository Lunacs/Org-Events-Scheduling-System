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

            <x-notifications.list
                :notifications="$notifications"
                :unread-count="$unreadCount"
                :today-count="$todayCount"
                :week-count="$weekCount"
                :total-count="$totalCount"
                :search="$search"
                :type-filter="$typeFilter"
                :status-filter="$statusFilter"
                :type-options="$typeOptions"
                :show-ticket-number="true" />

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
