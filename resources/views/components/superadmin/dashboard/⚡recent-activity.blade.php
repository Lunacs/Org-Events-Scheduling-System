<?php

use App\Models\Transaction_Logs;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    #[Computed(persist: true, seconds: 120)]
    public function recentActivity(): array
    {
        return Cache::remember('superadmin_dashboard_recent_activity', 120, function () {
            return Transaction_Logs::select(['log_id', 'action', 'details', 'created_at', 'user_id'])
                ->with('user:user_id,email,name')
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->log_id,
                        'user' => $log->user ? $log->user->name : 'System',
                        'email' => $log->user ? $log->user->email : 'system@app',
                        'action' => $log->action,
                        'details' => $log->details,
                        'timestamp' => $log->created_at->setTimezone('Asia/Manila')->format('M d, Y g:i A'),
                        'time_ago' => $log->created_at->diffForHumans(),
                    ];
                })
                ->toArray();
        });
    }

    public function placeholder()
    {
        return <<<'HTML'
        <x-ui.card title="Recent Activity" subtitle="Latest system activity" shadow>
            <div class="animate-pulse space-y-3">
                <div class="h-4 bg-base-300 rounded w-full"></div>
                <div class="h-4 bg-base-300 rounded w-5/6"></div>
                <div class="h-4 bg-base-300 rounded w-3/4"></div>
                <div class="h-4 bg-base-300 rounded w-4/5"></div>
            </div>
        </x-ui.card>
        HTML;
    }
};
?>

<x-ui.card title="Recent Activity" subtitle="Latest system activity" shadow>
    @if (count($this->recentActivity) > 0)
        <div class="space-y-1">
            @foreach ($this->recentActivity as $activity)
                <div class="flex items-start gap-3 p-3 hover:bg-base-200/50 rounded-lg transition-colors">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                        <x-ui.icon name="o-user" class="w-4 h-4 text-primary" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm">
                            <span class="font-medium">{{ $activity['user'] }}</span>
                            <span class="text-base-content/70">{{ $activity['action'] }}</span>
                        </div>
                        <div class="text-xs text-base-content/50 truncate">{{ $activity['details'] }}</div>
                        <div class="text-xs text-base-content/40 mt-1">{{ $activity['time_ago'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-base-200 mb-3">
                <x-ui.icon name="o-document-text" class="w-8 h-8 text-base-content/40" />
            </div>
            <p class="text-sm text-base-content/60 font-medium">No recent activity</p>
        </div>
    @endif
</x-ui.card>
