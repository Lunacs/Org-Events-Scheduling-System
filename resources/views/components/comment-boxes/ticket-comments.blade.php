@props(['ticket'])

<div class="bg-base-100 rounded-box shadow-lg p-6" x-data="{ isSubmitting: false }" x-init="$nextTick(() => { if (window.AvatarHelper) window.AvatarHelper.initAvatars(); })"
    @comment-added.window="
        isSubmitting = false;
        $nextTick(() => { if (window.AvatarHelper) window.AvatarHelper.initAvatars(); })
    ">
    <h2 class="text-xl font-bold text-base-content mb-4">Comments</h2>
    @if ($ticket->comments->count() > 0)
        <div class="mt-4 space-y-4" wire:key="comments-list-{{ $ticket->ticket_id }}">
            @foreach ($ticket->comments as $comment)
                <div class="chat {{ $comment->user_id === auth()->id() ? 'chat-end' : 'chat-start' }}"
                    wire:key="comment-{{ $comment->id }}">
                    <div class="chat-image avatar">
                        <div class="w-10 rounded-full bg-base-300">
                            <img data-avatar="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}"
                                draggable="false" class="rounded-full w-full h-full object-cover" />
                        </div>
                    </div>
                    <div class="chat-header">
                        {{ $comment->user->name }}
                        <x-mary-badge value="{{ $comment->user->role_display }}" class="badge-primary text-xs ml-2" />
                        <time class="text-xs opacity-50">{{ $comment->created_at->diffForHumans() }}</time>
                    </div>
                    <div class="chat-bubble">{{ $comment->content }}</div>
                </div>
            @endforeach
        </div>
    @else
        <div class="mt-4 text-center text-base-content/50 py-8">
            <p>No comments yet. Be the first to comment!</p>
        </div>
    @endif
    <div class="space-y-3 mt-4">
        <div>
            <textarea wire:model.defer="comment"
                class="textarea textarea-bordered w-full h-4 @error('comment') textarea-error @enderror"
                placeholder="Add a comment..."
                x-on:keydown.ctrl.enter="if (!isSubmitting && $el.value.trim().length >= 3) { $wire.addComment(); isSubmitting = true; }"
                :disabled="isSubmitting"></textarea>
            @error('comment')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>
        <button class="btn btn-primary w-full" wire:click="addComment"
            x-on:click="if (!isSubmitting) { isSubmitting = true; setTimeout(() => { if (isSubmitting) { isSubmitting = false; if (window.AvatarHelper) window.AvatarHelper.initAvatars(); } }, 2000); }"
            :disabled="isSubmitting" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="addComment">Add Comment</span>
            <span wire:loading wire:target="addComment" class="loading loading-spinner loading-sm"></span>
        </button>
    </div>

    @script
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.hook('morph.updated', ({
                    el,
                    component
                }) => {
                    if (window.AvatarHelper) {
                        window.AvatarHelper.initAvatars();
                    }
                });
            });
        </script>
    @endscript
</div>
