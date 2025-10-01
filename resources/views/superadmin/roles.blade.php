<x-layouts.superadmin>
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Roles & Permissions</h1>
            <x-mary-button icon="o-plus" class="btn-accent">Create Role</x-mary-button>
        </div>

        <x-mary-card title="Roles">
            <x-mary-table :headers="['Role','Users','Permissions','Actions']">
                <x-slot:rows>
                    <tr>
                        <td>SuperAdmin</td>
                        <td>2</td>
                        <td>All</td>
                        <td>
                            <x-mary-button size="xs" icon="o-pencil-square" class="btn-ghost">Edit</x-mary-button>
                        </td>
                    </tr>
                </x-slot:rows>
            </x-mary-table>
        </x-mary-card>

        <x-mary-alert icon="o-information-circle" class="alert-info">
            If you are using spatie/laravel-permission, sync roles and permissions here.
        </x-mary-alert>
    </div>
</x-layouts.superadmin>
