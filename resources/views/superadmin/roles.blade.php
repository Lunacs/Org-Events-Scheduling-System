<x-layouts.superadmin>
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Roles & Permissions</h1>
            <x-ui.button icon="o-plus" class="btn-accent">Create Role</x-ui.button>
        </div>

        <x-ui.card title="Roles">
            <x-ui.table :headers="['Role', 'Users', 'Permissions', 'Actions']">
                <tr>
                    <td>SuperAdmin</td>
                    <td>2</td>
                    <td>All</td>
                    <td>
                        <x-ui.button size="xs" icon="o-pencil-square" class="btn-ghost">Edit</x-ui.button>
                    </td>
                </tr>
            </x-ui.table>
        </x-ui.card>

        <x-ui.alert icon="o-information-circle" class="alert-info">
            If you are using spatie/laravel-permission, sync roles and permissions here.
        </x-ui.alert>
    </div>
</x-layouts.superadmin>
