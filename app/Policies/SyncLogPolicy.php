<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Eloquent\Model;

/** Sync logs are written by the sync service only. */
class SyncLogPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MANAGE_SPORTS_DATA;

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Model $model): bool
    {
        return false;
    }
}
