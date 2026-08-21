<x-layouts.superadmin>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-bold">Archive Management</h1>

        <x-ui.card>
            <div class="flex items-center gap-2 mb-4">
                <x-ui.input placeholder="Search archived items..." class="w-full" />
                <x-ui.select :options="['Events', 'Tickets', 'Users']" label="Type" />
            </div>
            <x-ui.table :headers="['Type', 'Title/Ref', 'Archived At', 'Actions']">
                <tr>
                    <td>Event</td>
                    <td>Event #120 - Seminar</td>
                    <td>2025-08-31</td>
                    <td class="space-x-1">
                        <x-ui.button size="xs" icon="o-eye" class="btn-ghost">View</x-ui.button>
                        <x-ui.button size="xs" icon="o-arrow-path" class="btn-ghost">Restore</x-ui.button>
                    </td>
                </tr>
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.superadmin>
