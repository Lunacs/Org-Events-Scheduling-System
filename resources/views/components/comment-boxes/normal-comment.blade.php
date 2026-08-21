@props(['comment'])

{{-- Latest Comment/Remark --}}
<div class="bg-blue-50 p-3 rounded-lg mt-4">
    <div class="flex items-start space-x-3">
        <x-ui.icon name="s-chat-bubble-left" class="w-5 h-5 text-blue-500 mt-0.5"/>
        <div class="flex-1">
            <p class="text-sm font-medium text-blue-700">From: {{ $comment->user->name }} ({{ $comment->namingConvention() }})</p>
            <p class="text-sm text-blue-600 mt-1">{{ $comment->content }}</p>
            <p class="text-xs text-blue-500 mt-2">{{ $comment->updated_at->diffForHumans() }}</p>
        </div>
    </div>
</div>
