<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Eloquent\Model;

/** Audit logs are append-only: they can be viewed but never created, edited or deleted from the UI. */
class AuditLogPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::VIEW_AUDIT_LOGS;

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Model $model): bool
    {
        return false;
    }

    public function delete(User $user, Model $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
