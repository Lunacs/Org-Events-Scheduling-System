<x-layouts.superadmin>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-bold">Transaction Logs</h1>

        <x-mary-card>
            <div class="flex items-center gap-2 mb-4">
                <x-mary-input placeholder="Search logs..." class="w-full" />
                <x-mary-select :options="['All','Info','Warning','Error']" label="Level" />
            </div>
            <x-mary-table :headers="['When','Who','Action','Target','Details']">
                <x-slot:rows>
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
                </x-slot:rows>
            </x-mary-table>
        </x-mary-card>
    </div>
</x-layouts.superadmin>
