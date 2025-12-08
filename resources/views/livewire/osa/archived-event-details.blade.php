<div>
    @if (!$event)
        <div class="space-y-3">
            <div class="h-6 bg-base-200 rounded animate-pulse"></div>
            <div class="h-4 bg-base-200 rounded animate-pulse"></div>
            <div class="h-4 bg-base-200 rounded animate-pulse w-3/4"></div>
        </div>
    @else
        <div class="space-y-6">
            <div class="border-b border-base-300 pb-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-bold">{{ $event->ticket->title }}</h2>
                        <p class="text-base-content/70">
                            {{ $event->ticket->user->studentOrganization->org_name ?? 'No Organization' }}
                        </p>
                    </div>
                    @php
                        $statusClasses = [
                            'approved' => 'badge-success',
                            'for_revision' => 'badge-error',
                            'completed' => 'badge-primary',
                        ];
                    @endphp
                    <span
                        class="badge {{ $statusClasses[$event->ticket->status] ?? 'badge-neutral' }} text-white">{{ ucfirst($event->ticket->status) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold mb-3">Event Details</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="font-medium text-base-content/70">Event Type:</span>
                            <span>{{ $event->eventType?->type_name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-base-content/70">Expected Attendees:</span>
                            <span>{{ $event->ticket->total_participants ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-base-content/70">Venue:</span>
                            <span>{{ $event->ticket->venue_requested ?? ($event->eventSchedules->first()?->venue ?? 'N/A') }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-base-content/70">Submitted:</span>
                            <span>{{ $event->ticket->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold mb-3">Decision</h3>
                    <div class="space-y-3">
                        @if ($event->ticket->latestOsaApproval)
                            <div class="bg-base-200 rounded-lg p-3">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-medium">OSA Decision</span>
                                </div>
                                <div class="text-sm space-y-1">
                                    <div>
                                        <span class="font-medium">Decision:</span>
                                        @php
                                            $decision = $event->ticket->latestOsaApproval->decision;
                                            $dClass =
                                                [
                                                    'approved' => 'badge-success',
                                                    'for_revision' => 'badge-error',
                                                    'forwarded' => 'badge-info',
                                                    'revision_requested' => 'badge-warning',
                                                ][$decision] ?? 'badge-neutral';
                                        @endphp
                                        <span
                                            class="badge badge-sm {{ $dClass }} text-white">{{ ucfirst($decision) }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Date:</span>
                                        {{ $event->ticket->latestOsaApproval->created_at?->format('M d, Y h:i A') }}
                                    </div>
                                    @if ($event->ticket->latestOsaApproval->remarks)
                                        <div>
                                            <span class="font-medium">Remarks:</span>
                                            <p class="mt-1 text-base-content/80">
                                                {{ $event->ticket->latestOsaApproval->remarks }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($event->eventSchedules?->count() > 0)
                <div>
                    <h3 class="font-semibold mb-3">Event Schedule</h3>
                    <div class="space-y-2">
                        @foreach ($event->eventSchedules as $schedule)
                            <div class="bg-base-200 rounded-lg p-3">
                                <div class="flex items-center gap-2 text-sm">
                                    <span>{{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }}</span>
                                    <span class="ml-4">{{ $schedule->start_time }} -
                                        {{ $schedule->end_time }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($event->ticket->description)
                <div>
                    <h3 class="font-semibold mb-3">Description</h3>
                    <p class="text-sm text-base-content/80 bg-base-200 rounded-lg p-4">
                        {{ $event->ticket->description }}</p>
                </div>
            @endif

            @if ($event->ticket->attachments->count() > 0)
                <div>
                    <h3 class="font-semibold mb-3">Attachments ({{ $event->ticket->attachments->count() }})</h3>
                    <div class="space-y-2">
                        @foreach ($event->ticket->attachments as $attachment)
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <p class="font-medium">{{ $attachment->file_name }}</p>
                                        <p class="text-sm text-base-content/70">{{ $attachment->file_size }} •
                                            {{ $attachment->file_type }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button class="btn btn-sm btn-ghost" title="Preview">Preview</button>
                                    <button class="btn btn-sm btn-ghost" title="Download">Download</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
