<x-layouts.superadmin>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-bold">System Settings</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-ui.card title="Event Types">
                <div class="flex gap-2 mb-4">
                    <x-ui.input placeholder="New event type" class="w-full" />
                    <x-ui.button icon="o-plus" class="btn-accent">Add</x-ui.button>
                </div>
                <ul class="space-y-2">
                    <li><x-ui.list-item title="Seminar" icon="o-tag" /></li>
                    <li><x-ui.list-item title="Workshop" icon="o-tag" /></li>
                </ul>
            </x-ui.card>

            <x-ui.card title="Office Setup">
                <x-ui.select :options="['OSA', 'GSO', 'Student Orgs']" label="Default Office" class="mb-4" />
                <x-ui.toggle label="Enable cross-office approvals" />
            </x-ui.card>

            <x-ui.card title="Approval Workflows" class="lg:col-span-2">
                <x-ui.timeline-item title="Student Org" subtitle="Submit Request" icon="o-paper-airplane" first />
                <x-ui.timeline-item title="OSA" subtitle="Review" icon="o-eye" />
                <x-ui.timeline-item title="GSO" subtitle="Finalize" icon="o-check-badge" last />
                <x-ui.button icon="o-pencil-square" class="mt-4">Edit Workflow</x-ui.button>
            </x-ui.card>
        </div>
    </div>
</x-layouts.superadmin>
