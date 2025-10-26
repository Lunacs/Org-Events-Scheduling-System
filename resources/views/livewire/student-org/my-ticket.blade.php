<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Tickets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Header Actions --}}
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">All Event Requests</h3>
                    <p class="text-sm text-gray-600">Track the progress of your submitted tickets</p>
                </div>
                <x-mary-button label="Submit New Ticket" icon="s-document-plus" class="btn-primary"
                               link="/student-org/submit-ticket" wire:navigate/>
            </div>

            {{-- Filter and Search --}}
            <x-mary-card>
                <div class="flex flex-wrap gap-4 items-end">
                    <x-mary-input label="Search Tickets" wire:model.live="search"
                                  placeholder="Search by title, ID, or description..." icon="s-magnifying-glass"
                                  class="flex-1 min-w-64"/>

                    <x-mary-select label="Status Filter" wire:model.live="statusFilter" :options="[
                        ['id' => '', 'name' => 'All Status'],
                        ['id' => 'draft', 'name' => 'Draft'],
                        ['id' => 'submitted', 'name' => 'Submitted'],
                        ['id' => 'under_review', 'name' => 'Under Review'],
                        ['id' => 'pending_osa', 'name' => 'Pending OSA Approval'],
                        ['id' => 'pending_gso', 'name' => 'Pending GSO Approval'],
                        ['id' => 'approved', 'name' => 'Approved'],
                        ['id' => 'rejected', 'name' => 'Rejected'],
                        ['id' => 'requires_revision', 'name' => 'Requires Revision'],
                    ]"
                                   placeholder="Filter by status"/>

                    <x-mary-select label="Date Range" wire:model.live="dateFilter" :options="[
                        ['id' => '', 'name' => 'All Time'],
                        ['id' => 'last_week', 'name' => 'Last Week'],
                        ['id' => 'last_month', 'name' => 'Last Month'],
                        ['id' => 'last_3_months', 'name' => 'Last 3 Months'],
                        ['id' => 'this_year', 'name' => 'This Year'],
                    ]"
                                   placeholder="Filter by date"/>

                    <x-mary-button icon="s-funnel" class="btn-ghost" wire:click="clearFilters"/>
                </div>
            </x-mary-card>

            {{-- Tickets List --}}
            <x-mary-card>
                <div class="space-y-4">
                    {{-- Ticket Item 1 --}}
                    @foreach($tickets as $ticket)
                        <x-tickets.ticketinfo :tickets="$ticket"/>

                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6 flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        Showing 3 of 12 tickets
                    </div>
                    <div class="flex space-x-2">
                        <x-mary-button icon="s-chevron-left" class="btn-sm btn-ghost" disabled/>
                        <x-mary-button label="1" class="btn-sm btn-primary"/>
                        <x-mary-button label="2" class="btn-sm btn-ghost"/>
                        <x-mary-button label="3" class="btn-sm btn-ghost"/>
                        <x-mary-button icon="s-chevron-right" class="btn-sm btn-ghost"/>
                    </div>
                </div>
            </x-mary-card>

            {{-- Quick Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-mary-stat title="Total Submitted" value="12" icon="s-document-text" color="text-primary"/>

                <x-mary-stat title="Under Review" value="4" icon="s-clock" color="text-warning"/>

                <x-mary-stat title="Approved" value="7" icon="s-check-circle" color="text-success"/>

                <x-mary-stat title="Need Action" value="1" icon="s-exclamation-triangle" color="text-error"/>
            </div>
        </div>
    </div>

</div>
