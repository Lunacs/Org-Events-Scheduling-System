<x-layouts.superadmin>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-bold">Transaction Logs</h1>

        <x-ui.card>
            <div class="flex items-center gap-2 mb-4">
                <x-ui.input placeholder="Search logs..." class="w-full" />
                <x-ui.select :options="['All', 'Info', 'Warning', 'Error']" label="Level" />
            </div>
            <x-ui.table :headers="['When', 'Who', 'Action', 'Target', 'Details']">
                <tr>
                    <td>2025-09-26 21:11</td>
                    <td>admin@plv.edu</td>
                    <td>APPROVED</td>
                    <td>Event #123</td>
                    <td>OSA approval</td>
                </tr>
                <tr>
                    <td>2025-09-26 20:45</td>
                    <td>osa@plv.edu</td>
                    <td>UPDATED</td>
                    <td>User #456</td>
                    <td>Role changed to GSO</td>
                </tr>
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.superadmin>
