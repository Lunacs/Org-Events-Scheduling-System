<div>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">SuperAdmin Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-mary-stat title="Total Users" value="1,245" icon="o-users" />
            <x-mary-stat title="Tickets" value="87" icon="o-ticket" />
            <x-mary-stat title="Events" value="42" icon="o-calendar-days" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-mary-card title="Pending Approvals" class="col-span-1 lg:col-span-2">
                <x-mary-table :headers="[
                    ['key' => 'request', 'label' => 'Request'],
                    ['key' => 'type', 'label' => 'Type'],
                    ['key' => 'submitted', 'label' => 'Submitted'],
                    ['key' => 'status', 'label' => 'Status'],
                ]" :rows="[
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
                ]">

                    @scope('cell_status', $row)
                        <x-mary-badge value="{{ $row['status'] }}" class="badge-warning" />
                    @endscope
                </x-mary-table>
            </x-mary-card>

            <x-mary-card shadow separator title="Recent Logs">
                <ul class="space-y-2">
                    <li>
                        <x-mary-list-item :item="['title' => 'admin@plv.edu', 'subtitle' => 'Approved event #123']" value="title" sub-value="subtitle" icon="o-check-circle" />
                    </li>
                    <li>
                        <x-mary-list-item :item="['title' => 'osa@plv.edu', 'subtitle' => 'Updated user role']" value="title" sub-value="subtitle"
                            icon="o-pencil-square" />
                    </li>
                </ul>
            </x-mary-card>
        </div>
    </div>
</div>
