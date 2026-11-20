<?php

namespace App\Policies;

use App\Support\Permissions;

class TransferPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MANAGE_TRANSFERS;
}
