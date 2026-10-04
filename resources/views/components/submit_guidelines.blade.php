@props(['guidelines' => null])

{{-- Ticket submission guidelines (CMS-managed when available). Shown inside a disclosure on step 1. --}}
@if ($guidelines && $guidelines->is_active)
    <div class="prose prose-sm max-w-none text-base-content/80">
        {{ h($guidelines->content) }}
    </div>
@else
    <ul class="list-disc space-y-1.5 ps-5 text-sm text-base-content/80">
        <li>Submit your request at least <strong class="font-semibold text-base-content">2 weeks</strong> before the event date.</li>
        <li>Complete every required field and attach all documentary requirements before you submit.</li>
        <li>Events must follow university policies and guidelines.</li>
        <li>Your organization is responsible for the conduct of the event.</li>
    </ul>
@endif
