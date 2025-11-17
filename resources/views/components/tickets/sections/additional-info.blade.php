@props(['ticket'])

@if ($ticket->additional_notes)
    <div class="bg-base-100 rounded-box shadow-lg p-6">
        <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
            <x-mary-icon name="o-document-text" class="w-5 h-5" />
            Additional Information
        </h2>
        <p class="text-base-content whitespace-pre-wrap bg-base-200 p-4 rounded">
            {{ $ticket->additional_notes }}</p>
    </div>
@endif
