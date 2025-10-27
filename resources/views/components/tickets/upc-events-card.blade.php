@props(['ticket'])

<div class="flex items-center space-x-4 p-4 bg-base-200 rounded-lg">
    <div class="flex-shrink-0">
        <x-mary-icon name="s-calendar-days" class="w-6 h-6 text-success" />
    </div>
    <div class="flex-1">
        <h4 class="font-semibold">{{ $ticket->title }}</h4>
        <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($ticket->date_from)->format('M j, Y') }} • {{ \Carbon\Carbon::parse($ticket->time_from)->format('h:i A') }}</p>
        <p class="text-sm text-gray-500">{{ $ticket->venue_requested }}</p>
    </div>
    <div>
        <x-mary-badge value="Confirmed" class="badge-success" />
    </div>
</div>
