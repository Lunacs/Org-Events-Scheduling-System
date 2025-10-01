<x-layouts.superadmin>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-bold">System Settings</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-mary-card title="Event Types">
                <div class="flex gap-2 mb-4">
                    <x-mary-input placeholder="New event type" class="w-full" />
                    <x-mary-button icon="o-plus" class="btn-accent">Add</x-mary-button>
                </div>
                <ul class="space-y-2">
                    <li><x-mary-list-item title="Seminar" icon="o-tag" /></li>
                    <li><x-mary-list-item title="Workshop" icon="o-tag" /></li>
                </ul>
            </x-mary-card>

            <x-mary-card title="Office Setup">
                <x-mary-select :options="['OSA','GSO','Student Orgs']" label="Default Office" class="mb-4" />
                <x-mary-toggle label="Enable cross-office approvals" />
            </x-mary-card>

            <x-mary-card title="Approval Workflows" class="lg:col-span-2">
                <x-mary-timeline>
                    <x-mary-timeline-item title="Student Org" subtitle="Submit Request" icon="o-paper-airplane" />
                    <x-mary-timeline-item title="OSA" subtitle="Review" icon="o-eye" />
                    <x-mary-timeline-item title="GSO" subtitle="Finalize" icon="o-check-badge" />
                </x-mary-timeline>
                <x-mary-button icon="o-pencil-square" class="mt-4">Edit Workflow</x-mary-button>
            </x-mary-card>
        </div>
    </div>
</x-layouts.superadmin>
