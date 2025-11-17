@props(['ticket'])

<div class="bg-base-100 rounded-box shadow-lg p-6">
    <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
        <x-mary-icon name="o-currency-dollar" class="w-5 h-5" />
        Budget Information
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-base-content/70">Estimated Total
                Budget</label>
            <p class="text-base-content font-semibold text-lg">
                ₱{{ number_format($ticket->estimated_budget ?? 0, 2) }}</p>
        </div>

        <div>
            <label class="text-sm font-medium text-base-content/70">Funding Source</label>
            <p class="text-base-content">{{ $ticket->fundSource->source_name ?? 'N/A' }}</p>
        </div>
    </div>

    @if ($ticket->budget_breakdown)
        <div class="mt-4">
            <label class="text-sm font-medium text-base-content/70">Budget Breakdown</label>
            <p class="text-base-content whitespace-pre-wrap bg-base-200 p-3 rounded">
                {{ $ticket->budget_breakdown }}</p>
        </div>
    @endif

    {{-- IGP Request --}}
    <div class="mt-4">
        <label class="text-sm font-medium text-base-content/70">IGP Request</label>
        <p class="text-base-content">
            @if ($ticket->igp_requested)
                <x-mary-badge value="Requested" class="badge-success" />
                @if ($ticket->igp_details)
                    <span
                        class="block mt-2 bg-base-200 p-3 rounded whitespace-pre-wrap">{{ $ticket->igp_details }}</span>
                @endif
            @else
                <x-mary-badge value="Not Requested" class="badge-neutral" />
            @endif
        </p>
    </div>
</div>
