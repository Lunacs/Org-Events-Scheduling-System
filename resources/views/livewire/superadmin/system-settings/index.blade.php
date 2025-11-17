<div x-data="{}" class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold font-heading">System Settings</h1>
        <x-mary-button icon="o-arrow-path" class="btn-outline" wire:click="refreshCache">
            Refresh Cache
        </x-mary-button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Event Types Manager Component --}}
        <livewire:superadmin.system-settings.event-type-manager />

        {{-- Student Organizations Manager Component --}}
        <livewire:superadmin.system-settings.organization-manager />

        {{-- Courses Manager Component --}}
        <livewire:superadmin.system-settings.course-manager />

        <x-mary-card title="Office Settings">
            <form wire:submit="updateSettings">
                <div class="space-y-4">
                    <x-mary-select wire:model="defaultOfficeId" :options="$offices
                        ->map(function ($office) {
                            return ['id' => $office->office_id, 'name' => $office->office_name];
                        })
                        ->toArray()" option-value="id" option-label="name"
                        label="Default Office" placeholder="Select default office" />

                    <x-mary-toggle wire:model="crossOfficeApprovals" label="Enable cross-office approvals" />

                    <x-mary-button type="submit" class="btn-primary w-full">
                        Update Settings
                    </x-mary-button>
                </div>
            </form>
        </x-mary-card>

        <x-mary-card title="Approval Workflows" class="lg:col-span-2">
            <div class="space-y-4">
                <x-mary-timeline-item title="Student Organization" subtitle="Submit Event Request"
                    icon="o-paper-airplane" first />
                <x-mary-timeline-item title="OSA Review" subtitle="Initial Review & Approval" icon="o-eye" />
                <x-mary-timeline-item title="GSO Finalization" subtitle="Final Approval & Scheduling"
                    icon="o-check-badge" last />

                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <x-mary-icon name="o-information-circle" class="w-5 h-5 text-blue-600 mr-2" />
                        <p class="text-blue-800 text-sm">
                            <strong>Note:</strong> This workflow can be customized based on your
                            organization's
                            requirements.
                            Contact your system administrator for workflow modifications.
                        </p>
                    </div>
                </div>
            </div>
        </x-mary-card>
    </div>
</div>
