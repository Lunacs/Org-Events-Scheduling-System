<div class="rounded-box border border-base-300 bg-base-100 p-4 sm:p-5" x-data="{ isSubmitting: false }" x-init="$nextTick(() => { if (window.AvatarHelper) window.AvatarHelper.initAvatars(); })"
    @comment-added.window="
        isSubmitting = false;
        $nextTick(() => { if (window.AvatarHelper) window.AvatarHelper.initAvatars(); })
    ">
    <h2 class="flex items-center gap-2 text-base font-semibold text-base-content">
        <x-ui.icon name="o-chat-bubble-left-right" class="size-4 shrink-0 text-base-content/60" aria-hidden="true" />
        Comments
        @if ($ticket->comments->count() > 0)
            <span class="badge badge-soft badge-sm tabular-nums">{{ $ticket->comments->count() }}</span>
        @endif
    </h2>

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
                        <x-ui.badge value="{{ $comment->user->role_display }}" class="badge-soft badge-sm ms-1.5" />
                        <time class="ms-1 text-xs text-base-content/70" datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->diffForHumans() }}</time>
                    </div>
                    <div @class(['chat-bubble whitespace-pre-line text-sm', 'bg-primary text-primary-content' => $comment->user_id === auth()->id(), 'bg-base-200 text-base-content' => $comment->user_id !== auth()->id()])>{{ $comment->content }}</div>
                </div>
            @endforeach
        </div>
    @else
        <p class="mt-3 text-sm text-base-content/70">
            No comments yet. Use comments to ask a question or clarify details about this request.
        </p>
    @endif

    @if ($ticket->areCommentsDisabled())
        <div class="mt-4 rounded-field border border-base-300 bg-base-200 p-3">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-base-content/70 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-base-content/80">
                        @if ($ticket->status === 'completed')
                            Comments are disabled for completed events.
                        @else
                            Comments are disabled for events that have already passed.
                        @endif
                    </p>
                    <p class="text-xs text-base-content/60 mt-1">
                        You can still view previous comments above.
                    </p>
                </div>
            </div>
        </div>
    @elseif (auth()->user()?->can('tickets.comment'))
        <div class="space-y-3 mt-4">
            <div>
                <label for="ticket-comment-{{ $ticket->ticket_id }}" class="sr-only">Add a comment</label>
                <textarea id="ticket-comment-{{ $ticket->ticket_id }}" wire:model.defer="comment" rows="3"
                    class="textarea w-full @error('comment') textarea-error @enderror" placeholder="Add a comment…"
                    x-on:keydown.ctrl.enter="if (!isSubmitting && $el.value.trim().length >= 3) { $wire.addComment(); isSubmitting = true; }"
                    :disabled="isSubmitting"></textarea>
                @error('comment')
                    <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center justify-between gap-3">
            <p class="hidden text-xs text-base-content/70 sm:block">Press Ctrl + Enter to post.</p>
            <button type="button" class="btn btn-primary btn-sm ms-auto" wire:click="addComment"
                x-on:click="if (!isSubmitting) { isSubmitting = true; setTimeout(() => { if (isSubmitting) { isSubmitting = false; if (window.AvatarHelper) window.AvatarHelper.initAvatars(); } }, 2000); }"
                :disabled="isSubmitting" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="addComment">Post comment</span>
                <span wire:loading wire:target="addComment" class="inline-flex items-center gap-2"><span class="loading loading-spinner loading-xs" aria-hidden="true"></span>Posting…</span>
            </button>
            </div>
        </div>
    @endif

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
