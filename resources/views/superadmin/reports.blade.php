<x-layouts.superadmin>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-bold">Reports & Analytics</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-ui.card title="Monthly Events">
                <x-ui.chart :series="[[10, 12, 8, 15, 18, 22, 19, 25, 20, 17, 14, 16]]" :labels="['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']" type="line" height="220" />
            </x-ui.card>
            <x-ui.card title="Most Used Venues">
                <x-ui.chart :series="[[35, 25, 20, 15, 5]]" :labels="['Auditorium', 'Gym', 'Hall A', 'Hall B', 'Room 101']" type="donut" height="220" />
            </x-ui.card>
            <x-ui.card title="Tickets by Status">
                <x-ui.chart :series="[[45, 30, 15, 10]]" :labels="['Resolved', 'Open', 'Pending', 'Closed']" type="bar" height="220" />
            </x-ui.card>
        </div>

        <x-ui.card title="Export">
            <div class="flex gap-2">
                <x-ui.select :options="['This month', 'Last month', 'This year']" label="Range" />
                <x-ui.button icon="o-arrow-down-tray" class="btn-primary">Download CSV</x-ui.button>
                <x-ui.button icon="o-document-text" class="btn-secondary">Download PDF</x-ui.button>
            </div>
        </x-ui.card>
    </div>
</x-layouts.superadmin>
