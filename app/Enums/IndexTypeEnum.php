<?php

namespace App\Enums;

use App\Traits\Attributes\UsesEnumLabel;
use App\Traits\UsesEnumSelectOptions;

enum IndexTypeEnum: string
{
    use UsesEnumLabel;
    use UsesEnumSelectOptions;

    case Image = 'image';
    case Text = 'text';
}
