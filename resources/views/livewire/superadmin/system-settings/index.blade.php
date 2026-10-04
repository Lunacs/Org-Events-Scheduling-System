<div x-data="{}" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    {{-- Header --}}
    <x-ui.page-heading title="System Settings" subtitle="Manage core system configurations, organizations, and workflows.">
        <x-slot:actions>
            <x-ui.button icon="o-arrow-path" label="Refresh System Cache"
            class="btn-outline btn-sm bg-base-100 w-full sm:w-auto" wire:click="refreshCache"
            spinner="refreshCache" />
        </x-slot:actions>
    </x-ui.page-heading>

    {{-- Tabs Interface (inline DaisyUI tabs; activeTab entangled to Livewire) --}}
    <div x-data="{ tab: @entangle('activeTab') }"
        class="bg-base-100 dark:bg-base-200 rounded-xl border border-base-300">
        <div role="tablist" class="tabs tabs-bordered flex-wrap px-2 pt-2">
            <button type="button" role="tab" class="tab gap-2" :class="{ 'tab-active': tab === 'organizations' }"
                @click="tab = 'organizations'">
                <x-ui.icon name="o-user-group" class="w-4 h-4" /> Organizations
            </button>
            <button type="button" role="tab" class="tab gap-2" :class="{ 'tab-active': tab === 'courses' }"
                @click="tab = 'courses'">
                <x-ui.icon name="o-academic-cap" class="w-4 h-4" /> Courses
            </button>
            <button type="button" role="tab" class="tab gap-2" :class="{ 'tab-active': tab === 'event-types' }"
                @click="tab = 'event-types'">
                <x-ui.icon name="o-tag" class="w-4 h-4" /> Event Types
            </button>
            <button type="button" role="tab" class="tab gap-2" :class="{ 'tab-active': tab === 'venues' }"
                @click="tab = 'venues'">
                <x-ui.icon name="o-map-pin" class="w-4 h-4" /> Venues
            </button>
            <button type="button" role="tab" class="tab gap-2" :class="{ 'tab-active': tab === 'content' }"
                @click="tab = 'content'">
                <x-ui.icon name="o-document-text" class="w-4 h-4" /> Content Sections
            </button>
            <button type="button" role="tab" class="tab gap-2" :class="{ 'tab-active': tab === 'config' }"
                @click="tab = 'config'">
                <x-ui.icon name="o-cog-6-tooth" class="w-4 h-4" /> General Configuration
            </button>
        </div>

        <div x-show="tab === 'organizations'" x-cloak>
            <div class="p-2 sm:p-4 w-full mx-auto">
                {{-- Student Organizations Manager Component --}}
                <livewire:superadmin.system-settings.organization-manager />
            </div>
        </div>

        <div x-show="tab === 'courses'" x-cloak>
            <div class="p-2 sm:p-4 max-w-4xl mx-auto">
                {{-- Courses Manager Component --}}
                <livewire:superadmin.system-settings.course-manager defer.bundle />
            </div>
        </div>

        <div x-show="tab === 'event-types'" x-cloak>
            <div class="p-2 sm:p-4 max-w-4xl mx-auto">
                {{-- Event Types Manager Component --}}
                <livewire:superadmin.system-settings.event-type-manager defer.bundle />
            </div>
        </div>

        <div x-show="tab === 'venues'" x-cloak>
            <div class="p-2 sm:p-4 max-w-4xl mx-auto">
                {{-- Venues Manager Component --}}
                <livewire:superadmin.system-settings.venue-manager defer.bundle />
            </div>
        </div>

        <div x-show="tab === 'content'" x-cloak>
            <div class="p-2 sm:p-4 w-full mx-auto">
                {{-- Content Sections Manager Component --}}
                <livewire:superadmin.system-settings.content-section-manager defer.bundle />
            </div>
        </div>

        <div x-show="tab === 'config'" x-cloak>
            <div class="p-4 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-ui.card title="Office Settings" separator shadow class="dark:bg-base-200">
                    <p class="text-sm text-base-content/60 mb-4">Set default office and institutional
                        approval behavior.</p>
                    <form wire:submit="updateSettings">
                        <div class="space-y-4">
                            <x-ui.select wire:model="defaultOfficeId" :options="$offices
                                ->map(function ($office) {
                                    return ['id' => $office->office_id, 'name' => $office->office_name];
                                })
                                ->toArray()" option-value="id"
                                option-label="name" label="Default Office" placeholder="Select default office"
                                icon="o-building-office" />

                            <x-ui.toggle wire:model="crossOfficeApprovals" label="Enable cross-office approvals"
                                hint="Allows approvals from secondary offices" />

                            <div class="pt-2">
                                <x-ui.button type="submit" label="Save Changes" class="btn-primary w-full"
                                    spinner="updateSettings" />
                            </div>
                        </div>
                    </form>
                </x-ui.card>

                <x-ui.card title="Approval Workflows" separator shadow class="dark:bg-base-200">
                    <p class="text-sm text-base-content/60 mb-4">Current sequential approval process for
                        event requests.</p>
                    <div class="space-y-4">
                        <x-ui.timeline-item title="Student Organization" subtitle="Submit Event Request"
                            icon="o-paper-airplane" first />
                        <x-ui.timeline-item title="OSA Review" subtitle="Initial Review & Approval" icon="o-eye" />
                        <x-ui.timeline-item title="GSO Finalization" subtitle="Final Approval & Scheduling"
                            icon="o-check-badge" last />

                        <div
                            class="mt-4 p-4 bg-info/10  border border-info/30  rounded-xl">
                            <div class="flex items-start">
                                <x-ui.icon name="o-information-circle"
                                    class="w-5 h-5 text-info  mr-3 mt-0.5" />
                                <div class="text-info  text-sm">
                                    <p class="font-bold mb-1">Workflow Note</p>
                                    <p>
                                        This sequence is hardcoded based on institutional policy. Changes require
                                        development-level updates to the approval logic.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</div>
