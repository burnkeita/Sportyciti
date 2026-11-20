<?php

namespace App\Services\Content;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use App\Support\Permissions;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Enforces the editorial workflow: draft → in review → scheduled/published → archived.
 * All status changes made through the CMS pass through normalise() so that the
 * rules are applied server-side regardless of what the form submitted.
 */
class ArticleWorkflow
{
    /** @return list<ArticleStatus> */
    public function allowedStatuses(User $user): array
    {
        if ($user->can(Permissions::PUBLISH_ARTICLES)) {
            return ArticleStatus::cases();
        }

        return [ArticleStatus::Draft, ArticleStatus::InReview];
    }

    /**
     * Validate and normalise form data before an article is saved.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalise(array $data, User $user, ?Article $article = null): array
    {
        $target = $data['status'] ?? $article?->status ?? ArticleStatus::Draft;
        $target = $target instanceof ArticleStatus ? $target : ArticleStatus::from($target);
        $current = $article?->status;

        if ($target !== $current && $target->requiresPublishPermission() && ! $user->can(Permissions::PUBLISH_ARTICLES)) {
            throw ValidationException::withMessages([
                'status' => 'You do not have permission to publish, schedule or archive articles. Submit it for review instead.',
            ]);
        }

        // Authors without publish rights cannot alter a live article's publication date.
        if (! $user->can(Permissions::PUBLISH_ARTICLES)) {
            unset($data['published_at']);
        }

        if (! $user->can(Permissions::FEATURE_ARTICLES)) {
            unset($data['is_featured'], $data['is_breaking'], $data['breaking_until']);
        }

        $publishAt = isset($data['published_at']) && $data['published_at'] ? Carbon::parse($data['published_at']) : null;

        if ($target === ArticleStatus::Scheduled) {
            if (! $publishAt || $publishAt->lte(now())) {
                throw ValidationException::withMessages([
                    'published_at' => 'A scheduled article needs a publication date in the future.',
                ]);
            }
        }

        if ($target === ArticleStatus::Published) {
            // Publishing with a future date is treated as scheduling.
            if ($publishAt && $publishAt->gt(now()->addMinute())) {
                $target = ArticleStatus::Scheduled;
            } else {
                $data['published_at'] = $publishAt ?? $article?->published_at ?? now();
            }
        }

        $data['status'] = $target;

        return $data;
    }

    /** Apply the side-effects of a status transition (timestamps, approver). */
    public function applyTransitionMetadata(Article $article, ?User $user): void
    {
        if (! $article->isDirty('status')) {
            return;
        }

        $status = $article->status;

        if ($status === ArticleStatus::InReview) {
            $article->submitted_at = now();
        }

        if (in_array($status, [ArticleStatus::Published, ArticleStatus::Scheduled], true) && $user) {
            $article->approved_by = $user->id;
            $article->approved_at = now();
        }
    }

    public function publishNow(Article $article, User $user): void
    {
        $this->guardPublish($user);
        $article->status = ArticleStatus::Published;
        $article->published_at ??= now();
        $article->save();
    }

    public function schedule(Article $article, User $user, CarbonInterface $when): void
    {
        $this->guardPublish($user);

        if ($when->lte(now())) {
            throw ValidationException::withMessages(['published_at' => 'Choose a time in the future.']);
        }

        $article->status = ArticleStatus::Scheduled;
        $article->published_at = $when;
        $article->save();
    }

    public function submitForReview(Article $article): void
    {
        $article->status = ArticleStatus::InReview;
        $article->save();
    }

    public function returnToDraft(Article $article, User $user, ?string $notes = null): void
    {
        $this->guardPublish($user);
        $article->status = ArticleStatus::Draft;
        $article->review_notes = $notes;
        $article->save();
    }

    public function archive(Article $article, User $user): void
    {
        $this->guardPublish($user);
        $article->status = ArticleStatus::Archived;
        $article->save();
    }

    /** Publish all scheduled articles whose time has come. Returns the number published. */
    public function releaseScheduled(): int
    {
        $count = 0;

        Article::query()
            ->where('status', ArticleStatus::Scheduled)
            ->where('published_at', '<=', now())
            ->each(function (Article $article) use (&$count): void {
                $article->status = ArticleStatus::Published;
                $article->save();
                $count++;
            });

        return $count;
    }

    private function guardPublish(User $user): void
    {
        if (! $user->can(Permissions::PUBLISH_ARTICLES)) {
            throw ValidationException::withMessages(['status' => 'You do not have permission to perform this editorial action.']);
        }
    }
}
