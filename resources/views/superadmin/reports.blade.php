<x-layouts.superadmin>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-bold">Reports & Analytics</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-mary-card title="Monthly Events">
                <x-mary-chart :series="[[10,12,8,15,18,22,19,25,20,17,14,16]]" :labels="['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']" type="line" height="220" />
            </x-mary-card>
            <x-mary-card title="Most Used Venues">
                <x-mary-chart :series="[[35,25,20,15,5]]" :labels="['Auditorium','Gym','Hall A','Hall B','Room 101']" type="donut" height="220" />
            </x-mary-card>
            <x-mary-card title="Tickets by Status">
                <x-mary-chart :series="[[45,30,15,10]]" :labels="['Resolved','Open','Pending','Closed']" type="bar" height="220" />
            </x-mary-card>
        </div>

        <x-mary-card title="Export">
            <div class="flex gap-2">
                <x-mary-select :options="['This month','Last month','This year']" label="Range"/>
                <x-mary-button icon="o-arrow-down-tray" class="btn-primary">Download CSV</x-mary-button>
                <x-mary-button icon="o-document-text" class="btn-secondary">Download PDF</x-mary-button>
            </div>
        </x-mary-card>
    </div>
</x-layouts.superadmin>
