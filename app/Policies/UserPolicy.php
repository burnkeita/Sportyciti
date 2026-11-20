<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MANAGE_USERS;

    /** Only a Super Administrator may modify another Super Administrator. */
    public function update(User $user, Model $model): bool
    {
        if ($model->hasRole(Permissions::SUPER_ADMIN) && ! $user->hasRole(Permissions::SUPER_ADMIN)) {
            return false;
        }

        return $user->can($this->permission);
    }

    /** Users cannot delete themselves, and Super Administrators are protected. */
    public function delete(User $user, Model $model): bool
    {
        if ($user->is($model) || $model->hasRole(Permissions::SUPER_ADMIN)) {
            return false;
        }

        return $user->can($this->permission);
    }
}
