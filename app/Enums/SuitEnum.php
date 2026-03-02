<?php

namespace App\Enums;

use App\Traits\Attributes\UsesEnumLabel;
use App\Traits\UsesEnumSelectOptions;

enum SuitEnum: string
{
    use UsesEnumLabel;
    use UsesEnumSelectOptions;

    case Rams = 'rams';
    case Crows = 'crows';
    case Masks = 'masks';
    case Tomes = 'tomes';
}
