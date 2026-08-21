<x-layouts.superadmin>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">SuperAdmin Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-ui.stat title="Total Users" value="1,245" icon="o-users" />
            <x-ui.stat title="Tickets" value="87" icon="o-ticket" />
            <x-ui.stat title="Events" value="42" icon="o-calendar-days" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-ui.card title="Pending Approvals" class="col-span-1 lg:col-span-2">
                @php
                    $pendingApprovals = [
                        [
                            'request' => 'Event #123',
                            'type' => 'Venue',
                            'submitted' => '2025-09-20',
                            'status' => 'Pending',
                        ],
                        [
                            'request' => 'User #456',
                            'type' => 'Account',
                            'submitted' => '2025-09-21',
                            'status' => 'Pending',
                        ],
                    ];
                @endphp

                <x-ui.table :headers="[
                    ['key' => 'request', 'label' => 'Request'],
                    ['key' => 'type', 'label' => 'Type'],
                    ['key' => 'submitted', 'label' => 'Submitted'],
                    ['key' => 'status', 'label' => 'Status'],
                ]" :rows="$pendingApprovals">
                    @foreach ($pendingApprovals as $row)
                        <tr>
                            <x-ui.table-column>{{ $row['request'] }}</x-ui.table-column>
                            <x-ui.table-column>{{ $row['type'] }}</x-ui.table-column>
                            <x-ui.table-column>{{ $row['submitted'] }}</x-ui.table-column>
                            <x-ui.table-column>
                                <x-ui.badge value="{{ $row['status'] }}" class="badge-warning" />
                            </x-ui.table-column>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>

            <x-ui.card title="Recent Logs">
                <ul class="space-y-2">
                    <li><x-ui.list-item title="admin@plv.edu" subtitle="Approved event #123" icon="o-check-circle" />
                    </li>
                    <li><x-ui.list-item title="osa@plv.edu" subtitle="Updated user role" icon="o-pencil-square" />
                    </li>
                </ul>
            </x-ui.card>
        </div>
    </div>
</x-layouts.superadmin>
