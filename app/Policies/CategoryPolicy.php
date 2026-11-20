<?php

namespace App\Policies;

use App\Support\Permissions;

class CategoryPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MANAGE_CATEGORIES;
}
