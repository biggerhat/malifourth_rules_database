<?php

namespace App\Enums;

use App\Traits\Attributes\UsesEnumLabel;
use App\Traits\UsesEnumSelectOptions;

enum FactionEnum: string
{
    use UsesEnumLabel;
    use UsesEnumSelectOptions;

    case Arcanists = 'arcanists';
    case Bayou = 'bayou';
    case Guild = 'guild';
    case ExplorersSociety = 'explorers_society';
    case Neverborn = 'neverborn';
    case Outcasts = 'outcasts';
    case Resurrectionists = 'resurrectionists';
    case TenThunders = 'ten_thunders';
}
