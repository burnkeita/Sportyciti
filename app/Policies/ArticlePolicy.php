<?php

namespace App\Policies;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use App\Support\Permissions;

/**
 * Editorial rules:
 *  - Contributors and journalists may create articles and edit their OWN articles
 *    only while they are drafts or awaiting review. They can never publish.
 *  - Editors and above may edit any article and publish, schedule or archive.
 *  - Deleting requires "delete articles", except authors may delete their own drafts.
 */
class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::CREATE_ARTICLES) || $user->can(Permissions::EDIT_ANY_ARTICLE);
    }

    public function view(User $user, Article $article): bool
    {
        return $user->can(Permissions::EDIT_ANY_ARTICLE) || $article->author_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::CREATE_ARTICLES);
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->can(Permissions::EDIT_ANY_ARTICLE)) {
            return true;
        }

        return $article->author_id === $user->id
            && $user->can(Permissions::EDIT_OWN_ARTICLES)
            && in_array($article->status, [ArticleStatus::Draft, ArticleStatus::InReview], true);
    }

    public function delete(User $user, Article $article): bool
    {
        if ($user->can(Permissions::DELETE_ARTICLES)) {
            return true;
        }

        return $article->author_id === $user->id && $article->status === ArticleStatus::Draft;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permissions::DELETE_ARTICLES);
    }

    public function restore(User $user, Article $article): bool
    {
        return $user->can(Permissions::DELETE_ARTICLES);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can(Permissions::DELETE_ARTICLES);
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return $user->hasRole(Permissions::SUPER_ADMIN) || $user->hasRole(Permissions::ADMIN);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasRole(Permissions::SUPER_ADMIN) || $user->hasRole(Permissions::ADMIN);
    }

    public function publish(User $user, ?Article $article = null): bool
    {
        return $user->can(Permissions::PUBLISH_ARTICLES);
    }

    public function feature(User $user, ?Article $article = null): bool
    {
        return $user->can(Permissions::FEATURE_ARTICLES);
    }
}
