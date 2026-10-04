@props(['ticket'])

<div class="overflow-hidden rounded-box border border-base-300 bg-base-100 p-4 sm:p-5">
    <h2 class="text-base font-semibold text-base-content mb-4 flex items-center gap-2">
        <x-ui.icon name="o-currency-dollar" class="size-4 shrink-0 text-base-content/60" aria-hidden="true" />
        <span class="break-words">Budget Information</span>
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 overflow-hidden">
        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Estimated Total
                Budget</p>
            <p class="text-base-content font-semibold text-base md:text-lg break-words">
                ₱{{ number_format($ticket->estimated_budget ?? 0, 2) }}</p>
        </div>

        <div class="min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">Funding Source</p>
            <p class="text-base-content break-words">{{ $ticket->fundSource->source_name ?? 'N/A' }}</p>
        </div>
    </div>

    @if ($ticket->budget_breakdown)
        <div class="mt-4 min-w-0">
            <p class="text-[0.8125rem] text-base-content/70">{{ $ticket->budget_breakdown_label }}</p>
            <p class="text-base-content whitespace-pre-wrap break-words overflow-wrap-anywhere bg-base-200 p-3 rounded">
                {{ $ticket->budget_breakdown }}</p>
        </div>
    @endif

    {{-- IGP Request --}}
    <div class="mt-4 min-w-0">
        <p class="text-[0.8125rem] text-base-content/70">IGP Request</p>
        <p class="text-base-content">
            @if ($ticket->igp_requested)
                <x-ui.badge value="Requested" class="badge-soft badge-success" />
                @if ($ticket->igp_details)
                    <span
                        class="block mt-2 bg-base-200 p-3 rounded whitespace-pre-wrap break-words overflow-wrap-anywhere">{{ $ticket->igp_details }}</span>
                @endif
            @else
                <x-ui.badge value="Not requested" class="badge-soft" />
            @endif
        </p>
    </div>
</div>
