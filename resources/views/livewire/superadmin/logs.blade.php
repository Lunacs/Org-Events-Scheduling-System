<div>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-bold">Transaction Logs</h1>

        <x-mary-card>
            <div class="flex items-center gap-2">
                <x-mary-input placeholder="Search logs..." class="w-full" />
                <x-mary-select :options="[
                    ['id' => 1, 'name' => 'All'],
                    ['id' => 2, 'name' => 'Info'],
                    ['id' => 3, 'name' => 'Warning'],
                    ['id' => 4, 'name' => 'Error'],
                ]" />
            </div>
            <x-mary-table :headers="[
                ['key' => 'when', 'label' => 'When'],
                ['key' => 'who', 'label' => 'Who'],
                ['key' => 'action', 'label' => 'Action'],
                ['key' => 'target', 'label' => 'Target'],
                ['key' => 'details', 'label' => 'Details'],
            ]" :rows="[
                [
                    'when' => '2025-09-26 21:11',
                    'who' => 'admin@plv.edu',
                    'action' => 'APPROVED',
                    'target' => 'Event #123',
                    'details' => 'OSA approval',
                ],
                [
                    'when' => '2025-09-26 20:45',
                    'who' => 'osa@plv.edu',
                    'action' => 'UPDATED',
                    'target' => 'User #456',
                    'details' => 'Role changed to GSO',
                ],
            ]">
            </x-mary-table>
        </x-mary-card>
    </div>
</div>
