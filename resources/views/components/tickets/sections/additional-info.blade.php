@props(['ticket'])

@if ($ticket->additional_notes)
    <div class="overflow-hidden rounded-box border border-base-300 bg-base-100 p-4 sm:p-5">
        <h2 class="text-base font-semibold text-base-content mb-4 flex items-center gap-2">
            <x-ui.icon name="o-document-text" class="size-4 shrink-0 text-base-content/60" aria-hidden="true" />
            <span class="break-words">Additional Information</span>
        </h2>
        <p class="text-base-content whitespace-pre-wrap break-words overflow-wrap-anywhere bg-base-200 p-3 md:p-4 rounded min-w-0">
            {{ $ticket->additional_notes }}</p>
    </div>
@endif
