<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Base policy: every ability is granted by a single management permission.
 * Subclasses set $permission (and may override individual abilities).
 */
abstract class PermissionPolicy
{
    protected string $permission;

    public function viewAny(User $user): bool
    {
        return $user->can($this->permission);
    }

    public function view(User $user, Model $model): bool
    {
        return $user->can($this->permission);
    }

    public function create(User $user): bool
    {
        return $user->can($this->permission);
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can($this->permission);
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->can($this->permission);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can($this->permission);
    }

    public function reorder(User $user): bool
    {
        return $user->can($this->permission);
    }
}
