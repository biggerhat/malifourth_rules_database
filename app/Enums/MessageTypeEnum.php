<?php

namespace App\Enums;

use App\Traits\Attributes\UsesEnumLabel;
use App\Traits\UsesEnumSelectOptions;

enum MessageTypeEnum: string
{
    use UsesEnumLabel;
    use UsesEnumSelectOptions;

    case default = 'default';
    case destructive = 'destructive';
}
