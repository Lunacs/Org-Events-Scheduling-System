{{-- Reusable Notification List Skeleton --}}
<div class="space-y-4 animate-pulse w-full">
    @for ($i = 0; $i < 6; $i++)
        <div class="flex items-start gap-4 p-4 bg-base-200/30 border border-base-300 rounded-lg w-full">
            <div class="shrink-0">
                <div class="w-10 h-10 bg-base-300 rounded-full"></div>
            </div>
            <div class="flex-1 min-w-0 space-y-2">
                <div class="h-5 bg-base-300 rounded w-1/3"></div>
                <div class="h-4 bg-base-200 rounded w-3/4"></div>
                <div class="flex items-center gap-4 mt-2">
                    <div class="h-3 bg-base-200 rounded w-20"></div>
                    <div class="h-3 bg-base-200 rounded w-16"></div>
                </div>
            </div>
            <div class="shrink-0">
                <div class="w-3 h-3 bg-base-300 rounded-full"></div>
            </div>
        </div>
        @if ($i < 5)
            <div class="divider my-0 opacity-50"></div>
        @endif
    @endfor
</div>
