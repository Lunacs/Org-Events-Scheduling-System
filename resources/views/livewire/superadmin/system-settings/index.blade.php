<div x-data="{}" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white font-heading">
                System
                Settings</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage core system configurations, organizations,
                and
                workflows.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-mary-button icon="o-arrow-path" label="Refresh System Cache"
                class="btn-outline btn-sm dark:border-slate-600 dark:text-slate-300 w-full sm:w-auto"
                wire:click="refreshCache" spinner="refreshCache" />
        </div>
    </div>

    {{-- Tabs Interface --}}
    <x-mary-tabs wire:model="activeTab"
        class="bg-white dark:bg-base-200 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
        <x-mary-tab name="organizations" label="Organizations" icon="o-user-group">
            <div class="p-2 sm:p-4 w-full mx-auto" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                {{-- Student Organizations Manager Component --}}
                <livewire:superadmin.system-settings.organization-manager />
            </div>
        </x-mary-tab>

        <x-mary-tab name="courses" label="Courses" icon="o-academic-cap">
            <div class="p-2 sm:p-4 max-w-4xl mx-auto" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                {{-- Courses Manager Component --}}
                <livewire:superadmin.system-settings.course-manager defer.bundle />
            </div>
        </x-mary-tab>

        <x-mary-tab name="event-types" label="Event Types" icon="o-tag">
            <div class="p-2 sm:p-4 max-w-4xl mx-auto" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                {{-- Event Types Manager Component --}}
                <livewire:superadmin.system-settings.event-type-manager defer.bundle />
            </div>
        </x-mary-tab>

        <x-mary-tab name="venues" label="Venues" icon="o-map-pin">
            <div class="p-2 sm:p-4 max-w-4xl mx-auto" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                {{-- Venues Manager Component --}}
                <livewire:superadmin.system-settings.venue-manager defer.bundle />
            </div>
        </x-mary-tab>

        <x-mary-tab name="content" label="Content Sections" icon="o-document-text">
            <div class="p-2 sm:p-4 w-full mx-auto" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                {{-- Content Sections Manager Component --}}
                <livewire:superadmin.system-settings.content-section-manager defer.bundle />
            </div>
        </x-mary-tab>

        <x-mary-tab name="config" label="General Configuration" icon="o-cog-6-tooth">
            <div class="p-4 grid grid-cols-1 lg:grid-cols-2 gap-6" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <x-mary-card title="Office Settings" separator shadow class="dark:bg-base-200">
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Set default office and institutional
                        approval behavior.</p>
                    <form wire:submit="updateSettings">
                        <div class="space-y-4">
                            <x-mary-select wire:model="defaultOfficeId" :options="$offices
                                ->map(function ($office) {
                                    return ['id' => $office->office_id, 'name' => $office->office_name];
                                })
                                ->toArray()" option-value="id"
                                option-label="name" label="Default Office" placeholder="Select default office"
                                icon="o-building-office" />

                            <x-mary-toggle wire:model="crossOfficeApprovals" label="Enable cross-office approvals"
                                hint="Allows approvals from secondary offices" />

                            <div class="pt-2">
                                <x-mary-button type="submit" label="Save Changes" class="btn-primary w-full"
                                    spinner="updateSettings" />
                            </div>
                        </div>
                    </form>
                </x-mary-card>

                <x-mary-card title="Approval Workflows" separator shadow class="dark:bg-base-200">
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Current sequential approval process for
                        event requests.</p>
                    <div class="space-y-4">
                        <x-mary-timeline-item title="Student Organization" subtitle="Submit Event Request"
                            icon="o-paper-airplane" first />
                        <x-mary-timeline-item title="OSA Review" subtitle="Initial Review & Approval" icon="o-eye" />
                        <x-mary-timeline-item title="GSO Finalization" subtitle="Final Approval & Scheduling"
                            icon="o-check-badge" last />

                        <div
                            class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 rounded-xl">
                            <div class="flex items-start">
                                <x-mary-icon name="o-information-circle"
                                    class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 mt-0.5" />
                                <div class="text-blue-800 dark:text-blue-200 text-sm">
                                    <p class="font-bold mb-1">Workflow Note</p>
                                    <p>
                                        This sequence is hardcoded based on institutional policy. Changes require
                                        development-level updates to the approval logic.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-mary-card>
            </div>
        </x-mary-tab>
    </x-mary-tabs>
</div>
