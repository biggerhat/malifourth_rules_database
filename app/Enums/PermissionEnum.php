<?php

namespace App\Enums;

use App\Traits\Attributes\UsesEnumLabel;
use App\Traits\UsesEnumSelectOptions;

enum PermissionEnum: string
{
    use UsesEnumLabel;
    use UsesEnumSelectOptions;

    case AddUser = 'add_user';
    case EditUser = 'edit_user';
    case DeleteUser = 'delete_user';
    case AddRole = 'add_role';
    case EditRole = 'edit_role';
    case DeleteRole = 'delete_role';
}
