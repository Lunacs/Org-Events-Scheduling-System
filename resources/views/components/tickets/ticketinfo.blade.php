@props(['tickets'])

{{-- Ticket Item 1 --}}
<div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-center space-x-3 mb-2">
                <h4 class="text-lg font-semibold">{{ $tickets->title }}</h4>
                <x-mary-badge value="Under Review" class="badge-info" />
                <span class="text-sm text-gray-500">#{{ $tickets->ticket_number }}</span>
            </div>
            <p class="text-gray-600 mb-3">{{ $tickets->description }}</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="flex items-center space-x-2">
                    <x-mary-icon name="s-calendar" class="w-4 h-4 text-gray-400" />
                    <span class="text-sm">Oct 15, 2025 • 2:00 PM</span>
                </div>
                <div class="flex items-center space-x-2">
                    <x-mary-icon name="s-map-pin" class="w-4 h-4 text-gray-400" />
                    <span class="text-sm">{{ $tickets->venue_requested }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <x-mary-icon name="s-users" class="w-4 h-4 text-gray-400" />
                    <span class="text-sm">{{ $tickets->total_participants }} attendees expected</span>
                </div>
            </div>

            {{-- Progress Steps --}}
            <div class="mb-4">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div
                            class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                            <x-mary-icon name="s-check" class="w-4 h-4 text-white" />
                        </div>
                        <span class="text-sm font-medium">Submitted</span>
                    </div>
                    <div class="flex-1 h-0.5 bg-success"></div>
                    <div class="flex items-center space-x-2">
                        <div
                            class="w-8 h-8 bg-warning rounded-full flex items-center justify-center">
                            <x-mary-icon name="s-clock" class="w-4 h-4 text-white" />
                        </div>
                        <span class="text-sm font-medium">OSA Review</span>
                    </div>
                    <div class="flex-1 h-0.5 bg-gray-200"></div>
                    <div class="flex items-center space-x-2">
                        <div
                            class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                            <x-mary-icon name="s-clock" class="w-4 h-4 text-gray-400" />
                        </div>
                        <span class="text-sm text-gray-400">GSO Review</span>
                    </div>
                    <div class="flex-1 h-0.5 bg-gray-200"></div>
                    <div class="flex items-center space-x-2">
                        <div
                            class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                            <x-mary-icon name="s-check-circle" class="w-4 h-4 text-gray-400" />
                        </div>
                        <span class="text-sm text-gray-400">Approved</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col space-y-2">
            <x-mary-button icon="s-eye" class="btn-sm btn-ghost" tooltip="View Details" />
            <x-mary-button icon="s-pencil" class="btn-sm btn-ghost" tooltip="Edit" />
            <x-mary-button icon="s-chat-bubble-left-right" class="btn-sm btn-ghost"
                           tooltip="Comments" />
        </div>
    </div>

    {{-- Latest Comment/Remark --}}
    <div class="mt-4 pt-4 border-t border-gray-100">
        <div class="bg-blue-50 p-3 rounded-lg">
            <div class="flex items-start space-x-3">
                <x-mary-icon name="s-chat-bubble-left" class="w-5 h-5 text-blue-500 mt-0.5" />
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-700">Latest Update from OSA</p>
                    <p class="text-sm text-blue-600 mt-1">Your event proposal looks good. Please
                        provide the list of expected attendees with their contact information for
                        security purposes.</p>
                    <p class="text-xs text-blue-500 mt-2">2 days ago</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 text-sm text-gray-500">
        Submitted on September 28, 2025 • Last updated October 1, 2025
    </div>
</div>
