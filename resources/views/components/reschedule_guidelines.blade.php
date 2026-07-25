@props(['guidelines' => null])

@if ($guidelines && $guidelines->is_active)
    <x-ui.card>
        <div class="bg-warning/10 p-4 rounded-lg border-l-4 border-warning">
            <div class="flex items-start space-x-3">
                <x-ui.icon name="s-exclamation-triangle" class="w-5 h-5 text-warning mt-0.5" />
                <div class="text-sm">
                    <p class="font-medium mb-2">Important Notice:</p>
                    <div class="prose prose-sm max-w-none text-base-content/80">
                        {{ h($guidelines->content) }}
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>
@else
    <x-ui.card>
        <div class="bg-warning/10 p-4 rounded-lg border-l-4 border-warning">
            <div class="flex items-start space-x-3">
                <x-ui.icon name="s-exclamation-triangle" class="w-5 h-5 text-warning mt-0.5" />
                <div class="text-sm">
                    <p class="font-medium mb-2">Important Notice:</p>
                    <ul class="list-disc list-inside space-y-1 text-base-content/80">
                        <li>Reschedule requests must be submitted at least 2 days before the current
                            event date
                        </li>
                        <li>All reschedule requests are subject to approval by OSA and GSO</li>
                        <li>Venue availability will be checked for your new requested date</li>
                        <li>You will be notified via email about the status of your request</li>
                        <li>Frequent reschedule requests may affect future event approvals</li>
                    </ul>
                </div>
            </div>
        </div>
    </x-ui.card>
@endif
