<?php

use App\Models\Transaction_Logs;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    #[Computed(persist: true, seconds: 600)]
    public function recentActivity(): array
    {
        return Cache::remember('osa_dashboard_recent_activity', 300, function () {
            return Transaction_Logs::with('user')
                // The action names TransactionLogService actually stores.
                ->whereIn('action', [
                    'TICKET_CREATED',
                    'TICKET_AMENDED',
                    'TICKET_APPROVED',
                    'TICKET_FINAL_APPROVED',
                    'TICKET_FORWARDED',
                    'TICKET_FOR_REVISION',
                    'OFFICE_APPROVED',
                    'OFFICE_FOR_REVISION',
                ])
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function ($log) {
                    $icon = match (true) {
                        str_contains($log->action, 'APPROVED') => 'o-check-circle',
                        str_contains($log->action, 'REJECTED'), str_contains($log->action, 'REVISION') => 'o-x-circle',
                        str_contains($log->action, 'FORWARDED') => 'o-paper-airplane',
                        str_contains($log->action, 'CREATED') || str_contains($log->action, 'AMENDED') => 'o-document-plus',
                        default => 'o-information-circle',
                    };

                    $iconClass = match (true) {
                        str_contains($log->action, 'APPROVED') => 'text-success',
                        str_contains($log->action, 'REJECTED') => 'text-error',
                        str_contains($log->action, 'REVISION') => 'text-warning',
                        str_contains($log->action, 'FORWARDED') => 'text-info',
                        str_contains($log->action, 'CREATED') || str_contains($log->action, 'AMENDED') => 'text-primary',
                        default => 'text-base-content/60',
                    };

                    return [
                        'id' => $log->log_id,
                        'action' => ucwords(strtolower(str_replace('_', ' ', $log->action))),
                        'details' => $log->details,
                        'time_ago' => $log->created_at?->diffForHumans() ?? 'Just now',
                        'icon' => $icon,
                        'icon_class' => $iconClass,
                    ];
                })
                ->toArray();
        });
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="rounded-box border border-base-300 bg-base-100 p-4 sm:p-5">
            <div class="animate-pulse space-y-4">
                <div class="h-5 bg-base-300 rounded w-1/3"></div>
                <div class="h-4 bg-base-300 rounded w-full"></div>
                <div class="h-4 bg-base-300 rounded w-5/6"></div>
                <div class="h-4 bg-base-300 rounded w-3/4"></div>
            </div>
        </div>
        HTML;
    }
};
?>

<div class="rounded-box border border-base-300 bg-base-100 p-4 sm:p-5">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-base-content">Recent activity</h2>
            <p class="mt-0.5 text-sm text-base-content/70">Latest decisions and changes across all tickets.</p>
        </div>
    </div>
    @if (count($this->recentActivity) > 0)
        <div class="space-y-4">
            @foreach ($this->recentActivity as $index => $activity)
                <div class="flex gap-3" wire:key="activity-{{ $activity['id'] }}">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center">
                            <x-ui.icon :name="$activity['icon']" class="w-4 h-4 {{ $activity['icon_class'] }}" />
                        </div>
                        @if (! $loop->last)
                            <div class="w-px flex-1 bg-base-300 mt-2"></div>
                        @endif
                    </div>
                    <div class="flex-1 pb-4">
                        <p class="text-sm font-medium text-base-content">{{ $activity['action'] }}</p>
                        <p class="text-xs text-base-content/60">{{ $activity['details'] }}</p>
                        <p class="text-xs text-base-content/60 mt-1">{{ $activity['time_ago'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <x-ui.icon name="o-clock" class="w-8 h-8 mx-auto mb-2 text-base-content/30" />
            <p class="text-sm text-base-content/60">No recent activity</p>
        </div>
    @endif
</div>
