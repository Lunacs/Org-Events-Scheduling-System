<x-layouts.superadmin>
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">User Management</h1>
            <x-ui.button icon="o-plus" class="btn-accent">Create User</x-ui.button>
        </div>

        <x-ui.card>
            <x-ui.table :headers="['Name', 'Email', 'Role', 'Status', 'Actions']">
                <tr>
                    <td>Juan Dela Cruz</td>
                    <td>juan@plv.edu</td>
                    <td>OSA Staff</td>
                    <td><x-ui.badge value="Active" class="badge-success" /></td>
                    <td class="space-x-1">
                        <x-ui.button size="xs" icon="o-pencil-square" class="btn-ghost">Edit</x-ui.button>
                        <x-ui.button size="xs" icon="o-user-minus" class="btn-ghost">Deactivate</x-ui.button>
                    </td>
                </tr>
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.superadmin>
