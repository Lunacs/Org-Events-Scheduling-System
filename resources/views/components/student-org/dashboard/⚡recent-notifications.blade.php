<?php

use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    #[Computed]
    public function recentNotifications()
    {
        return auth()->user()->unreadNotifications()
            ->take(3)
            ->get();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <x-mary-card title="Recent Notifications" subtitle="Latest updates from OSA and GSO">
            <div class="animate-pulse space-y-3">
                <div class="h-16 bg-base-300 rounded"></div>
                <div class="h-16 bg-base-300 rounded"></div>
            </div>
        </x-mary-card>
        HTML;
    }
};
?>

<x-mary-card title="Recent Notifications" subtitle="Latest updates from OSA and GSO">
    <x-slot:menu>
        <x-mary-button label="View All" link="/student-org/notifications" icon="s-bell"
            class="btn-sm btn-ghost" wire:navigate />
    </x-slot:menu>

    <div class="space-y-3">
        @forelse($this->recentNotifications as $notification)
            @php
                $data = $notification->data;
                $createdAt = \Illuminate\Support\Carbon::parse($notification->created_at);
                $timeAgo = $createdAt->diffForHumans();

                $color = $data['color'] ?? 'info';

                $bgMap = [
                    'primary' => 'bg-primary/10 border-primary',
                    'success' => 'bg-success/10 border-success',
                    'error' => 'bg-error/10 border-error',
                    'warning' => 'bg-warning/10 border-warning',
                    'info' => 'bg-info/10 border-info',
                    'secondary' => 'bg-secondary/10 border-secondary',
                ];
                $iconColorMap = [
                    'primary' => 'text-primary',
                    'success' => 'text-success',
                    'error' => 'text-error',
                    'warning' => 'text-warning',
                    'info' => 'text-info',
                    'secondary' => 'text-secondary',
                ];
                $iconMap = [
                    'success' => 's-check-circle',
                    'warning' => 's-exclamation-triangle',
                    'error' => 's-x-circle',
                    'info' => 's-information-circle',
                ];

                $containerClass = $bgMap[$color] ?? 'bg-info/10 border-info';
                $iconColorClass = $iconColorMap[$color] ?? 'text-info';
                $icon = $iconMap[$color] ?? 's-bell';
            @endphp

            <div class="flex items-start gap-3 p-3 {{ $containerClass }} rounded-lg border-l-4">
                <x-mary-icon :name="$icon" class="w-5 h-5 {{ $iconColorClass }} mt-0.5" />
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-base-content">{{ $data['title'] ?? 'Notification' }}</p>
                    <p class="text-sm text-base-content/70">{{ $data['message'] ?? 'No message' }}</p>
                    <p class="text-xs text-base-content/50 mt-1">{{ $timeAgo }}</p>
                </div>
            </div>
        @empty
            <x-ui.empty-state title="No recent notifications"
                description="System updates and ticket feedback will appear here." icon="o-bell-slash"
                tone="secondary" iconColor="text-secondary" actionLabel="View Notification Center"
                actionLink="/student-org/notifications" />
        @endforelse
    </div>
</x-mary-card>
