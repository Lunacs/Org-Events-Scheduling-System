@props(['ticket'])

@if ($ticket->additional_notes)
    <div class="bg-base-100 rounded-box shadow-lg p-4 md:p-6 overflow-hidden">
        <h2 class="text-lg md:text-xl font-bold text-base-content mb-4 flex items-center gap-2">
            <x-mary-icon name="o-document-text" class="w-5 h-5 flex-shrink-0" />
            <span class="break-words">Additional Information</span>
        </h2>
        <p class="text-base-content whitespace-pre-wrap break-words overflow-wrap-anywhere bg-base-200 p-3 md:p-4 rounded min-w-0">
            {{ $ticket->additional_notes }}</p>
    </div>
@endif
