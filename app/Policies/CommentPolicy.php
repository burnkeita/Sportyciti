<?php

namespace App\Policies;

use App\Support\Permissions;

class CommentPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MODERATE_COMMENTS;
}
