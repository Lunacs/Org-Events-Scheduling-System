<x-layouts.superadmin>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-bold">Archive Management</h1>

        <x-mary-card>
            <div class="flex items-center gap-2 mb-4">
                <x-mary-input placeholder="Search archived items..." class="w-full" />
                <x-mary-select :options="['Events','Tickets','Users']" label="Type" />
            </div>
            <x-mary-table :headers="['Type','Title/Ref','Archived At','Actions']">
                <x-slot:rows>
                    <tr>
                        <td>Event</td>
                        <td>Event #120 - Seminar</td>
                        <td>2025-08-31</td>
                        <td class="space-x-1">
                            <x-mary-button size="xs" icon="o-eye" class="btn-ghost">View</x-mary-button>
                            <x-mary-button size="xs" icon="o-arrow-path" class="btn-ghost">Restore</x-mary-button>
                        </td>
                    </tr>
                </x-slot:rows>
            </x-mary-table>
        </x-mary-card>
    </div>
</x-layouts.superadmin>
