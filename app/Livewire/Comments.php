<?php

namespace App\Livewire;

use App\Enums\CommentStatus;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Setting;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Moderated comments. Registered, verified readers may post; every comment is held
 * for moderation unless the moderator setting auto-approves trusted readers.
 */
class Comments extends Component
{
    #[Locked]
    public int $articleId;

    public string $body = '';

    public ?int $replyTo = null;

    public int $perPage = 20;

    public ?string $notice = null;

    public function mount(int $articleId): void
    {
        $this->articleId = $articleId;
    }

    #[Computed]
    public function article(): Article
    {
        return Article::query()->published()->select(['id', 'allow_comments'])->findOrFail($this->articleId);
    }

    #[Computed]
    public function comments()
    {
        return Comment::query()
            ->where('article_id', $this->articleId)->whereNull('parent_id')
            ->where('status', CommentStatus::Approved)
            ->with(['user:id,name,avatar_path', 'replies.user:id,name,avatar_path'])
            ->latest()->limit($this->perPage)->get();
    }

    #[Computed]
    public function total(): int
    {
        return Comment::where('article_id', $this->articleId)->where('status', CommentStatus::Approved)->count();
    }

    public function reply(int $commentId): void
    {
        $this->replyTo = Comment::where('article_id', $this->articleId)->where('status', CommentStatus::Approved)->whereKey($commentId)->exists() ? $commentId : null;
    }

    public function cancelReply(): void
    {
        $this->replyTo = null;
    }

    public function loadMore(): void
    {
        $this->perPage = min($this->perPage + 20, 200);
    }

    public function post(): void
    {
        $user = auth()->user();

        abort_unless($user && $user->hasVerifiedEmail(), 403);
        abort_unless($this->article->allow_comments && Setting::get('comments.enabled', true), 403);

        $this->validate(['body' => ['required', 'string', 'min:2', 'max:2000']]);

        $key = 'comment:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('body', 'You are commenting too quickly. Please wait a minute.');

            return;
        }
        RateLimiter::hit($key, 60);

        $trusted = Setting::get('comments.auto_approve_trusted', false)
            && Comment::where('user_id', $user->id)->where('status', CommentStatus::Approved)->count() >= 5;

        Comment::create([
            'article_id' => $this->articleId,
            'user_id' => $user->id,
            'parent_id' => $this->replyTo,
            'body' => strip_tags($this->body),
            'status' => $trusted ? CommentStatus::Approved : CommentStatus::Pending,
            'ip_hash' => hash_hmac('sha256', (string) request()->ip(), (string) config('app.key')),
        ]);

        $this->reset('body', 'replyTo');
        $this->notice = $trusted ? 'Your comment has been published.' : 'Thanks! Your comment will appear once a moderator has approved it.';
        unset($this->comments, $this->total);
    }

    public function render()
    {
        return view('livewire.comments', [
            'enabled' => $this->article->allow_comments && Setting::get('comments.enabled', true),
        ]);
    }
}
