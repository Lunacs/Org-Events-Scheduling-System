<div>
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Roles & Permissions</h1>
            <x-mary-button icon="o-plus" class="btn-accent">Create Role</x-mary-button>
        </div>

        <x-mary-card title="Roles">
            <x-mary-table :headers="[
                ['key' => 'roles', 'label' => 'Roles'],
                ['key' => 'users', 'label' => 'Users'],
                ['key' => 'permissions', 'label' => 'Permissions'],
                ['key' => 'actions', 'label' => 'Actions'],
            ]" :rows="[
                [
                    'roles' => 'Superadmin',
                    'users' => '2',
                    'permissions' => 'All',
                    'actions' => 'Edit',
                ],
            ]">
                @scope('cell_actions', $row)
                    <x-mary-button size="xs" icon="o-pencil-square"
                        class="btn-ghost">{{ $row['actions'] }}</x-mary-button>
                @endscope
            </x-mary-table>
        </x-mary-card>

        <x-mary-alert icon="o-information-circle" class="alert-success text-base-200">
            If you are using spatie/laravel-permission, sync roles and permissions here.
        </x-mary-alert>
    </div>

</div>
