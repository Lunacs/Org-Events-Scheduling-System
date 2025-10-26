@props(['status'])


<div class="mt-4 pt-4 border-t border-gray-100">
    @if(in_array(strtolower($status), ['received', 'rescheduled', 'amended', 'gso_review']))
        {{-- Latest Comment/Remark --}}
        <div class="bg-blue-50 p-3 rounded-lg">
            <div class="flex items-start space-x-3">
                <x-mary-icon name="s-chat-bubble-left" class="w-5 h-5 text-blue-500 mt-0.5"/>
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-700">Latest Update from OSA</p>
                    <p class="text-sm text-blue-600 mt-1">Your event proposal looks good. Please
                        provide the list of expected attendees with their contact information for
                        security purposes.</p>
                    <p class="text-xs text-blue-500 mt-2">2 days ago</p>
                </div>
            </div>
        </div>
    @elseif(strtolower($status) == 'approved')
        {{-- Approval Notice --}}
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="bg-green-50 p-3 rounded-lg">
                <div class="flex items-start space-x-3">
                    <x-mary-icon name="s-check-circle" class="w-5 h-5 text-green-500 mt-0.5"/>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-green-700">Event Approved!</p>
                        <p class="text-sm text-green-600 mt-1">Congratulations! Your event has been
                            approved by both OSA and GSO. You may now proceed with your preparations.
                            Please ensure to follow all safety guidelines and submit a post-event
                            report.</p>
                        <p class="text-xs text-green-500 mt-2">1 week ago</p>
                    </div>
                </div>
            </div>
        </div>
    @elseif(in_array(strtolower($status), ['for_rescheduling', 'needs_revision']))
        {{-- Revision Request --}}
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="bg-orange-50 p-3 rounded-lg">
                <div class="flex items-start space-x-3">
                    <x-mary-icon name="s-exclamation-triangle"
                                 class="w-5 h-5 text-orange-500 mt-0.5"/>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-orange-700">Revision Required</p>
                        <p class="text-sm text-orange-600 mt-1">Please provide more details about the
                            workshop facilitators, their qualifications, and a detailed schedule for
                            each day. Also, include the registration process for participants and
                            maximum capacity per session.</p>
                        <p class="text-xs text-orange-500 mt-2">3 days ago</p>
                        <x-mary-button label="Submit Revision" icon="s-arrow-up"
                                       class="btn-sm btn-primary mt-2"/>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
