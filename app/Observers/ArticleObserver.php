<?php

namespace App\Observers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Services\Content\ArticleWorkflow;
use App\Services\Content\HtmlSanitizer;
use App\Support\Permissions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;

class ArticleObserver
{
    public function __construct(
        private readonly ArticleWorkflow $workflow,
        private readonly HtmlSanitizer $sanitizer,
    ) {}

    public function saving(Article $article): void
    {
        $user = auth()->user();

        // Defence in depth: even if a form or API bypasses ArticleWorkflow::normalise(),
        // an authenticated user without publish rights cannot move an article into a live state.
        if ($user && $article->isDirty('status') && $article->status?->requiresPublishPermission() && ! $user->can(Permissions::PUBLISH_ARTICLES)) {
            throw new AuthorizationException('You are not allowed to publish, schedule or archive articles.');
        }

        if ($article->isDirty('body')) {
            $article->body = $this->sanitizer->sanitize((string) $article->body);
            $article->reading_time = Article::estimateReadingTime($article->body);
        }

        if ($article->status === ArticleStatus::Published && ! $article->published_at) {
            $article->published_at = now();
        }

        $this->workflow->applyTransitionMetadata($article, $user);
    }

    public function updated(Article $article): void
    {
        if ($article->wasChanged(['title', 'excerpt', 'body', 'insight_blocks', 'status'])) {
            $article->revisions()->create([
                'user_id' => auth()->id(),
                'title' => $article->getOriginal('title'),
                'excerpt' => $article->getOriginal('excerpt'),
                'body' => $article->getOriginal('body'),
                'insight_blocks' => $article->getRawOriginal('insight_blocks') ? json_decode($article->getRawOriginal('insight_blocks'), true) : null,
                'status' => $article->getOriginal('status') instanceof ArticleStatus ? $article->getOriginal('status')->value : (string) $article->getOriginal('status'),
                'note' => $article->wasChanged('status') ? 'Status changed to '.$article->status->getLabel() : null,
            ]);
        }

        $this->flushCaches();
    }

    public function created(Article $article): void
    {
        $this->flushCaches();
    }

    public function deleted(Article $article): void
    {
        $this->flushCaches();
    }

    private function flushCaches(): void
    {
        foreach (['home.sections.data', 'articles.breaking', 'articles.trending', 'articles.most_read', 'sitemap.news'] as $key) {
            Cache::forget($key);
        }
    }
}
