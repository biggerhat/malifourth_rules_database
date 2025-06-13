<?php

namespace App\Enums;

use App\Traits\Attributes\UsesEnumLabel;
use App\Traits\UsesEnumSelectOptions;

enum PermissionEnum: string
{
    use UsesEnumLabel;
    use UsesEnumSelectOptions;
}
