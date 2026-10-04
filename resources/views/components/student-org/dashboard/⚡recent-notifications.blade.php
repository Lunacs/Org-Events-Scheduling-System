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
        <x-ui.card title="Latest updates" subtitle="Unread messages from OSA and GSO.">
            <div class="animate-pulse space-y-3" aria-hidden="true">
                <div class="h-12 rounded-field bg-base-200"></div>
                <div class="h-12 rounded-field bg-base-200"></div>
            </div>
        </x-ui.card>
        HTML;
    }
};
?>

<x-ui.card title="Latest updates" subtitle="Unread messages from OSA and GSO.">
    <x-slot:menu>
        <x-ui.button label="View all" link="{{ route('student-org.notifications') }}" class="btn-sm btn-ghost" />
    </x-slot:menu>

    @if ($this->recentNotifications->isEmpty())
        <x-ui.empty-state compact icon="o-bell" title="You're all caught up"
            description="Decisions, revision requests, and comments on your tickets appear here." />
    @else
        <ul class="-my-2 divide-y divide-base-300">
            @foreach ($this->recentNotifications as $notification)
                @php
                    $data = $notification->data;
                    $tone = $data['color'] ?? 'info';
                    $icon = match ($tone) {
                        'success' => 's-check-circle',
                        'warning' => 's-exclamation-triangle',
                        'error' => 's-x-circle',
                        default => 's-information-circle',
                    };
                    $iconClass = match ($tone) {
                        'success' => 'text-success',
                        'warning' => 'text-warning',
                        'error' => 'text-error',
                        default => 'text-info',
                    };
                @endphp
                <li class="flex items-start gap-3 py-3" wire:key="note-{{ $notification->id }}">
                    <x-ui.icon :name="$icon" class="mt-0.5 size-4 shrink-0 {{ $iconClass }}" aria-hidden="true" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-base-content">{{ $data['title'] ?? 'Notification' }}</p>
                        <p class="line-clamp-2 text-sm text-base-content/70">{{ $data['message'] ?? '' }}</p>
                        <p class="mt-1 text-xs text-base-content/70">
                            {{ \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans() }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-ui.card>
