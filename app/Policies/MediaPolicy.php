<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;
use App\Support\Permissions;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::UPLOAD_MEDIA) || $user->can(Permissions::MANAGE_MEDIA);
    }

    public function view(User $user, Media $media): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Media $media): bool
    {
        return $user->can(Permissions::MANAGE_MEDIA) || ($media->uploaded_by === $user->id && $user->can(Permissions::UPLOAD_MEDIA));
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->can(Permissions::MANAGE_MEDIA);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permissions::MANAGE_MEDIA);
    }
}
