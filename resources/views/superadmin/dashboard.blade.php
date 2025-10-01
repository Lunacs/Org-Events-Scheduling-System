@php($title = 'Dashboard')
<x-layouts.superadmin>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">SuperAdmin Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-mary-stat title="Total Users" value="1,245" icon="o-users" />
            <x-mary-stat title="Tickets" value="87" icon="o-ticket" />
            <x-mary-stat title="Events" value="42" icon="o-calendar-days" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-mary-card title="Pending Approvals" class="col-span-1 lg:col-span-2">
                <x-mary-table :headers="['Request','Type','Submitted','Status']">
                    <x-slot:rows>
                        <tr><td>Event #123</td><td>Venue</td><td>2025-09-20</td><td><x-mary-badge value="Pending" class="badge-warning"/></td></tr>
                        <tr><td>User #456</td><td>Account</td><td>2025-09-21</td><td><x-mary-badge value="Pending" class="badge-warning"/></td></tr>
                    </x-slot:rows>
                </x-mary-table>
            </x-mary-card>

            <x-mary-card title="Recent Logs">
                <ul class="space-y-2">
                    <li><x-mary-list-item title="admin@plv.edu" subtitle="Approved event #123" icon="o-check-circle" /></li>
                    <li><x-mary-list-item title="osa@plv.edu" subtitle="Updated user role" icon="o-pencil-square" /></li>
                </ul>
            </x-mary-card>
        </div>
    </div>
</x-layouts.superadmin>
