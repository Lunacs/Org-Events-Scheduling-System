<x-layouts.superadmin>
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">User Management</h1>
            <x-mary-button icon="o-plus" class="btn-accent">Create User</x-mary-button>
        </div>

        <x-mary-card>
            <x-mary-table :headers="['Name', 'Email', 'Role', 'Status', 'Actions']">
                <x-slot:rows>
                    <tr>
                        <td>Juan Dela Cruz</td>
                        <td>juan@plv.edu</td>
                        <td>OSA Staff</td>
                        <td><x-mary-badge value="Active" class="badge-success" /></td>
                        <td class="space-x-1">
                            <x-mary-button size="xs" icon="o-pencil-square" class="btn-ghost">Edit</x-mary-button>
                            <x-mary-button size="xs" icon="o-user-minus"
                                class="btn-ghost">Deactivate</x-mary-button>
                        </td>
                    </tr>
                </x-slot:rows>
            </x-mary-table>
        </x-mary-card>
    </div>
</x-layouts.superadmin>
