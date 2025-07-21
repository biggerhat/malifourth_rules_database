<?php

namespace App\Enums;

use App\Traits\Attributes\UsesEnumLabel;
use App\Traits\UsesEnumSelectOptions;

enum PermissionGroupEnum: string
{
    use UsesEnumLabel;
    use UsesEnumSelectOptions;

    case User = 'user';
    case Role = 'role';
    case Batch = 'batch';
    case Index = 'index';
    case Section = 'section';
    case Page = 'page';
    case Season = 'season';
    case Scheme = 'scheme';
    case Strategy = 'strategy';
}
