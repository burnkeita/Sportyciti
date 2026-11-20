<section id="comments" class="space-y-6" aria-labelledby="comments-title">
    <h2 id="comments-title" class="section-title">Comments ({{ $this->total }})</h2>

    @if (! $enabled)
        <p class="text-sm text-slate-500">Comments are closed for this article.</p>
    @else
        @auth
            @if (auth()->user()->hasVerifiedEmail())
                <form wire:submit="post" class="card space-y-3 p-4">
                    @if ($replyTo)
                        <p class="flex items-center justify-between rounded bg-slate-50 px-3 py-2 text-xs dark:bg-white/5">
                            Replying to a comment
                            <button type="button" wire:click="cancelReply" class="font-semibold text-pitch-700 hover:underline">Cancel</button>
                        </p>
                    @endif
                    <label for="comment-body" class="sr-only">Your comment</label>
                    <textarea id="comment-body" wire:model="body" rows="3" maxlength="2000" class="input" placeholder="Join the conversation — keep it respectful."></textarea>
                    @error('body')<p class="text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
                    @if ($notice)<p class="text-sm font-medium text-pitch-700 dark:text-pitch-400" role="status">{{ $notice }}</p>@endif
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs text-slate-500">Comments are moderated. Read our <a href="{{ route('pages.show', 'terms') }}" class="underline">community rules</a>.</p>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">Post comment</button>
                    </div>
                </form>
            @else
                <p class="card p-4 text-sm">Please <a href="{{ route('verification.notice') }}" class="font-semibold text-pitch-700 underline">verify your email address</a> to comment.</p>
            @endif
        @else
            <p class="card p-4 text-sm"><a href="{{ route('login') }}" class="font-semibold text-pitch-700 underline">Sign in</a> or <a href="{{ route('register') }}" class="font-semibold text-pitch-700 underline">create a free account</a> to join the conversation.</p>
        @endauth

        <ul class="space-y-5">
            @foreach ($this->comments as $comment)
                <li wire:key="c-{{ $comment->id }}" class="flex gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-navy-900 text-xs font-bold text-white dark:bg-white/10">{{ $comment->user->initials() }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm"><span class="font-semibold text-navy-900 dark:text-white">{{ $comment->user->name }}</span> <span class="text-xs text-slate-400">· <x-local-time :at="$comment->created_at" format="relative" /></span></p>
                        <p class="mt-1 whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">{{ $comment->body }}</p>
                        @auth
                            <button type="button" wire:click="reply({{ $comment->id }})" class="mt-1 text-xs font-semibold text-slate-500 hover:text-pitch-700">Reply</button>
                        @endauth
                        @if ($comment->replies->isNotEmpty())
                            <ul class="mt-3 space-y-3 border-l-2 border-slate-100 pl-4 dark:border-white/10">
                                @foreach ($comment->replies as $reply)
                                    <li wire:key="r-{{ $reply->id }}">
                                        <p class="text-sm"><span class="font-semibold text-navy-900 dark:text-white">{{ $reply->user->name }}</span> <span class="text-xs text-slate-400">· <x-local-time :at="$reply->created_at" format="relative" /></span></p>
                                        <p class="mt-1 whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">{{ $reply->body }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        @if ($this->total > $this->comments->count())
            <button type="button" wire:click="loadMore" class="btn-ghost w-full">Load more comments</button>
        @endif
    @endif
</section>
