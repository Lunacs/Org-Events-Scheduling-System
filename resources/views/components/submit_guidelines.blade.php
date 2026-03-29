@props(['guidelines' => null])

@if ($guidelines && $guidelines->is_active)
    <div class="bg-info/10 p-4 rounded-lg border-l-4 border-info mb-4">
        <div class="flex items-start space-x-2">
            <x-mary-icon name="s-information-circle" class="w-5 h-5 text-info mt-0.5" />
            <div class="text-sm">
                <p class="font-medium mb-2">Important Guidelines:</p>
                <div class="prose prose-sm max-w-none">
                    {{ h($guidelines->content) }}
                </div>
            </div>
        </div>
    </div>
@else
    <div class="bg-info/10 p-4 rounded-lg border-l-4 border-info mb-4">
        <div class="flex items-start space-x-2">
            <x-mary-icon name="s-information-circle" class="w-5 h-5 text-info mt-0.5" />
            <div class="text-sm">
                <p class="font-medium mb-2">Important Guidelines:</p>
                <ul class="list-disc list-inside space-y-1 text-base-content/80">
                    <li>Submit your event request at least 2 weeks before the scheduled date.</li>
                    <li>Ensure all required information and documents are complete before submission.</li>
                    <li>Events must comply with university policies and guidelines.</li>
                    <li>The requesting organization is responsible for the conduct of the event.</li>
                    <li>Attach all required documentary requirements before submitting.</li>
                </ul>
            </div>
        </div>
    </div>
@endif
