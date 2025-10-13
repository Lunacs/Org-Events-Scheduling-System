<div>
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">User Management</h1>
            <x-mary-button icon="o-plus" class="btn-accent">Create User</x-mary-button>
        </div>

        <x-mary-card>
            <x-mary-table :headers="[
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'role', 'label' => 'Role'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'actions', 'label' => 'Actions'],
            ]" :rows="[
                [
                    'name' => 'Juan Dela Cruz',
                    'email' => 'juan@plv.edu',
                    'role' => 'OSA Staff',
                    'status' => 'Active',
                ],
            ]">

                @scope('cell_status', $row)
                    <x-mary-badge value="{{ $row['status'] }}" class="badge-success text-white" />
                @endscope

                @scope('cell_actions', $row)
                    <div class="space-x-1">
                        <x-mary-button size="xs" icon="o-pencil-square" class="btn-ghost">Edit</x-mary-button>
                        <x-mary-button size="xs" icon="o-user-minus" class="btn-ghost">Deactivate</x-mary-button>
                    </div>
                @endscope
            </x-mary-table>
        </x-mary-card>
    </div>
</div>
