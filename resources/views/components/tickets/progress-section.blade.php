@props(['status'])

{{-- Progress Steps --}}
<div class="mb-4">
    <div class="flex items-center space-x-4">
        @if(in_array(strtolower($status), ['received', 'rescheduled', 'amended']))
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">Submitted</span>
            </div>
            <div class="flex-1 h-0.5 bg-success"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-warning rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-clock" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">OSA Review</span>
            </div>
            <div class="flex-1 h-0.5 bg-gray-200"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-clock" class="w-4 h-4 text-gray-400"/>
                </div>
                <span class="text-sm text-gray-400">GSO Review</span>
            </div>
            <div class="flex-1 h-0.5 bg-gray-200"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check-circle" class="w-4 h-4 text-gray-400"/>
                </div>
                <span class="text-sm text-gray-400">Approved</span>
            </div>
        @elseif(strtolower($status) == 'gso_review')
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">Submitted</span>
            </div>
            <div class="flex-1 h-0.5 bg-success"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">OSA Review</span>
            </div>
            <div class="flex-1 h-0.5 bg-success"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-warning rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-clock" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm text-gray-400">GSO Review</span>
            </div>
            <div class="flex-1 h-0.5 bg-gray-200"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check-circle" class="w-4 h-4 text-gray-400"/>
                </div>
                <span class="text-sm text-gray-400">Approved</span>
            </div>
        @elseif(strtolower($status) == 'approved')
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">Submitted</span>
            </div>
            <div class="flex-1 h-0.5 bg-success"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">OSA Review</span>
            </div>
            <div class="flex-1 h-0.5 bg-success"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">GSO Review</span>
            </div>
            <div class="flex-1 h-0.5 bg-success"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check-circle" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">Approved</span>
            </div>
        @elseif(in_array(strtolower($status), ['for_rescheduling', 'needs_revision']))
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">Submitted</span>
            </div>
            <div class="flex-1 h-0.5 bg-warning"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-warning rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-exclamation-triangle"
                                 class="w-4 h-4 text-white"/>
                </div>
                @if(strtolower($status) === 'needs_revision')
                    <span class="text-sm font-medium">Needs Revision</span>
                @elseif(strtolower($status) === 'for_rescheduling')
                    <span class="text-sm font-medium">Needs Rescheduling</span>
                @endif
            </div>
            <div class="flex-1 h-0.5 bg-gray-200"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-clock" class="w-4 h-4 text-gray-400"/>
                </div>
                <span class="text-sm text-gray-400">GSO Review</span>
            </div>
            <div class="flex-1 h-0.5 bg-gray-200"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check-circle" class="w-4 h-4 text-gray-400"/>
                </div>
                <span class="text-sm text-gray-400">Approved</span>
            </div>
        @elseif(strtolower($status) == 'rejected')
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">Submitted</span>
            </div>
            <div class="flex-1 h-0.5 bg-success"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">OSA Review</span>
            </div>
            <div class="flex-1 h-0.5 bg-success"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-check" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">GSO Review</span>
            </div>
            <div class="flex-1 h-0.5 bg-error"></div>
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-error rounded-full flex items-center justify-center">
                    <x-mary-icon name="s-x-circle" class="w-4 h-4 text-white"/>
                </div>
                <span class="text-sm font-medium">Rejected</span>
            </div>
        @endif

    </div>
</div>
