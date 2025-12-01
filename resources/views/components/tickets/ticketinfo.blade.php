@props(['tickets'])

{{-- Ticket Item --}}
<div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
    <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-3 mb-2">
                <h4 class="text-lg font-semibold">{{ $tickets->title }}</h4>
                <x-tickets.progress-badge :status="$tickets->status"/>
                <span class="text-sm text-gray-500">#{{ $tickets->ticket_number }}</span>
            </div>
            <p class="text-gray-600 mb-3 break-words">{{ $tickets->description }}</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="flex items-center space-x-2">
                    <x-mary-icon name="s-calendar" class="w-4 h-4 text-gray-400"/>
                    <span
                        class="text-sm">{{ \Carbon\Carbon::parse($tickets->date_from)->format('M j, Y') }} • {{ \Carbon\Carbon::parse($tickets->time_from)->format('h:i A') }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <x-mary-icon name="s-map-pin" class="w-4 h-4 text-gray-400"/>
                    <span class="text-sm">{{ $tickets->venue_requested }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <x-mary-icon name="s-users" class="w-4 h-4 text-gray-400"/>
                    <span class="text-sm">{{ $tickets->total_participants }} attendees expected</span>
                </div>
            </div>

            {{-- Progress Steps --}}
            <x-tickets.progress-section :status="$tickets->status"/>
        </div>

        <x-tickets.ticket-actions :status="$tickets->status" :ticket="$tickets"/>
    </div>

    <div class="mt-4 pt-4 border-t border-gray-100">
        {{-- Latest Comment/Remark --}}
        <x-tickets.latest-remark :status="$tickets->status" :ticket="$tickets"/>
    </div>

    <div class="mt-3 text-sm text-gray-500">
        Submitted on {{ \Carbon\Carbon::parse($tickets->created_at)->format('F j, Y') }} • Last
        updated {{ \Carbon\Carbon::parse($tickets->updated_at)->format('F j, Y') }}
    </div>
</div>
