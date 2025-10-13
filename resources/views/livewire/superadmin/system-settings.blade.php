<div>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-bold">System Settings</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-mary-card title="Event Types">
                <div class="flex gap-2 mb-4">
                    <x-mary-input placeholder="New event type"
                        class="w-full focus:outline-none focus:ring-0 active:outline-none" />
                    <x-mary-button icon="o-plus" class="btn-accent">Add</x-mary-button>
                </div>
                <ul class="space-y-2">

                    <li>
                        <x-mary-list-item :item="['title' => 'Seminar']" value="title" icon="o-tag" />
                    </li>
                    <li>
                        <x-mary-list-item :item="['title' => 'Workshop']" value="title" icon="o-tag" />
                    </li>
                </ul>
            </x-mary-card>

            <x-mary-card title="Office Setup">
                <x-mary-select :options="[
                    ['id' => 1, 'name' => 'OSA'],
                    ['id' => 2, 'name' => 'GSO'],
                    ['id' => 3, 'name' => 'Student Orgs'],
                ]" option-value="id" label="helo" option-label="name"
                    label="Default Office" class="mb-4 text-black focus:outline-none" />
                <x-mary-toggle label="Enable cross-office approvals" />
            </x-mary-card>

            <x-mary-card title="Approval Workflows" class="lg:col-span-2">
                <x-mary-timeline-item title="Student Org" subtitle="Submit Request" icon="o-paper-airplane" />
                <x-mary-timeline-item title="OSA" subtitle="Review" icon="o-eye" />
                <x-mary-timeline-item title="GSO" subtitle="Finalize" icon="o-check-badge" />
                <x-mary-button icon="o-pencil-square" class="mt-4">Edit Workflow</x-mary-button>
            </x-mary-card>
        </div>
    </div>
</div>
