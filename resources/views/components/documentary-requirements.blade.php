{{--
    Documentary Requirements Component
    Displays documentary requirements from Event_Type rich text field

    Usage:
        <x-documentary-requirements :event-type="$eventType" />
        or
        <x-documentary-requirements :event-type-id="$eventTypeId" />
--}}

@props(['eventType' => null, 'eventTypeId' => null])

@php
    // Resolve Event_Type model
    $resolvedEventType = $eventType;
    if (!$resolvedEventType && $eventTypeId) {
        $resolvedEventType = \App\Models\Event_Type::find($eventTypeId);
    }

    $requirements = null;
    if ($resolvedEventType && $resolvedEventType->documentary_requirements) {
        $requirements = $resolvedEventType->documentary_requirements;
    }

    $isRichText = $requirements instanceof \Tonysm\RichTextLaravel\Models\RichText;
@endphp

<div class="bg-warning/10 p-4 rounded-lg border-l-4 border-warning">
    <div class="flex items-start space-x-2">
        <x-mary-icon name="s-exclamation-triangle" class="w-5 h-5 text-warning mt-0.5" />
        <div class="text-sm flex-1">
            <p class="font-medium mb-1">Required Documents:</p>
            @if ($isRichText)
                {{-- Rich text from Event_Type --}}
                <div class="prose prose-sm prose-slate dark:prose-invert max-w-none text-gray-600">
                    <ul class="list-disc list-inside space-y-1">
                        {{-- <li>Document containing the Rationale</li> --}}
                    </ul>
                    {{ h((string) $requirements) }}
                </div>
            @elseif ($resolvedEventType)
                {{-- Fallback to config if no rich text set --}}
                @php
                    $configRequirements = config(
                        "event_requirements.documents.{$resolvedEventType->event_type_id}",
                        [],
                    );
                @endphp
                @if (!empty($configRequirements))
                    <ul class="list-disc list-inside space-y-1 text-gray-600">
                        <li>Document containing the Rationale</li>
                        @foreach ($configRequirements as $document)
                            @if (is_array($document) && isset($document['nested']))
                                <li>{{ $document[0] }}</li>
                                <ul class="list-disc list-inside ml-8 mt-1 space-y-1 text-gray-600">
                                    @foreach ($document['nested'] as $nestedDoc)
                                        <li>{{ $nestedDoc }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <li>{{ is_array($document) ? $document[0] : $document }}</li>
                            @endif
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500 italic">No specific requirements defined for this event type.</p>
                @endif
            @else
                {{-- No event type provided --}}
                <p class="text-gray-500 italic">Select an event type to see required documents.</p>
            @endif
            {{ $slot ?? '' }}
        </div>
    </div>
</div>
