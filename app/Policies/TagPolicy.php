<?php

namespace App\Policies;

use App\Support\Permissions;

class TagPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MANAGE_TAGS;
}
